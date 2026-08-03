<?php

declare(strict_types=1);

namespace App\Core;

use Nette\Application\Routers\RouteList;
use Nette\StaticClass;

final class RouterFactory
{
    use StaticClass;

    public static function createRouter(): RouteList
    {
        $router = new RouteList();

        self::buildAdmin($router);
        self::buildFront($router);

        return $router;
    }

    protected static function buildAdmin(RouteList $router): RouteList
    {
        $list = new RouteList('Admin');
        $router->add($list);
        $list->addRoute('admin/repository/specimen[/<id .+>]', 'Repository:specimen');
        $list->addRoute('admin/<presenter>/<action>[/<id>]', 'Home:default');

        return $router;
    }

    protected static function buildFront(RouteList $router): RouteList
    {
        $list = new RouteList('Front');
        $router->add($list);
        $list->addRoute('ark[/<value .+>]', 'Ark:default');
        $list->addRoute('iiif/manifest[/<id .+>]', 'Iiif:manifest');
        $list->addRoute('repository/specimen[/<sid .+>]', 'Repository:specimen');
        $list->addRoute('<presenter>/<action>[/<id>]', 'Home:default');

        return $router;
    }
}
