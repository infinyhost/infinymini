<?php

namespace InfinyHost\InfinyMini;

use InfinyHost\InfinyMini\Exceptions\ServiceNotFoundException;

abstract class ServiceProvider
{
    protected App $app;
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public abstract function register(): void;

    /**
     * Get the app instance
     * @return App
     */
    public function app(): App
    {
        return $this->app;
    }

    /**
     * Get the service container
     * @return ServiceContainer
     */
    public function services(): ServiceContainer
    {
        return $this->app->services();
    }

    /**
     * Get the config service
     * @return Config
     * @throws ServiceNotFoundException
     */
    public function config(): Config
    {
        return $this->app->services()->get('config');
    }

}