<?php declare(strict_types=1);

namespace Tests\Cases\Unit\Model\Database\Entity;

use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Repository\HerbariaRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Tester\Assert;

require_once __DIR__ . '/../../../../../bootstrap.php';

test('HerbariaRepository::findOneWithAcronym returns entity', function (): void {
    $acronym = 'PR';


    $em = \Mockery::mock(EntityManagerInterface::class)->makePartial();
    $em->shouldReceive('getConfiguration')->andReturn(\Mockery::mock(\Doctrine\ORM\Configuration::class)->makePartial());


    // Mock QueryBuilder
    $qb = \Mockery::mock(QueryBuilder::class, [$em])->makePartial();
    $qb->shouldReceive('where')->with('upper(a.acronym) = upper(:acronym)')->andReturnSelf();
    $qb->shouldReceive('setParameter')->with('acronym', $acronym)->andReturnSelf();

    // Mock Query
    $query = \Mockery::mock(Query::class, [$em])->makePartial();
    $herbarium = \Mockery::mock(Herbaria::class);
    $query->shouldReceive('getOneOrNullResult')->andReturn($herbarium);

    $qb->shouldReceive('getQuery')->andReturn($query);

    // Partial mock repository
    $repo = \Mockery::mock(HerbariaRepository::class)->makePartial();
    $repo->shouldAllowMockingProtectedMethods();
    $repo->shouldReceive('createQueryBuilder')->with('a')->andReturn($qb);

    // Test
    $result = $repo->findOneWithAcronym($acronym);
    Assert::same($herbarium, $result);
});

test('HerbariaRepository::findOneWithAcronym returns null when not found', function (): void {
    $acronym = 'XYZ';

    $em = \Mockery::mock(EntityManagerInterface::class)->makePartial();
    $em->shouldReceive('getConfiguration')->andReturn(\Mockery::mock(\Doctrine\ORM\Configuration::class)->makePartial());

    $qb = \Mockery::mock(QueryBuilder::class, [$em])->makePartial();
    $qb->shouldReceive('where')->with('upper(a.acronym) = upper(:acronym)')->andReturnSelf();
    $qb->shouldReceive('setParameter')->with('acronym', $acronym)->andReturnSelf();

    $query = \Mockery::mock(Query::class, [$em])->makePartial();
    $query->shouldReceive('getOneOrNullResult')->andReturn(null);

    $qb->shouldReceive('getQuery')->andReturn($query);

    $repo = \Mockery::mock(HerbariaRepository::class)->makePartial();
    $repo->shouldAllowMockingProtectedMethods();
    $repo->shouldReceive('createQueryBuilder')->with('a')->andReturn($qb);

    $result = $repo->findOneWithAcronym($acronym);
    Assert::null($result);
});
