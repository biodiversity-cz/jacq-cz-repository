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
        $this->cleanSolr();
        Assert::equal(1,1);
    }

    protected function cleanDb():void{
        $connection = $this->em->getConnection();
        $schema = 'public';
        $connection->executeStatement("DROP SCHEMA IF EXISTS {$schema} CASCADE");
        $connection->executeStatement("DROP SCHEMA IF EXISTS front CASCADE");
        $connection->executeStatement("DROP SCHEMA IF EXISTS databots CASCADE");
        $connection->executeStatement("DROP SCHEMA IF EXISTS cache CASCADE");
        $connection->executeStatement("DROP SCHEMA IF EXISTS cetaf CASCADE");
        $connection->executeStatement("CREATE SCHEMA {$schema}");

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

            $buckets = [self::BUCKET_HERBARIUM,
                $this->repositoryConfiguration->getRepositoryArchiveBucketPrefix().'-01',
                $this->repositoryConfiguration->getRecentlyUsedArchiveBucket(),
                $this->repositoryConfiguration->getRepositoryDatabotThumbsBucketPrefix().'-01',
                $this->repositoryConfiguration->getRecentlyUsedDatabotThumbsBucket(),
                $this->repositoryConfiguration->getRepositoryImageServerBucketPrefix().'-01',
                $this->repositoryConfiguration->getRecentlyUsedImageServerBucket(),];
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

    protected function cleanSolr():void
    {
        $update = $this->solrClientService->client->createUpdate();

        $update->addDeleteQuery('*:*');
        $update->addCommit();

        $this->solrClientService->client->update($update);
    }

}
new PrepareServicesTest()->run();
