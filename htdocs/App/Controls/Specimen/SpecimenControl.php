<?php declare(strict_types=1);

namespace App\Controls\Specimen;

use App\Facades\CuratorFacade;
use App\Model\Database\Entity\Photos;
use App\Model\Specimen\SpecimenFactory;
use App\Services\DatabotsService;
use App\Services\EntityServices\PhotoService;
use App\Services\SpecimenIdService;
use Nette\Application\UI\Control;
use Nette\Security\User;

class SpecimenControl extends Control
{


    public function __construct(private PhotoService $photoService, private CuratorFacade $curatorFacade, private readonly User $user, protected DatabotsService $databotsService, private SpecimenFactory $specimenFactory, private SpecimenIdService $specimenIdService)
    {

    }

    public function create(): self
    {
        return $this;
    }

    public function render(Photos $photo): void
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/specimen.latte');

        $template->photo = $photo;
        $specimen = $this->specimenFactory->createFromNumeric($this->user, (int) $photo->specimenId);
        $template->specimen = $specimen;
        $template->specimenPID = $this->specimenIdService->getSpecimenPid($photo);
        $this->template->maxPhotoStatus = $this->photoService->getMaxPhotoStatusOfSpecimen($this->user, $specimen);
        $template->render();
    }


}
