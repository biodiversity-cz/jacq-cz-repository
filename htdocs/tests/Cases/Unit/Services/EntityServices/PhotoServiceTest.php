<?php

declare(strict_types=1);

namespace Tests\Cases\Unit\Services\EntityServices;

use App\Bootstrap;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use App\Model\Specimen\Specimen;
use App\Services\EntityServices\PhotoService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Nette\Security\SimpleIdentity;
use Nette\Security\User;
use Tester\Assert;
use Tests\Toolkit\PhotoTestFactory;

require_once __DIR__.'/../../../../bootstrap.php';

test('PhotoService: specimenHasPublicPhotos true if repository returns some photos', function (): void {
    $repository = \Mockery::mock(EntityRepository::class);
    $em = \Mockery::mock(EntityManagerInterface::class);
    $specimen = \Mockery::mock(Specimen::class);

    $em->shouldReceive('getRepository')->with(Photos::class)->andReturn($repository);

    $service = new PhotoService($em);
    $photo = PhotoTestFactory::detailed();

    $repository->shouldReceive('getPublicPhotosOfSpecimen')
        ->with($specimen)
        ->andReturn([$photo]);

    Assert::true($service->specimenHasPublicPhotos($specimen));
});

test('PhotoService: specimenHasPublicPhotos false if repository returns empty', function (): void {
    $repository = \Mockery::mock(EntityRepository::class);
    $em = \Mockery::mock(EntityManagerInterface::class);
    $specimen = \Mockery::mock(Specimen::class);

    $em->shouldReceive('getRepository')->with(Photos::class)->andReturn($repository);

    $service = new PhotoService($em);

    $repository->shouldReceive('getPublicPhotosOfSpecimen')->with($specimen)->andReturn([]);

    Assert::false($service->specimenHasPublicPhotos($specimen));
});

test('PhotoService: getDefaultDatasource delegates to repository', function (): void {
    $repository = \Mockery::mock(EntityRepository::class);
    $em = \Mockery::mock(EntityManagerInterface::class);

    $container = Bootstrap::boot()->createContainer();
    $user = $container->getByType(User::class);

    $em->shouldReceive('getRepository')->with(Photos::class)->andReturn($repository);

    $service = new PhotoService($em);

    $qb = \Mockery::mock(QueryBuilder::class);

    $repository->shouldReceive('getDefaultDatasource')->with($user)->andReturn($qb);

    Assert::same($qb, $service->getDefaultDatasource($user));
});

test('PhotoService: getAllPhotosOfSpecimen delegates to repository', function (): void {
    $repository = \Mockery::mock(EntityRepository::class);
    $em = \Mockery::mock(EntityManagerInterface::class);
    $container = Bootstrap::boot()->createContainer();
    $user = $container->getByType(User::class);

    $specimen = \Mockery::mock(Specimen::class);

    $em->shouldReceive('getRepository')->with(Photos::class)->andReturn($repository);

    $service = new PhotoService($em);

    $photos = [PhotoTestFactory::detailed()];

    $repository->shouldReceive('getAllPhotosOfSpecimen')->with($user, $specimen)->andReturn($photos);

    Assert::same($photos, $service->getAllPhotosOfSpecimen($user, $specimen));
});

test('PhotoService: getPhotoReference returns entity reference', function (): void {
    $repository = \Mockery::mock(EntityRepository::class);
    $em = \Mockery::mock(EntityManagerInterface::class);
    $photo = PhotoTestFactory::detailed();

    $em->shouldReceive('getRepository')->with(Photos::class)->andReturn($repository);

    $em->shouldReceive('getReference')->with(Photos::class, 42)->andReturn($photo);

    $service = new PhotoService($em);

    Assert::same($photo, $service->getPhotoReference(42));
});

test('PhotoService: getPhoto returns private photo if logged in and found', function (): void {
    $repository = \Mockery::mock(EntityRepository::class);
    $em = \Mockery::mock(EntityManagerInterface::class);

    $container = Bootstrap::boot()->createContainer();
    $user = $container->getByType(User::class);
    $identity = new SimpleIdentity(123, ['admin']);
    $user->login($identity);

    $photo = PhotoTestFactory::detailed();

    $em->shouldReceive('getRepository')->with(Photos::class)->andReturn($repository);

    $repository->shouldReceive('getPhoto')->with($user, 5)->andReturn($photo);

    $service = new PhotoService($em);

    Assert::same($photo, $service->getPhoto($user, 5));
});

