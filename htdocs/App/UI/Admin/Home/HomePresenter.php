<?php

declare(strict_types=1);

namespace App\UI\Admin\Home;

use App\UI\Base\SecuredPresenter;

final class HomePresenter extends SecuredPresenter
{
    public function renderDefault(): void
    {
        $this->template->title = 'Admin';
    }
}
