<?php declare(strict_types=1);

namespace App\UI\Front\Ark;

use App\Services\RepositoryConfiguration;
use App\Services\SpecimenIdService;
use App\UI\Base\UnsecuredPresenter;

final class ArkPresenter extends UnsecuredPresenter
{

    /** @inject */
    public RepositoryConfiguration $repositoryConfiguration;

    /** @inject */
    public SpecimenIdService $specimenIdService;

    /**
     * works only with value of ark, without protocol&naan prefix
     * synergic with \App\Services\SpecimenIdService::generateArk
     */
    public function actionDefault(string $value): void
    {
        $settings = $this->repositoryConfiguration->getArkProperties();

        if ($value === $settings['shoulder'] . $settings['repository']) {
            $this->redirect('Home:');
        }


        $parts = explode('/', $value);
        switch (count($parts)) {
            case 2:
                $this->redirect('Repository:herbarium', $parts[1]);
                break;
            case 3:
                $specimenID = $this->specimenIdService->searchSpecimenIdByArk($value, false);
                $this->redirect('Repository:specimen', $specimenID);
                break;
            case 4:
                $this->redirect('Repository:image', $parts[3]);
                break;
            default:
                $this->redirect('Home:');
        }
    }
}