test('PhotoService: getPhoto falls back to public if not logged in or private not found', function (): void {
    $repository = \Mockery::mock(EntityRepository::class);
    $em = \Mockery::mock(EntityManagerInterface::class);
    $container = Bootstrap::boot()->createContainer();
    $user = $container->getByType(User::class);
    $user->logout();

    $photo = PhotoTestFactory::detailed();

    $em->shouldReceive('getRepository')->with(Photos::class)->andReturn($repository);

    $repository->shouldReceive('getPublicPhoto')->with(7)->andReturn($photo);

    $service = new PhotoService($em);

    Assert::same($photo, $service->getPhoto($user, 7));
});

test('PhotoService: getPublicPhoto delegates to repository', function (): void {
    $repository = \Mockery::mock(EntityRepository::class);
    $em = \Mockery::mock(EntityManagerInterface::class);
    $photo = PhotoTestFactory::detailed();

    $em->shouldReceive('getRepository')->with(Photos::class)->andReturn($repository);

    $repository->shouldReceive('getPublicPhoto')->with(99)->andReturn($photo);

    $service = new PhotoService($em);

    Assert::same($photo, $service->getPublicPhoto(99));
});

test('PhotoService: getWaitingStatus returns entity reference', function (): void {
    $repository = \Mockery::mock(EntityRepository::class);
    $em = \Mockery::mock(EntityManagerInterface::class);
    $status = \Mockery::mock(PhotosStatus::class);

    $em->shouldReceive('getRepository')->with(Photos::class)->andReturn($repository);

    $em->shouldReceive('getReference')->with(PhotosStatus::class, PhotosStatus::WAITING)->andReturn($status);

    $service = new PhotoService($em);

    Assert::same($status, $service->getWaitingStatus());
});

test('PhotoService: getPhotoWithError delegates to repository', function (): void {
    $repository = \Mockery::mock(EntityRepository::class);
    $em = \Mockery::mock(EntityManagerInterface::class);
    $container = Bootstrap::boot()->createContainer();
    $user = $container->getByType(User::class);
    $photo = PhotoTestFactory::detailed();

    $em->shouldReceive('getRepository')->with(Photos::class)->andReturn($repository);

    $repository->shouldReceive('getPhotoWithError')->with($user, 321)->andReturn($photo);

    $service = new PhotoService($em);

    Assert::same($photo, $service->getPhotoWithError($user, 321));
});

test('PhotoService: getPhotosWithError delegates to repository', function (): void {
    $repository = \Mockery::mock(EntityRepository::class);
    $em = \Mockery::mock(EntityManagerInterface::class);
    $container = Bootstrap::boot()->createContainer();
    $user = $container->getByType(User::class);
    $photos = [PhotoTestFactory::detailed()];

    $em->shouldReceive('getRepository')->with(Photos::class)->andReturn($repository);

    $repository->shouldReceive('getPhotosWithError')->with($user)->andReturn($photos);

    $service = new PhotoService($em);

    Assert::same($photos, $service->getPhotosWithError($user));
});

test('PhotoService: findUnprocessedPhotos builds associative array by filename', function (): void {
    $repository = \Mockery::mock(EntityRepository::class);
    $em = \Mockery::mock(EntityManagerInterface::class);
    $container = Bootstrap::boot()->createContainer();
    $user = $container->getByType(User::class);

    $em->shouldReceive('getRepository')->with(Photos::class)->andReturn($repository);

    $photo1 = PhotoTestFactory::detailed();

    $repository->shouldReceive('findUnprocessedPhotos')->with($user)->andReturn([$photo1]);

    $service = new PhotoService($em);

    $result = $service->findUnprocessedPhotos($user);

    Assert::same(['specimen_123.tif' => $photo1], $result);
});

test('PhotoService: pendingPhotosCount builds query and returns result', function (): void {
    $repository = \Mockery::mock(EntityRepository::class);
    $em = \Mockery::mock(EntityManagerInterface::class);
    $qb = \Mockery::mock(QueryBuilder::class);
    $query = \Mockery::mock(Query::class);

    $em->shouldReceive('getRepository')->with(Photos::class)->andReturn($repository);
    $em->shouldReceive('createQueryBuilder')->andReturn($qb);

    $qb->shouldReceive('select')->andReturnSelf();
    $qb->shouldReceive('from')->andReturnSelf();
    $qb->shouldReceive('andWhere')->andReturnSelf();
    $qb->shouldReceive('join')->andReturnSelf();
    $qb->shouldReceive('groupBy')->andReturnSelf();
    $qb->shouldReceive('setParameter')->with('status', PhotosStatus::WAITING)->andReturnSelf();
    $qb->shouldReceive('getQuery')->andReturn($query);

    $expected = [['id' => 1, 'acronym' => 'X', 'count' => 5]];
    $query->shouldReceive('getResult')->andReturn($expected);

    $service = new PhotoService($em);

    Assert::same($expected, $service->pendingPhotosCount());
});

register_shutdown_function(function (): void {
    \Mockery::close();
});
