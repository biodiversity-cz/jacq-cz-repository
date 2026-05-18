<?php

declare(strict_types=1);

namespace App\UI\Base;

use App\Services\AppConfiguration;
use App\Services\EntityServices\MaintenanceService;
use Nette\Application\UI\Presenter;

abstract class BasePresenter extends Presenter
{
    public const string DESTINATION_AFTER_SIGN_IN = ':Admin:Home:';
    public const string DESTINATION_AFTER_SIGN_OUT = ':Front:Home:';
    public const string DESTINATION_LOG_IN = ':Front:Sign:in';

    /** @inject */ public MaintenanceService $maintenanceService;

    public function __construct(protected readonly AppConfiguration $appConfiguration)
    {
        parent::__construct();
    }

    protected function beforeRender(): void
    {
        if ('production' !== $this->appConfiguration->getPlatform()) {
            $this->template->platform = $this->appConfiguration->getPlatform();
        }

        $this->template->version = $this->appConfiguration->getVersion();
        $this->template->dbSsl = $this->appConfiguration->isSslDbConnection();
        $this->template->maintenances = $this->maintenanceService->getValid();

        parent::beforeRender();
    }
}
