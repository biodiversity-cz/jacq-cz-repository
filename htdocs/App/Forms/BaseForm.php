<?php declare(strict_types = 1);

namespace App\Forms;

use Contributte\FormsBootstrap\BootstrapForm;
use Nette\Forms\Controls\TextInput;

class BaseForm extends BootstrapForm
{

    public function addNumeric(string $name, ?string $label = null): TextInput
    {
        $input = self::addText($name, $label);
        $input->addCondition(self::Filled)
            ->addRule(self::MaxLength, null, 255)
            ->addRule(self::Numeric);

        return $input;
    }

}
