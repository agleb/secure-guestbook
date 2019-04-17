<?php

namespace SecureGuestbook;

/*
 * Simple storage interface (PDO wrapper)
 *
 * @package  Guestbook
 * @author   Gleb Andreev <glebandreev@gmail.com>
 * @version  $Revision: 1.0 $
 */
use PDO;

class Storage
{
    /**
     * $pdo.
     *
     * @property $pdo PDO instance
     */
    private $pdo;

    public function __construct()
    {
        $dsn = 'mysql:host='.getenv('WEBAPP_STORAGE_MYSQL_HOST').';'
            .'dbname='.getenv('WEBAPP_STORAGE_MYSQL_DB').';'
                .'charset=utf8';
        $opt = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $this->pdo = new PDO(
            $dsn,
            getenv('WEBAPP_STORAGE_MYSQL_USER'),
            getenv('WEBAPP_STORAGE_MYSQL_PASSWORD'),
            $opt
            );
    }

    /**
     * select query handler.
     *
     * @param string $query
     * @param array  $values
     *
     * @return array
     */
    public function select(string $query, array $values)
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($values);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * selectSingle - single selects queries handler.
     *
     * @param string $query
     * @param array  $values
     *
     * @return array
     */
    public function selectSingle(string $query, array $values)
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($values);

        return $stmt->fetch();
    }

    /**
     * insert query handler.
     *
     * @param string $query
     * @param array  $values
     *
     * @return mixed
     */
    public function insert(string $query, array $values)
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($values);

        return $this->pdo->lastInsertId() | '!';
    }

    /**
     * update query handler.
     *
     * @param string $query
     * @param array  $values
     *
     * @return bool
     */
    public function update(string $query, array $values)
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($values);

        return true;
    }

    /**
     * delete query handler.
     *
     * @param string $query
     * @param array  $values
     *
     * @return bool
     */
    public function delete(string $query, array $values)
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($values);

        return true;
    }
}
