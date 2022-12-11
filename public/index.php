<?php

use App\Router;

require_once '../vendor/autoload.php';

$router = new Router();
$router->handleUri();

//        $datetime = new \DateTime();
//        echo "<pre>";
//        var_dump($datetime->format('c')); die;