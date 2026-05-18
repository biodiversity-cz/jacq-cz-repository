<?php

declare(strict_types=1);

namespace Tests\Cases\Unit\Model\Database\Entity;

use App\Model\Database\Repository\AbstractRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Tester\Assert;

require_once __DIR__.'/../../../../../bootstrap.php';

class TestRepository extends AbstractRepository
{
}

test('AbstractRepository::findPairs returns correct key => value array', function (): void {
    $metadata = \Mockery::mock(ClassMetadata::class);
    $metadata->name = 'DummyEntity';
    $metadata->shouldReceive('getSingleIdentifierFieldName')->andReturn('id');

    $em = \Mockery::mock(EntityManagerInterface::class);
    // Partial mock repository
    $repo = new TestRepository($em, $metadata);

    // Mock QueryBuilder a Query
    $query = \Mockery::mock(Query::class);
    $query->shouldReceive('getArrayResult')->andReturn([
        ['name' => 'Alice', 'id' => 1],
        ['name' => 'Bob', 'id' => 2],
    ]);

    $qb = \Mockery::mock(QueryBuilder::class);
    $qb->shouldReceive('select')->andReturnSelf();
    $qb->shouldReceive('resetDQLPart')->andReturnSelf();
    $qb->shouldReceive('from')->andReturnSelf();
    $qb->shouldReceive('andWhere')->andReturnSelf();
    $qb->shouldReceive('setParameter')->andReturnSelf();
    $qb->shouldReceive('addOrderBy')->andReturnSelf();
    $qb->shouldReceive('getQuery')->andReturn($query);

    $em->shouldReceive('createQueryBuilder')->andReturn($qb);

    $repo = \Mockery::mock($repo)->makePartial()->shouldAllowMockingProtectedMethods();
    $repo->shouldReceive('createQueryBuilder')->andReturn($qb);

    $result = $repo->findPairs('id', 'name');

    Assert::same([
        1 => 'Alice',
        2 => 'Bob',
    ], $result);
});
