<?php declare(strict_types = 1);

namespace App\Forms;

use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;

final class FormFactory
{

	private function create(): BaseForm
	{
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);
		return new BaseForm();
	}

	public function forFrontend(): BaseForm
	{
		return $this->create();
	}

	public function forBackend(): BaseForm
	{
		return $this->create();
	}

}
