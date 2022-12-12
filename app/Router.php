<?php

namespace App;

use App\Repositories\{
    UsersRepository,
    DatabaseUsersRepository,
    CurrenciesRepository,
    CoinMarketCapCurrenciesRepository,
    TransactionsRepository,
    DatabaseTransactionsRepository
};
use DI\Container;
use FastRoute\{Dispatcher, RouteCollector};
use function DI\create;
use function FastRoute\simpleDispatcher;

require_once 'routes.php';
require_once 'constants.php';

class Router
{
    private Dispatcher $dispatcher;
    private Container $container;

    public function __construct()
    {
        $this->container = new Container();
        $this->container->set(UsersRepository::class, create(DatabaseUsersRepository::class));
        $this->container->set(CurrenciesRepository::class, create(CoinMarketCapCurrenciesRepository::class));
        $this->container->set(TransactionsRepository::class, create(DatabaseTransactionsRepository::class));
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
                $response = $this->container->get($controller)->{$method}($vars);
                if ($response instanceof Template) {
                    Twig::renderTemplate($response);
                    Session::remove("errors");
                    Session::remove("flashMessages");
                }
                if ($response instanceof Redirect) {
                    header("Location: " . $response->getUri());
                    exit();
                }
                break;
        }
    }
}