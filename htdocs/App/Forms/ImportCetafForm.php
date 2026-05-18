<?php

declare(strict_types=1);

namespace App\Forms;

use App\Exceptions\ImportValuesException;
use App\Services\CetafSidImportService;
use Nette\Application\AbortException;
use Nette\Application\LinkGenerator;
use Nette\Application\UI\Form;

class ImportCetafForm
{
    public const int VALIDATION = 0;
    public const int IMPORT = 1;

    public function __construct(protected readonly FormFactory $factory, protected readonly CetafSidImportService $service, protected readonly LinkGenerator $linkGenerator)
    {
    }

    public function validationFormSucceeded(Form $form, $values)
    {
        try {
            $processedRowsCount = $this->service->validate($values);
            $form->getPresenter()->flashMessage('Validation is ok, '.$processedRowsCount.' rows processed');
            $form->getPresenter()->redirect(':import');
        } catch (ImportValuesException  $e) {
            $form->getPresenter()->flashMessage($e->getMessage(), 'error');
            $form->getPresenter()->redirect(':errors');
        } catch (\Exception  $e) {
            $form->addError($e->getMessage());
            if ($e instanceof AbortException) {
                throw $e;
            }
        }
    }

    public function importFormSucceeded(Form $form, $values)
    {
        try {
            $this->service->import($values);
            $form->getPresenter()->flashMessage('Specimens were successfully imported/updated');
            $form->getPresenter()->redirect('Cetaf:');
        } catch (ImportValuesException  $e) {
            $form->getPresenter()->flashMessage($e->getMessage());
            $form->getPresenter()->redirect(':errors');
        } catch (\Exception  $e) {
            $form->addError($e->getMessage());
            if ($e instanceof AbortException) {
                throw $e;
            }
        }
    }

    public function create(int $type = self::VALIDATION): BaseForm
    {
        $form = $this->factory->forBackend();

        $form->addUpload('table', 'DwC XLSX file')
            ->addRule(Form::MaxFileSize, 'error.size', 1024 * 1024 * 11 / 10)
            ->setRequired('missingFile')
            ->setHtmlAttribute('class', 'form-control-file');

        if (self::VALIDATION === $type) {
            $form->onSuccess[] = [$this, 'validationFormSucceeded'];
            $form->addSubmit('send', 'Validate')
                ->setHtmlAttribute('class', 'btn btn-primary');
        } else {
            $form->onSuccess[] = [$this, 'importFormSucceeded'];
            $form->addSubmit('send', 'Import specimens')
                ->setHtmlAttribute('class', 'btn btn-primary');
        }

        return $form;
    }
}
