<?php

namespace SecureGuestbook;

/**
 * View renderer
 *
 * @package  Guestbook
 * @author   Gleb Andreev <glebandreev@gmail.com>
 * @version  $Revision: 1.0 $
 */
class View
{
    /**
     * request
     *
     * @param object $request
     * @return object
     */
    public function request(object $request)
    {
        if ($this->supported($request->view)) {
            return $request->putHtml($this->render($request->view, $request->data));
        }
        return $request->terminate('unsupported view "' . $request->view . '"');
    }

    /**
     * Load and render a given view.
     *
     * @param string $view
     * @param array $data
     * @return string
     */
    private function render(string $view, array $data)
    {
        $viewTemplate = $this->loadFile($view);

        return $this->finalize($this->renderData($viewTemplate, $data));
    }

    /**
     * Finalize a view - remove all unused placeholders.
     *
     * @param string $viewResult
     * @return void
     */
    private function finalize(string $viewResult)
    {
        $viewResult = preg_replace('/<!--\\s+.+?_ARRAY .+?\\s+-->/s', '', $viewResult);

        return preg_replace('/\\{\\{([a-zA-Z0-9\\_]+?)\\}\\}/s', '', $viewResult);
    }

    /**
     * loadFile - load view's file.
     *
     * @param string $view
     * @return void
     */
    private function loadFile(string $view)
    {
        $viewFilename = $this->getViewFilename($view);
        if (file_exists($viewFilename)) {
            return file_get_contents($viewFilename);
        }

        return '';
    }

    /**
     * renderData - recursively render all the data with a given view
     *
     * @param string $viewTemplate
     * @param array $data
     * @return void
     */
    private function renderData(string $viewTemplate, array $data)
    {

        foreach ($data as $key => $value) {
            if (is_array($value)
                and $this->isAssoc($value)
                and $arrayBlock = $this->extractArrayBlock($viewTemplate, $key)) {
                // Key is an associative array
                $viewTemplate = str_replace($arrayBlock, $this->renderData($arrayBlock, $value), $viewTemplate);
            } elseif (is_array($value)
                and !$this->isAssoc($value)
                and $arrayBlock = $this->extractArrayBlock($viewTemplate, $key)) {
                // Key is an indexed array
                foreach ($value as $item) {
                    $result[] = $this->renderData($arrayBlock, $item);
                }
                $viewTemplate = str_replace($arrayBlock, join('', $result), $viewTemplate);
            } elseif (is_object($value) and $objectBlock = $this->extractObjectBlock($viewTemplate, $key)) {
                // Key is an object
                $viewTemplate = str_replace(
                    $objectBlock,
                    $this->renderData($objectBlock, $value),
                    $viewTemplate
                );
            } else {
                $viewTemplate = str_replace('{{' . $key . '}}', $value, $viewTemplate);
            }
        }

        return $viewTemplate;
    }

    /**
     * isAssoc - checks if a given array an associative one
     *
     * @param array $arr
     * @return void
     */
    private function isAssoc(array $arr)
    {
        if ([] === $arr) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * extractArrayBlock
     *
     * @param string $viewTemplate
     * @param string $arrayName
     * @return void
     */
    private function extractArrayBlock(string $viewTemplate, string $arrayName)
    {
        $reg = "/<!--\\s+BEGIN_ARRAY ${arrayName}\\s+-->(.*)\n*\\s*<!--\\s+END_ARRAY ${arrayName}\\s+-->/sm";
        preg_match($reg, $viewTemplate, $matches);

        return $matches[1];
    }

    /**
     * extractObjectBlock
     *
     * @param string $viewTemplate
     * @param string $objectName
     * @return void
     */
    private function extractObjectBlock(string $viewTemplate, string $objectName)
    {
        $reg = "/<!--\\s+BEGIN_OBJECT ${objectName}\\s+-->(.*)\n*\\s*<!--\\s+END_OBJECT ${objectName}\\s+-->/sm";
        preg_match($reg, $viewTemplate, $matches);

        return $matches[0];
    }

    /**
     * getViewFilename
     *
     * @param string $view
     * @return void
     */
    private function getViewFilename(string $view)
    {
        return getenv('WEBAPP_BASEDIR') . '/views/' . \SecureGuestbook\Configuration::views()[$view];
    }

    /**
     * Check if the view is amongst supported.
     *
     * @param mixed $view
     * @return void
     */
    private function supported($view)
    {
        return isset(\SecureGuestbook\Configuration::views()[$view]);
    }
}
