<?php

namespace InfinyHost\InfinyMini;

use Dotenv\Dotenv;
use InfinyHost\InfinyMini\Exceptions\ServiceNotFoundException;
use InfinyHost\InfinyMini\Services\Config;
use InfinyHost\InfinyMini\Services\Logger;
use InfinyHost\InfinyMini\Services\Router\Router;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use \Laminas\Diactoros\ServerRequestFactory;

class App
{
    /**
     * The service container instance
     * @var ServiceContainer $container
     */
    private ServiceContainer $container;

    /**
     * Is the current run a CLI one
     * @var bool $isCli
     */
    private bool $isCli = false;

    /**
     * Current app instance as singleton
     * @var App|null $instance
     */
    private static ?App $instance = null;

    private function __construct(){
        // Init service container
        $this->container = new ServiceContainer();
        // Determine if the current run is a CLI one
        $this->isCli = php_sapi_name() == 'cli';
        // Boot the app
        $this->boot();
    }

    /**
     * Get the service container
     * @return ServiceContainer
     */
    public function services(): ServiceContainer
    {
        return $this->container;
    }

    private function boot(): void
    {
        // Load environment variables
        $dotenv = Dotenv::createImmutable(ROOTPATH);
        $dotenv->load();

        // Load config
        $cfg = new Config();
        $this->container->add('config', function() use(&$cfg) {
            return $cfg;
        });

        // Load logger
        $this->container->add('logger', function() use(&$cfg){
            return new Logger(ROOTPATH . $cfg->get('app.logDir'), $cfg->get('app.logLevel'),
                [
                    'logFormat' => $cfg->get('app.logFormat'),
                    'logExtension' => $cfg->get('app.logExtension'),
                    'logPrefix' => $cfg->get('app.logPrefix'),
                ]);
        });

        // Parse the request
        $request = ServerRequestFactory::fromGlobals();
        $this->container->add('request', function() use(&$request){
            return $request;
        });

        // Template engine
        $this->container->add('twig', function() use(&$cfg){
            return new Environment(new FilesystemLoader($cfg->get('app.views')), [
                'cache' => $cfg->get('app.viewsCache'),
            ]);
        });

        // Router
        $this->container->add('router', function(){
            $router = new Router();
            $router->loadFromFile(ROUTESPATH . 'web.php');
            // Load CLI routes
            if ($this->isCli && file_exists(ROUTESPATH . 'cli.php')) {
                $router->loadFromFile(ROUTESPATH . 'cli.php');
            }
            return $router;
        });
    }

    /**
     * Get the config service
     * @return Config
     * @throws ServiceNotFoundException
     */
    public function config(): Config
    {
        /* @var Config $config */
        $config = $this->container->get('config');
        return $config;
    }

    /**
     * Get the logger service
     * @return Logger
     * @throws ServiceNotFoundException
     */
    public function logger(): Logger
    {
        /* @var Logger $logger */
        $logger = $this->container->get('logger');
        return $logger;
    }

    /**
     * Render a view
     * @param string $view
     * @param array $data
     * @return ResponseInterface
     * @throws ServiceNotFoundException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function render(string $view, array $data = [])
    {
        /* @var Environment $twig */
        $twig = $this->container->get('twig');
        $body = $twig->render($view, $data);
        $response = $this->response()->withStatus(200);
        $response->getBody()->write($body);
        return $response;
    }

    public function run(bool $useURI = false): void
    {
        /* @var Router $router */
        $router = $this->container->get('router');

        /* @var ServerRequestInterface $request */
        $request = $this->container->get('request');

        /* @var ResponseInterface $response */
        $response = null;
        if ($useURI) {
            $response = $router->route($request->getUri(), $request->getMethod());
        } else {
            $route = "/";
            $method = "GET";
            if (isset($_GET['route']) && !empty($_GET['route'])) {
               $route = $_GET['route'];
               $method = $request->getMethod();
            } else if($this->isCli && isset($_SERVER['argv'][1]) && !empty($_SERVER['argv'][1])) {
                $route = $_SERVER['argv'][1];
                $method = "CLI";
            }
            $response = $router->route($route, $method);
        }

        // Simple response parsing and sending
        // If the response is null, send a 404 response
        if ($response == null) {
            $response = $this->response()->withStatus(404);
            $response->getBody()->write('404 Not Found');
        }

        // If we are running as CLI, just return the response
        if ($this->isCli) {
            echo $response->getBody() . PHP_EOL;
            return;
        }

        // Send the response as HTTP response with code and content type
        http_response_code($response->getStatusCode());
        if ($response->hasHeader('Content-Type')){
            header('Content-Type: ' . $response->getHeader('Content-Type')[0]);
        } else {
            header('Content-Type: text/html');
        }

        echo $response->getBody();
    }

    public function response(): ResponseInterface
    {
        return new Response();
    }

    public function request(): ServerRequestInterface
    {
        return $this->container->get('request');
    }

    /**
     * Get the app instance
     * @return App
     */
    public static function getInstance(): App
    {
        if (static::$instance == null) {
            static::$instance = new App();
        }
        return static::$instance;
    }
}