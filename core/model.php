<?php

abstract class Core_Model
{

    protected $app;

    function __construct ($app)
    {
        $this->app = $app;
    }

    protected function db ()
    {
        return $this->app->singleton('core_db');
    }

    protected function getTable()
    {
        return $this->table;
    }

    public final function getById ($params)
    {
        $cond = array();
        foreach ($params as $key => $column) {
            $cond[] = "{$key}=:{$key}";
        }
        $table = $this->getTable();
        $sql = "SELECT * FROM {$table} WHERE " . implode(' AND ', $cond) . ' LIMIT 1';
        $rs = $this->querySql($sql, $params);
        if ($rs) {
            $rs = $rs[0];
        }
        return $rs;
    }

    public final function setById ($id, $data, $optional = array())
    {
        $cond = array();
        foreach ($id as $column => $val) {
            $cond[] = "{$column}=:{$column}";
        }
        $set = array();
        $params = $id;
        foreach ($data as $key => $val) {
            if (is_numeric($key)) {
                if (is_array($val)) {
                    $set[] = array_shift($val);
                    $params = array_merge($params, $val);
                } else {
                    $set[] = $val;
                }
            } else {
                $params[$key] = $val;
                $set[] = "{$key}=:{$key}";
            }
        }
        foreach ($optional as $key => $val) {
            if (is_numeric($key)) {
                if (is_array($val)) {
                    $cond[] = array_shift($val);
                    $params = array_merge($params, $val);
                } else {
                    $cond[] = $val;
                }
            } else {
                $params['c_o_n_d_' . $key] = $val;
                $cond[] = "{$key}=:c_o_n_d_{$key}";
            }
        }
        $table = $this->getTable();
        $sql = "UPDATE {$table} SET " . implode(',', $set) . ' WHERE ' . implode(' AND ', $cond);
        return $this->executeSql($sql, $params);
    }

    public final function create ($data)
    {
        $set = array();
        foreach ($data as $key => $val) {
            $set[] = ":{$key}";
        }
        $table = $this->getTable();
        $sql = "INSERT INTO {$table} (" . implode(',', array_keys($data)) . ") VALUES (" . implode(',', $set) . ')';
        if ($this->executeSql($sql, $data)) {
            $ins = $this->lastInsertId();
            return $ins ? $ins : true;
        }
        return false;
    }

    public final function delById ($params)
    {
        $cond = array();
        foreach ($params as $column => $v) {
            $cond[] = "{$column}=:{$column}";
        }
        $table = $this->getTable();
        $sql = "DELETE FROM {$table} WHERE " . implode(' AND ', $cond);
        return $this->executeSql($sql, $params);
    }

    public final function find ($conditions, $orders = null)
    {
        $conds = array();
        $params = array();
        foreach ($conditions as $key => $value) {
            if (is_numeric($key)) {
                if (is_array($value)) {
                    array_push($conds, array_shift($value));
                    $params = array_merge($params, $value);
                } else {
                    array_push($conds, $value);
                }
                continue;
            }
            array_push($conds, "{$key}=:{$key}");
            $params[$key] = $value;
        }
        $table = $this->getTable();
        $sql = "SELECT * FROM " . $table . ' ';
        if ($conds) {
            $sql .= "WHERE " . implode(' AND ', $conds) . " ";
        }
        if ($orders) {
            $sql .= "ORDER BY {$orders} ";
        }
        $args = func_get_args();
        $as = sizeof($args);
        if ($as >= 4) {
            $sql .= "LIMIT {$args[2]}, {$args[3]}";
        } elseif ($as == 4) {
            $sql .= "LIMIT {$args[2]}";
        } else {
            $sql .= "LIMIT 1000";
        }
        return $this->querySql($sql, $params);
    }

    public final function count ($conditions)
    {
        $conds = array();
        $params = array();
        foreach ($conditions as $key => $value) {
            if (is_numeric($key)) {
                if (is_array($value)) {
                    array_push($conds, array_shift($value));
                    $params = array_merge($params, $value);
                } else {
                    array_push($conds, $value);
                }
                continue;
            }
            array_push($conds, "{$key}=:{$key}");
            $params[$key] = $value;
        }
        $table = $this->getTable();
        $sql = "SELECT count(*) as ct FROM " . $table . ' ';
        if ($conds) {
            $sql .= "WHERE " . implode(' AND ', $conds) . " ";
        }
        $rs = $this->querySql($sql, $params);
        if ($rs) {
            return $rs[0]['ct'];
        }
        return 0;
    }

    public final function querySql ($sql, $bind = array())
    {
        return $this->db()->querySql($sql, $bind);
    }

    public final function executeSql ($sql, $bind = array())
    {
        return $this->db()->executeSql($sql, $bind);
    }

    public final function lastInsertId ()
    {
        return $this->db()->lastInsertId();
    }

}
