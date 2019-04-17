<?php

declare (strict_types = 1);

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \View::renderData
 * @covers \View::request
 * @covers \Router::request
 * @covers \Request::__construct
 */
final class ApplicationTest extends TestCase
{
    protected $request;

    protected function setUp(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $stub = $this->getMockBuilder(\SecureGuestbook\Fail2Ban::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock()
        ;
        $stub->method('registerIncidentForIP')->willReturn(false);
        $this->request = new \SecureGuestbook\Request(true, $stub);
        $this->request->action = 'test';
    }

    protected function tearDown(): void
    {
    }

    protected static function getMethod($name, $class_name)
    {
        $class = new ReflectionClass($class_name);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    public function testViewRenderData()
    {
        $view = new \SecureGuestbook\View();

        $template = '{{first}}<ul><!-- BEGIN_ARRAY x --><li>{{middle}}</li><!-- END_ARRAY x --></ul>{{last}}';
        $data = ['first' => 'first_val', 'x' => [['middle' => 1], ['middle' => 2], ['middle' => 3]], 'last' => 'last_val'];
        $expected = 'first_val<ul><!-- BEGIN_ARRAY x --><li>1</li><li>2</li><li>3</li><!-- END_ARRAY x --></ul>last_val';

        $instance = $this->getMethod('renderData', '\SecureGuestbook\View');

        $result = $instance->invokeArgs($view, [$template, $data]);
        $this->assertSame($expected, $result);
    }

    public function testRouterAndView()
    {

        $view = new \SecureGuestbook\View();

        $this->request = \SecureGuestbook\Router::request($this->request);
        $this->request = $view->request($this->request);
        $this->assertSame('passed', $this->request->html);
    }

    public function testRequest()
    {

        $this->assertInstanceOf('\SecureGuestbook\Request', $this->request);
    }    

}
