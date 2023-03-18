<?php

namespace InfinyHost\InfinyMini;

class Service
{
    private $object = null;
    private bool $singleton;
    private $loader;

    public function __construct(callable $loader, bool $singleton = true)
    {
        $this->singleton = $singleton;
        $this->loader = $loader;
    }

    /**
     * Returns the specific dependency instance.
     *
     * @return mixed
     */
    public function get()
    {
        // Not a singleton, always return a new instance
        if (!$this->singleton) {
            return call_user_func($this->loader);
        }
        // Singleton, but might not be initialized yet
        if ($this->object === null) {
            $this->object = call_user_func($this->loader);
        }
        // Initialized now
        return $this->object;
    }
}