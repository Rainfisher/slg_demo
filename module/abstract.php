<?php
class Module_Abstract extends Core_Module
{
    function time()
    {
        return time();
    }

    public function __get ($name)
    {
        if (strpos($name, 'model') === 0) {
            if ($name == 'model') {
                $reflect = new \ReflectionClass($this);
                $class_name = str_ireplace('module', 'model', $reflect->getName());
            } else {
                $class_name = $name;
            }
        } else {
            $class_name = 'module_' . $name;
        }
        return $this->app->singleton($class_name);
    }

}
