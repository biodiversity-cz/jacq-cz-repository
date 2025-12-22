<?php

declare(strict_types=1);

namespace Database\Base;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Application\LinkGenerator;
use Nette\DI\Container;
use Nettrine\Fixtures\Fixture\ContainerAwareInterface;


abstract class FixtureBase implements FixtureInterface, OrderedFixtureInterface, ContainerAwareInterface
{

    protected Container $container;
    protected LinkGenerator $linkGenerator;

    public function setContainer(Container $container): void
    {
        $this->container = $container;
        $this->linkGenerator = $container->getByType(LinkGenerator::class);
    }

}
