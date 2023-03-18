<?php

namespace InfinyHost\InfinyMini;

use InfinyHost\InfinyMini\Exceptions\ServiceNotFoundException;
use Psr\Container\ContainerInterface;

class ServiceContainer implements ContainerInterface
{

    private array $services;

    public function __construct()
    {
        $this->services = [];
    }

    /**
     * Adds an object generator and the identifier for that object, with the option
     * of make the object 'singleton' or not.
     *
     * @param string   $identifier The identifier of the service
     * @param callable $loader     The generator for the service object
     * @param boolean  $singleton  Whether or not to return always the same instance of the object
     */
    public function add(string $identifier, callable $loader, bool $singleton = true): void
    {
        $this->services[$identifier] = new Service($loader, $singleton);
    }

    /**
     * Gets the service identified by the given identifier.
     *
     * @param string $identifier The identifier of the service
     *
     * @return object The object identified by the given id
     * @throws ServiceNotFoundException If there's no such service
     */
    public function get($identifier)
    {
        if (!isset($this->services[$identifier])) {
            throw new ServiceNotFoundException(
                "Service identified by '$identifier' does not exist"
            );
        }
        return $this->services[$identifier]->get();
    }

    /**
     * Checks if the service identified by the given identifier exists.
     *
     * @param string $identifier The identifier of the service
     *
     * @return bool Whether or not the service exists
     */
    public function has($identifier): bool
    {
        return isset($this->services[$identifier]);
    }

}