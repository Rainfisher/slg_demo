<?php

class Core_Module
{
    protected $app;

    function __construct ($app)
    {
        $this->app = $app;
    }

    protected final function error($msg)
    {
        throw new Exception($msg);
    }

}
