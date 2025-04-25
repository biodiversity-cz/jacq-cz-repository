<?php declare(strict_types=1);

namespace App\Services;

use Doctrine\ORM\EntityManagerInterface;

final readonly class AppConfiguration
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
        return $this->getPlatform() === 'production';
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
        if (getenv(self::VERSION_VARIABLE) !== false) {
            return getenv(self::VERSION_VARIABLE);
        }

        return 'unknown version';
    }

    public function isSslDbConnection(): bool
    {
        $result = $this->entityManager->getConnection()->executeQuery("SHOW ssl;")->fetchOne();
        return ($result !== 'off');
    }

    protected function getPdf(): array
    {
        return $this->config['pdf'];
    }

    public function getPdfGeneratorUrl(): string
    {
        return $this->getPdf()['url'];
    }

    public function getPdfBarcodeUrl(): string
    {
        return $this->getPdf()['barcodeUrl'];
    }
    public function getPdfGeneratorToken(): string
    {
        return $this->getPdf()['token'];
    }

}
