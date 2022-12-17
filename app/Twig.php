<?php

namespace App;

use App\ViewVariables\ViewVariables;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extra\Intl\IntlExtension;
use Composer\ClassMapGenerator\ClassMapGenerator;

class Twig
{
    private Environment $twig;

    public function __construct()
    {
        $this->twig = new Environment(new FilesystemLoader('../views'));
        $this->twig->addExtension(new IntlExtension());
        foreach (ClassMapGenerator::createMap('../app/ViewVariables') as $symbol => $path) {
            if (class_exists($symbol)) {
                $variable = Container::get($symbol);
                /** @var ViewVariables $variable */
                $this->twig->addGlobal($variable->getName(), $variable->getValue());
            }
        }
    }

    public function renderTemplate(Template $template): void
    {
        echo $this->twig->render($template->getPath(), $template->getParameters());
    }
}