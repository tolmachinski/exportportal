<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\FlatValidationData;
use App\Common\Validation\Legacy\Standalone\Validator;

final class TranslationDescriptionValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        return [
            [
                'field' => 'translation_description',
                'label' => 'Translation',
                'rules' => ['html_max_len[20000]' => ''],
            ]
        ];
    }
}
