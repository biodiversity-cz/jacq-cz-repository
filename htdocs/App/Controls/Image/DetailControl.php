<?php declare(strict_types=1);

namespace App\Controls\Image;

use App\Facades\CuratorFacade;
use App\Model\Database\Entity\Photos;
use App\Services\DatabotsService;
use App\Services\EntityServices\PhotoService;
use Nette\Application\UI\Control;
use Nette\Neon\Exception;
use Nette\Security\User;

class DetailControl extends Control
{

    private Photos $photo;

    public function __construct(private int $id, private PhotoService $photoService, private CuratorFacade $curatorFacade, private readonly User $user,  protected DatabotsService $databotsService)
    {
        $this->photo = $this->photoService->getPhoto($this->user, $this->id);
    }

    public function create(): self
    {
        return $this;
    }

    public function render(bool $forPublic = true, bool $fullInfo = false): void
    {
        $template = $this->template;
        $template->photo = $this->photo;
        $template->coreInfo = ['EXIF'=>$this->photo->exif, 'Identify' => $this->photo->identify];
        $template->databots = [];//$this->databotsService->databotResultsToArray($this->photo);
//        $template->metric = $this->databotsService->getQualityEvaluation($this->photo);
        if ($forPublic) {
            $template->setFile(__DIR__ . '/detail_front.latte');
        } else {
            if ($fullInfo) {
                $template->setFile(__DIR__ . '/detail_adminFull.latte');
            } else {
                $template->setFile(__DIR__ . '/detail_admin.latte');
            }

        }

        $template->render();
    }

    public function handleDelete(): void
    {
        try {
            $this->curatorFacade->deletePhoto($this->user, $this->photo);
        } catch (Exception $e) {
            $this->presenter->flashMessage($e->getMessage(), 'danger');
        }

        $this->redirect('this');
    }

}
