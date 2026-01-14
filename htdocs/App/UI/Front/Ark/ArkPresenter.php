<?php declare(strict_types=1);

namespace App\UI\Front\Ark;

use App\Services\RepositoryConfiguration;
use App\UI\Base\UnsecuredPresenter;

final class ArkPresenter extends UnsecuredPresenter
{

    /** @inject */
    public RepositoryConfiguration $repositoryConfiguration;

    /**
     * works only with value of ark, without protocol&naan prefix
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
                $this->redirect('Repository:specimen', $parts[2]);
                break;
            case 4:
                $this->redirect('Repository:image', $parts[3]);
                break;
            default:
                $this->redirect('Home:');
        }
    }
}
