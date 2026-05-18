<?php

declare(strict_types=1);

namespace App\Api\PubV1;

use Apitte\Core\Annotation\Controller as Apitte;
use App\Services\AppConfiguration;

#[Apitte\Path('/system')]
#[Apitte\Tag('System')]
class SystemController extends BasePubV1Controller
{
    public function __construct(protected readonly AppConfiguration $appConfiguration)
    {
    }

    #[Apitte\OpenApi('summary: Get core version')]
    #[Apitte\Path('/version')]
    #[Apitte\Method('GET')]
    #[Apitte\Response(description: 'Success', code: '200')]
    public function version(): string
    {
        return $this->appConfiguration->getVersion();
    }

    #[Apitte\Path('/platform')]
    #[Apitte\Method('GET')]
    #[Apitte\OpenApi('summary: Get core platform')]
    #[Apitte\Response(description: 'Success', code: '200')]
    public function platform(): string
    {
        return $this->appConfiguration->getPlatform();
    }
}
