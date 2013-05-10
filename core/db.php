<?php

class Core_Db
{

    private $_pdo;

    public function querySql ($sql, $bind = array())
    {
        $pdo = $this->getConn();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($bind);
        return $stmt->fetchAll();
    }

    public function executeSql ($sql, $bind = array())
    {
        $pdo = $this->getConn();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($bind);
        $affected = $stmt->rowCount();
        return $affected;
    }

    public function lastInsertId ()
    {
        return (int) $this->_pdo->lastInsertId();
    }

    protected function getConn ()
    {
        if (! $this->_pdo) {
            $this->_pdo = new Core_PDO('mysql:dbname=slg;host=127.0.0.1', 'root', '123321');
        }
        return $this->_pdo;
    }

}
