<?php

namespace InfinyHost\InfinyMini\Services\Router;

class Route
{
    private string $method;
    private string $path;
    private $callback;
    private string $name;

    public function __construct(string $method, string $path, $callback)
    {
        $this->method = $method;
        $this->path = $path;
        $this->callback = $callback;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getCallback(): callable
    {
        return $this->callback;
    }

    public function execute()
    {
        if (is_array($this->callback) && is_string($this->callback[0]) && is_string($this->callback[1])) {
            // TODO: Add dependency injection here if needed.
            $class = new $this->callback[0]();
            $method = $this->callback[1];
            // TODO: Check if method exists
            return $class->$method();
        }

        return ($this->callback)();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Route
    {
        $this->name = $name;
        return $this;
    }
}