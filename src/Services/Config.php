<?php

namespace InfinyHost\InfinyMini\Services;

use InfinyHost\InfinyMini\Exceptions\InvalidStateException;

class Config
{
    private array $config = [];

    public function __construct()
    {
        if (!defined('CFGPATH')) {
            throw new InvalidStateException("Config path is not defined. Did you access this file directly?");
        }

        // List diectories in the config directory and load them one by one, by adding the filename as a config prefix
        $configFiles = scandir(CFGPATH);
        foreach ($configFiles as $configFile) {
            if (is_file(CFGPATH . $configFile)) {
                $this->loadConfigFile($configFile);
            }
        }
    }

    /**
     * Loads a config file and merges it with the existing config
     * @param string $configFile
     * @return void
     */
    private function loadConfigFile(string $configFile)
    {
        $config = require(CFGPATH . $configFile);
        $configPrefix = pathinfo($configFile, PATHINFO_FILENAME);
        $this->config = array_merge($this->config, [$configPrefix => $config]);
    }

    /**
     * Gets a config value
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $config = $this->config;
        foreach ($keys as $key) {
            if (isset($config[$key])) {
                $config = $config[$key];
            } else {
                return $default;
            }
        }
        return $config;
    }


    /**
     * Sets a config value
     * @param string $key
     * @param $value
     * @return void
     */
    public function set(string $key, $value)
    {
        $keys = explode('.', $key);
        $config = &$this->config;
        foreach ($keys as $key) {
            if (isset($config[$key])) {
                $config = &$config[$key];
            } else {
                $config[$key] = [];
                $config = &$config[$key];
            }
        }
        $config = $value;
    }

    /**
     * Gets all config values
     * @return array
     */
    public function getAll(): array
    {
        return $this->config;
    }

    /**
     * Sets all config values
     * @param array $config
     * @return void
     */
    public function setAll(array $config)
    {
        $this->config = $config;
    }

}