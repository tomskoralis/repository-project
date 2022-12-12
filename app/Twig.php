<?php

namespace App;

use App\Repositories\{UsersRepository, DatabaseUsersRepository};
use DI\Container;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extra\Intl\IntlExtension;
use Composer\ClassMapGenerator\ClassMapGenerator;
use function DI\create;

class Twig
{
    private static Environment $twig;
    private static Container $container;

    public static function renderTemplate(Template $template): void
    {
        if (!isset(self::$twig)) {
            self::$container = new Container();
            self::$container->set(UsersRepository::class, create(DatabaseUsersRepository::class));

            self::$twig = new Environment(new FilesystemLoader('../views'));
            self::$twig->addExtension(new IntlExtension());

            foreach (ClassMapGenerator::createMap('../app/ViewVariables') as $symbol => $path) {
                if (class_exists($symbol)) {
                    $variable = self::$container->get($symbol);
                    self::$twig->addGlobal($variable->getName(), $variable->getValue());
                }
            }
        }
        echo self::$twig->render($template->getPath(), $template->getParameters());
    }
}