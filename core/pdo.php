<?php
use \PDO;

class Core_Pdo extends PDO
{
    function __construct($dsn, $username = '', $password = '', $options = array())
    {
        $options = array_merge(array(
            self::ATTR_ERRMODE => self::ERRMODE_EXCEPTION
        ), $options);
        parent::__construct($dsn, $username, $password, $options);
        $this->setAttribute(self::ATTR_ERRMODE, self::ERRMODE_EXCEPTION);
        $this->setAttribute(self::ATTR_EMULATE_PREPARES, false);
        $this->setAttribute(self::ATTR_CASE, self::CASE_LOWER);
        $this->setAttribute(self::ATTR_DEFAULT_FETCH_MODE, self::FETCH_ASSOC);
        // $this->setAttribute(self::ATTR_STRINGIFY_FETCHES, false);
        $this->exec('set sql_mode=NO_UNSIGNED_SUBTRACTION');
        $this->exec('SET NAMES utf8');
    }
}
