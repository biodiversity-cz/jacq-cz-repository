<?php declare(strict_types = 1);

namespace App\UI\Admin\Herbarium;

use App\Facades\CuratorFacade;
use App\Grids\ImportedPhotosGrid;
use App\Model\Database\Entity\Photos;
use App\Services\EntityServices\HerbariumService;
use App\UI\Base\SecuredPresenter;

final class HerbariumPresenter extends SecuredPresenter
{

    /** @inject */ public HerbariumService $herbariumService;


    public function renderDefault(): void
    {
        $this->template->title = 'Herbarium overview';
        $this->template->herbarium = $this->herbariumService->find($this->user->getIdentity()->herbarium);
    }


}
