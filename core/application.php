<?php

class Core_Application
{

    private $_class;

    final function singleton($class_name)
    {
        if ($this->isRegister($class_name)) {
            return $this->getRegister($class_name);
        }
        return $this->register($class_name, new $class_name($this));
    }

    function getRegister ($class_name)
    {
        return $this->_class[$class_name];
    }

    function isRegister ($class_name)
    {
        return isset($this->_class[$class_name]);
    }

    function register ($class_name, $class)
    {
        $this->_class[$class_name] = $class;
        return $class;
    }

    function action ($module, $action, $params)
    {
        $class = $this->singleton('module_' . $module);
        return call_user_func_array(array(
            $class,
            $action
        ), $params);
    }

}
