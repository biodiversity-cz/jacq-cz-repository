<?php declare(strict_types=1);

namespace Tests\Cases\Unit\Model\Database\Entity;

use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use App\Model\Database\Repository\PhotosRepository;
use App\Model\Specimen\Specimen;
use Doctrine\ORM\Query;
use Nette\Security\User;
use Tester\Assert;
use Mockery;


require_once __DIR__ . '/../../../../../bootstrap.php';



test('getPublicPhotosOfSpecimen returns expected photos', function (): void {
    $photo1 = Mockery::mock(Photos::class);
    $photo2 = Mockery::mock(Photos::class);

    $specimen = Mockery::mock(Specimen::class);
    $specimen->shouldReceive('getNumericPartOfId')->andReturn(42);
    $herbarium = Mockery::mock(Herbaria::class);
    $specimen->shouldReceive('getHerbarium')->andReturn($herbarium);

    $repo = Mockery::mock(PhotosRepository::class)->makePartial();
    $repo->shouldReceive('findBy')
        ->with([
            'specimenId' => 42,
            'herbarium' => $herbarium,
            'status' => PhotosStatus::PASSED_PUBLIC
        ])
        ->andReturn([$photo1, $photo2]);

    $result = $repo->getPublicPhotosOfSpecimen($specimen);
    Assert::same([$photo1, $photo2], $result);
});

test('getPublicPhoto returns expected photo', function (): void {
    $photo = Mockery::mock(Photos::class);

    $repo = Mockery::mock(PhotosRepository::class)->makePartial();
    $repo->shouldReceive('findOneBy')
        ->with(['id' => 123, 'status' => PhotosStatus::PASSED_PUBLIC])
        ->andReturn($photo);

    Assert::same($photo, $repo->getPublicPhoto(123));
});


test('getPhoto calls getDefaultDatasource and returns one', function (): void {
    $query = Mockery::mock(Query::class);
    $query->shouldReceive('getOneOrNullResult')->andReturn('photo');

    $qb = Mockery::mock(\Doctrine\ORM\QueryBuilder::class);
    $qb->shouldReceive('andWhere')->andReturnSelf();
    $qb->shouldReceive('setParameter')->andReturnSelf();
    $qb->shouldReceive('getQuery')->andReturn($query);

    $user = Mockery::mock(User::class);
    $user->shouldReceive('getIdentity')->andReturn((object)['herbarium' => 'H1']);
    $user->shouldReceive('isInRole')->andReturn(false);

    $photo = new Photos();
    $repo = Mockery::mock(PhotosRepository::class)->makePartial();
    $repo->shouldReceive('getDefaultDatasource')->with($user)->andReturn($qb);
    $repo->shouldReceive('getPhoto')->with($user,1)->andReturn($photo);

    Assert::type(Photos::class, $repo->getPhoto($user, 1));
});


test('getAllPhotosOfSpecimen calls getDefaultDatasource and returns result', function (): void {
    $query = Mockery::mock(Query::class);
    $query->shouldReceive('getResult')->andReturn(['photo1']);

    $qb = Mockery::mock(\Doctrine\ORM\QueryBuilder::class);
    $qb->shouldReceive('andWhere')->andReturnSelf();
    $qb->shouldReceive('setParameter')->andReturnSelf();
    $qb->shouldReceive('getQuery')->andReturn($query);

    $repo = Mockery::mock(PhotosRepository::class)->makePartial();
    $repo->shouldReceive('getDefaultDatasource')->andReturn($qb);

    $user = Mockery::mock(User::class);
    $specimen = Mockery::mock(Specimen::class);
    $specimen->shouldReceive('getNumericPartOfId')->andReturn(42);

    Assert::same(['photo1'], $repo->getAllPhotosOfSpecimen($user, $specimen));
});

test('findUnprocessedPhotos calls getDefaultDatasource and returns result', function (): void {
    $query = Mockery::mock(Query::class);
    $query->shouldReceive('getResult')->andReturn(['photo1']);

    $qb = Mockery::mock(\Doctrine\ORM\QueryBuilder::class);
    $qb->shouldReceive('andWhere')->andReturnSelf();
    $qb->shouldReceive('setParameter')->andReturnSelf();
    $qb->shouldReceive('getQuery')->andReturn($query);

    $repo = Mockery::mock(PhotosRepository::class)->makePartial();
    $repo->shouldReceive('getDefaultDatasource')->andReturn($qb);

    $user = Mockery::mock(User::class);

    Assert::same(['photo1'], $repo->findUnprocessedPhotos($user));
});
