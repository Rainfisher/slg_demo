<?php

const MODULE_PATH = 'module';

function __autoload($class_name)
{
    if (class_exists($class_name, false) || interface_exists($class_name, false)) {
        return;
    }
    $class_name = strtolower($class_name);
    $file_name = str_replace('_', DIRECTORY_SEPARATOR, $class_name) . '.php';
    if (is_readable($file_name)) {
        include $file_name;
    }
}

if (! isset($argv[1])) {
    $dh = opendir(MODULE_PATH);
    while (($file = readdir($dh)) !== false) {
        if (is_file(MODULE_PATH . '/' . $file)) {
            $file = str_replace('.php', '', $file);
            $class = new ReflectionClass(MODULE_PATH . '_' . $file);
            $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $method) {
                if ($method->isFinal()) {
                    $params = $method->getParameters();
                    $str = '';
                    foreach ($params as $i => $param) {
                        $str .= ' ' . $param->name;
                    }
                    echo $file . ' ' . $method->name . $str . "\n";
                }
            }
        }
    }
    closedir($dh);
    exit();
}

try {
    $app = new Core_Application();
    $ret = $app->action($argv[1], $argv[2], array_slice($argv, 3));
    var_dump($ret);
} catch (Exception $e) {
    var_dump($e);
}
