<?php

namespace Tests\Cases\Integration;

use Tester\Assert;
use Exception;
use RuntimeException;

require __DIR__ . '/../../bootstrap.integration.php';

final class PrepareServicesTest extends IntegrationTestCase
{

    public function testPrepare()
    {
        $this->cleanDb();
        $this->cleanS3();
        Assert::equal(1,1);
    }

    protected function cleanDb():void{
        $connection = $this->em->getConnection();
        $schema = 'public';
        $connection->executeStatement("DROP SCHEMA IF EXISTS {$schema} CASCADE");
        $connection->executeStatement("DROP SCHEMA IF EXISTS front CASCADE");
        $connection->executeStatement("DROP SCHEMA IF EXISTS databots CASCADE");
        $connection->executeStatement("CREATE SCHEMA {$schema}");
//TODO add check for emptiness
        // db migration
        $this->runCommand([
            'command' => 'migrations:migrate',
            '--no-interaction' => true,
        ], 'Migrations failed');

        // fixtures
        $this->runCommand([
            'command' => 'doctrine:fixtures:load',
            '--no-interaction' => true,
        ], 'Fixtures failed');

    }
    protected function cleanS3():void
    {
        //s3 refresh
        try {

            $buckets = [self::BUCKET, $this->repositoryConfiguration->getRepositoryArchiveBucket(), $this->repositoryConfiguration->getRepositoryDatabotThumbsBucket(), $this->repositoryConfiguration->getRepositoryImageServerBucket()];
            foreach ($buckets as $bucket) {
                if ($this->s3Service->doesBucketExist($bucket)) {
                    $objects = $this->s3Service->listObjectsNamesOnly($bucket);
                    if (!empty($objects)) {
                        foreach ($objects as $obj) {
                            $this->s3Service->deleteObject($bucket, $obj);
                        }
                    }
                    $this->s3Service->deleteBucket($bucket);
                }
                $this->s3Service->createBucket($bucket);
            }
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage());
        }

    }

}
new PrepareServicesTest()->run();
