<?php declare(strict_types = 1);

namespace App\Forms;

use App\Facades\CuratorFacade;
use Nette\Application\UI\Form;

final readonly class ImportFormFactory
{

    public function __construct(private FormFactory $formFactory, private CuratorFacade $curatorFacade) { }

    public function create(): Form
    {
        $form = $this->formFactory->forBackend();
        $form->addSelect('photoType', 'Type:', $this->curatorFacade->getAllPhotoTypes())->setPrompt('----')->setRequired();
        $form->addSubmit('send', 'Send to repository');
        $form->onSuccess[] = [$this, 'processForm'];
        return $form;
    }

    public function processForm(Form $form, array $data): void
    {
        try {
            $this->curatorFacade->registerNewFiles($data);
        } catch (\Throwable $exception) {
           $form->addError('An error occurred: ' . $exception->getMessage());
        }
    }

}
