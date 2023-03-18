<?php

namespace InfinyHost\InfinyMini;

class Controller
{
    protected App $app;

    public function __construct(?App $app = null)
    {
        $this->app = $app ?? App::getInstance();
    }
}