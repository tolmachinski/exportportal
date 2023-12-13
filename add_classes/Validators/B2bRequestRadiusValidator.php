<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use Symfony\Component\HttpFoundation\ParameterBag;

final class B2bRequestRadiusValidator extends Validator
{
    protected function rules(): array
    {
        $fields = $this->getFields();
        $labels = $this->getLabels();
        $messages = $this->getMessages();

        return [
            [
                'field' => $fields->get('radius'),
                'label' => $labels->get('radius'),
                'rules' => $this->getRadiusRules($messages),
            ],
        ];
    }

    protected function fields(): array
    {
        return [
            'radius' => 'radius',
        ];
    }

    protected function labels(): array
    {
        return [
            'radius' => 'Radius',
        ];
    }

    protected function getRadiusRules(ParameterBag $messages, int $minLength = 1, int $maxLength = 3000): array
    {
        return [
            'required'          => $messages->get('radius.required') ?? '',
            'integer'           => $messages->get('radius.integer') ?? '',
            "min[{$minLength}]" => $messages->get('radius.minAmount') ?? '',
            "max[{$maxLength}]" => $messages->get('radius.maxAmount') ?? '',
        ];
    }
}
