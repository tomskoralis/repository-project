<?php

namespace App;

use FastRoute\{Dispatcher, RouteCollector};
use function FastRoute\simpleDispatcher;

require_once 'routes.php';
require_once 'constants.php';

class Router
{
    private static ?Router $instance = null;
    private static Dispatcher $dispatcher;

    public function __construct()
    {
        Session::start();
        self::$dispatcher = simpleDispatcher(function (RouteCollector $route) {
            foreach (ROUTES_MAP as $routePoint) {
                $route->addRoute($routePoint[0], $routePoint[1], [$routePoint[2][0], $routePoint[2]][1]);
            }
        });
    }

    public static function handleUri(): void
    {
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);
        $routeInfo = self::getDispatcher()->dispatch($httpMethod, $uri);
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                (new Twig)->renderTemplate(
                    new Template('templates/404.twig')
                );
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                (new Twig)->renderTemplate(
                    new Template('templates/405.twig', [
                        'allowedMethods' => $routeInfo[1],
                    ])
                );
                break;
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                [$controller, $method] = $handler;
                $response = Container::get($controller)->{$method}($vars);
                if ($response instanceof Template) {
                    (new Twig)->renderTemplate($response);
                    Session::remove('errors');
                    Session::remove('flashMessages');
                }
                if ($response instanceof Redirect) {
                    if (Session::get('redirect', 'success') === 'true') {
                        Session::remove('redirect');
                    }
                    header('Location: ' . $response->getUri());
                    exit();
                }
                break;
        }
    }

    private static function getInstance(): ?Router
    {
        if (!isset(self::$instance)) {
            self::$instance = new Router();
        }
        return self::$instance;
    }

    private static function getDispatcher(): Dispatcher
    {
        return self::getInstance()::$dispatcher;
    }
}