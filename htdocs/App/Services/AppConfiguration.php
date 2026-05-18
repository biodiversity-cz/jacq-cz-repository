<?php

declare(strict_types=1);

namespace App\Services;

use Doctrine\ORM\EntityManagerInterface;

class AppConfiguration
{
    public const string VERSION_VARIABLE = 'GIT_TAG';

    /**
     * @param mixed[] $config
     */
    public function __construct(private array $config, private EntityManagerInterface $entityManager)
    {
    }

    public function isProduction(): bool
    {
        return 'production' === $this->getPlatform();
    }

    public function getPlatform(): ?string
    {
        if (!isset($this->config['environment'])) {
            return null;
        }

        return $this->config['environment'];
    }

    public function getVersion(): string
    {
        if (false !== getenv(self::VERSION_VARIABLE)) {
            return getenv(self::VERSION_VARIABLE);
        }

        return 'unknown version';
    }

    public function isSslDbConnection(): bool
    {
        $result = $this->entityManager->getConnection()->executeQuery('SHOW ssl;')->fetchOne();

        return 'off' !== $result;
    }

    public function getDatabotBasePath(): string
    {
        return $this->config['databot']['basePath'];
    }

    public function getOpenIDProviders(string $provider): array
    {
        return $this->config['openid']['providers'][$provider];
    }
}
