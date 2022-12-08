<?php

namespace App;

use FastRoute\{Dispatcher, RouteCollector};
use function FastRoute\simpleDispatcher;

require_once 'routes.php';
require_once 'constants.php';

class Router
{
    private Dispatcher $dispatcher;

    public function __construct()
    {
        session_start();
        $this->dispatcher = simpleDispatcher(function (RouteCollector $route) {
            foreach (ROUTES_MAP as $routePoint) {
                $route->addRoute($routePoint[0], $routePoint[1], [$routePoint[2][0], $routePoint[2]][1]);
            }
        });
    }

    public function handleUri(): void
    {
        $httpMethod = $_SERVER["REQUEST_METHOD"];
        $uri = $_SERVER["REQUEST_URI"];
        if (false !== $pos = strpos($uri, "?")) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);
        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                Twig::renderTemplate(
                    new Template("templates/404.twig")
                );
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                Twig::renderTemplate(
                    new Template("templates/405.twig", [
                        "allowedMethods" => $routeInfo[1],
                    ])
                );
                break;
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                [$controller, $method] = $handler;
                $response = (new $controller)->{$method}($vars);
                if ($response instanceof Template) {
                    Twig::renderTemplate($response);
                    unset($_SESSION["errors"]);
                    unset($_SESSION["flashMessages"]);
                }
                if ($response instanceof Redirect) {
                    header("Location: " . $response->getUri());
                    exit();
                }
                break;
        }
    }
}