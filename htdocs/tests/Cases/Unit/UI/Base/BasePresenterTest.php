<?php declare(strict_types=1);

namespace Tests\Cases\Unit\UI\Base;

use App\Bootstrap;
use App\Services\AppConfiguration;
use App\Services\EntityServices\MaintenanceService;
use App\UI\Base\BasePresenter;
use Latte\Runtime\Template;
use Tester\Assert;

require_once __DIR__ . '/../../../../bootstrap.php';


test('BasePresenter::beforeRender sets template variables correctly', function (): void {
    // Mock services

    $container = Bootstrap::boot()->createContainer();
    $config = $container->getByType(AppConfiguration::class);

    $maintenanceService =  $container->getByType(MaintenanceService::class);

    // Anonymous class extending BasePresenter
    $presenter = new class($config) extends BasePresenter {
        public $template;
        public $calledParent = false;

        public function beforeRender(): void {
            parent::beforeRender();
        }

        protected function afterRender(): void
        {}
        public function beforeRenderParentCalled() {
            $this->calledParent = true;
        }
    };

    $presenter->maintenanceService = $maintenanceService;
    $presenter->template = new \stdClass();
    $presenter->beforeRender();

    Assert::same('development', $presenter->template->platform);
    Assert::same('unknown version', $presenter->template->version);
    Assert::false($presenter->template->dbSsl);
});
