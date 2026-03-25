<?php declare(strict_types=1);

namespace App\Services\Solr;

use App\Services\AppConfiguration;

class SolrClientService
{
    protected string $baseUrl;

    public function __construct(
        private readonly AppConfiguration $appConfiguration,
    )
    {
        $this->baseUrl = $this->appConfiguration->getSolrBasePath() . '/solr/specimens';
    }


}
