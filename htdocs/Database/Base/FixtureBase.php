<?php

declare(strict_types=1);

namespace Database\Base;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Nette\DI\Container;
use Nettrine\Fixtures\Fixture\ContainerAwareInterface;


abstract class FixtureBase implements FixtureInterface, OrderedFixtureInterface, ContainerAwareInterface
{

    protected Container $container;

    public function setContainer(Container $container): void
    {
        $this->container = $container;
    }

}
