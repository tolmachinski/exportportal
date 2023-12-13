<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;

final class RestoreCompanyNameValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        return [
            [
                'field' => 'company_legal_name',
                'label' => 'Company Legal Name',
                'rules' => ['required' => '', 'min_len[3]' => '', 'max_len[50]' => '', 'company_title' => ''],
            ],
            [
                'field' => 'company_name',
                'label' => 'Company Name',
                'rules' => ['required' => '', 'min_len[3]' => '', 'max_len[50]' => '', 'company_title' => ''],
            ],
        ];
    }
}
