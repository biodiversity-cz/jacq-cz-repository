<?php

declare(strict_types=1);

namespace Tests\Cases\Unit\Services\OaiPmh\MetadataFormat;

use App\Bootstrap;
use App\Services\OaiPmh\MetadataFormat\CcmmFormat;
use Nette\Application\LinkGenerator;
use Tester\Assert;

require_once __DIR__.'/../../../../../bootstrap.php';

test('CcmmFormat: getMetadataPrefix returns ccmm', function (): void {
    $container = Bootstrap::boot()->createContainer();
    $linkGenerator = $container->getByType(LinkGenerator::class);
    $format = new CcmmFormat($linkGenerator);

    Assert::same('ccmm-xml', $format->getMetadataPrefix());
});

test('CcmmFormat: getSchema returns placeholder URL', function (): void {
    $container = Bootstrap::boot()->createContainer();
    $linkGenerator = $container->getByType(LinkGenerator::class);
    $format = new CcmmFormat($linkGenerator);

    Assert::same('https://model.ccmm.cz/research-data/dataset/schema.xsd', $format->getSchema());
});

test('CcmmFormat: getMetadataNamespace returns placeholder namespace', function (): void {
    $container = Bootstrap::boot()->createContainer();
    $linkGenerator = $container->getByType(LinkGenerator::class);
    $format = new CcmmFormat($linkGenerator);

    Assert::same('https://schema.ccmm.cz/research-data/1.1', $format->getMetadataNamespace());
});

test('CcmmFormat: getFormatName returns descriptive name', function (): void {
    $container = Bootstrap::boot()->createContainer();
    $linkGenerator = $container->getByType(LinkGenerator::class);
    $format = new CcmmFormat($linkGenerator);

    Assert::same('Czech Core Metadata Model v1.1', $format->getFormatName());
});

register_shutdown_function(function (): void {
    \Mockery::close();
});
