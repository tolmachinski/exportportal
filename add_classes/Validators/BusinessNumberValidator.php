<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;

final class BusinessNumberValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        return array(
            array(
                'field' => $this->getFields()->get('businessNumber') ?? 'business_number',
                'label' => $this->getLabels()->get('businessNumber') ?? 'Business Number',
                'rules' => array(
                    'required'    => '',
                    'min_len[3]'  => '',
                    'max_len[30]' => '',
                ),
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return array(
            'businessNumber' => 'business_number',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return array(
            'businessNumber' => 'Business Number',
        );
    }
}
