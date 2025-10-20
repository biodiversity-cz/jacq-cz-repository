<?php declare(strict_types=1);

namespace App\Forms;

use App\Model\Database\Entity\Funding;
use App\Services\EntityServices\FundingService;
use App\Services\EntityServices\HerbariumService;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Security\User;

final readonly class FundingForm
{
    public function __construct(
        private FormFactory      $formFactory,
        private FundingService   $fundingService,
        private HerbariumService $herbariumService,
        protected User           $user
    )
    {
    }

    public function formSucceeded(Form $form, $values): void
    {
        try {
            if ((isset($values['id'])) && $values['id'] != "") {
                /** @var Funding $funding */
                $funding = $this->fundingService->find((int)$values['id']);
                $funding
                    ->setName($values['name'])
                    ->setDescription($values['description'])
                    ->setCode($values['code'])
                    ->setFunder($values['funder'])
                    ->setNote($values['note'])
                    ->setActive($values['active'])
                    ->setCcmmFormat($values['ccmm_format'])
                    ->setLastEditAt();

                $resultEntity = $this->fundingService->update($this->user, $funding);
                $form->getPresenter()->flashMessage('Funding updated');
            } else {
                $funding = new Funding();
                $funding
                    ->setName($values['name'])
                    ->setDescription($values['description'])
                    ->setCode($values['code'])
                    ->setFunder($values['funder'])
                    ->setNote($values['note'])
                    ->setActive($values['active'])
                    ->setCcmmFormat($values['ccmm_format'])
                    ->setHerbarium($this->herbariumService->getCurrentUserHerbarium($this->user))
                    ->setCreatedAt()
                    ->setLastEditAt();

                $resultEntity = $this->fundingService->create($funding);
                $form->getPresenter()->flashMessage('Funding created');
            }
            $form->getPresenter()->redirect(':default');
        } catch (\Exception  $e) {
            if ($e instanceof AbortException) {
                throw $e;
            }
            $form->addError($e->getMessage());
        }
    }

    public function create(?Funding $funding = null): Form
    {
        $form = $this->formFactory->forBackend();

        $form->addText('name', 'Name:')
            ->setRequired('Please enter funding name.')
            ->addRule(Form::MaxLength, 'Name cannot exceed 255 characters.', 255);

        $form->addText('code', 'Code:')
            ->addRule(Form::MaxLength, 'Code cannot exceed 255 characters.', 255);

        $form->addText('funder', 'Funder/provider:')
            ->addRule(Form::MaxLength, 'Funder cannot exceed 255 characters.', 255);

        $form->addTextArea('description', 'Description:')
            ->addRule(Form::MaxLength, 'Description cannot exceed 65535 characters.', 65535);

        $form->addTextArea('note', 'Internal note:')
            ->addRule(Form::MaxLength, 'Note cannot exceed 65535 characters.', 65535);

        $form->addTextArea('ccmm_format', 'CCMM (XML):')
            ->addRule(Form::MaxLength, 'CCMM cannot exceed 65535 characters.', 65535);

        $form->addCheckbox('active', 'Active')
            ->setDefaultValue(true);

        $form->addSubmit('save', 'Save');

        if ($funding != null) {
            $form->addHidden("id");
            $form = $this->presetDefaultValues($form, $funding);
        }
        $form->onSuccess[] = [$this, 'formSucceeded'];

        return $form;
    }

    protected function presetDefaultValues(Form $form, Funding $funding): Form
    {
        $defaults = [
            "id" => $funding->getId(),
            'name' => $funding->getName(),
            'description' => $funding->getDescription(),
            'code' => $funding->getCode(),
            'funder' => $funding->getFunder(),
            'note' => $funding->getNote(),
            'ccmm_format' => $funding->getCcmmFormat(),
            'active' => $funding->isActive()
        ];
        return $form->setDefaults($defaults);
    }
}
