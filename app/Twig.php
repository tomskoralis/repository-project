<?php

namespace App;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extra\Intl\IntlExtension;
use Composer\ClassMapGenerator\ClassMapGenerator;

class Twig
{
    private static Environment $twig;

    public static function renderTemplate(Template $template): void
    {
        if (!isset(self::$twig)) {
            self::$twig = new Environment(new FilesystemLoader('../views'));
            self::$twig->addExtension(new IntlExtension());
            foreach (ClassMapGenerator::createMap('../app/ViewVariables') as $symbol => $path) {
                if (class_exists($symbol)) {
                    $variable = new $symbol;
                    self::$twig->addGlobal($variable->getName(), $variable->getValue());
                }
            }
        }
        echo self::$twig->render($template->getPath(), $template->getParameters());
//        try {
//            echo self::$twig->render($template->getPath(), $template->getParameters());
//        } catch (\Twig\Error\LoaderError $e) {
//            echo 'Twig Loader Error: ' . $e->getMessage();
//        } catch (\Twig\Error\RuntimeError $e) {
//            echo 'Twig Runtime Error: ' . $e->getMessage();
//        } catch (\Twig\Error\SyntaxError $e) {
//            echo 'Twig Syntax Error: ' . $e->getMessage();
//        }
    }
}