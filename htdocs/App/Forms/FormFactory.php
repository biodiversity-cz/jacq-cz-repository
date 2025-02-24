<?php declare(strict_types = 1);

namespace App\Forms;

use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;

final class FormFactory
{

    public function forFrontend(): BaseForm
    {
        return $this->create();
    }

    public function forBackend(): BaseForm
    {
        return $this->create();
    }

    private function create(): BaseForm
    {
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);

        return new BaseForm();
    }

}
