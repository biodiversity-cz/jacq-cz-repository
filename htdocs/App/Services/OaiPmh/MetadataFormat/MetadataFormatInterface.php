<?php

declare(strict_types=1);

namespace App\Services\OaiPmh\MetadataFormat;

/**
 * Interface for OAI-PMH metadata formats.
 */
interface MetadataFormatInterface
{
    /**
     * Get the metadata prefix (identifier).
     */
    public function getMetadataPrefix(): string;

    /**
     * Get the XML schema URL.
     */
    public function getSchema(): string;

    /**
     * Get the metadata namespace URI.
     */
    public function getMetadataNamespace(): string;

    /**
     * Convert a database entity to XML metadata.
     */
    public function toXml(mixed $item, string $oaiIdentifier): \DOMElement;

    /**
     * Get human-readable format name.
     */
    public function getFormatName(): string;
}
