<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;

final class CompanyLocationValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        return array(
            array(
                'label' => 'lat',
                'rules' => array(
                    'required' => translate('validation_company_map_marker_position'),
                ),
            ),
            array(
                'label' => 'lng',
                'rules' => array(
                    'required' => translate('validation_company_map_marker_position'),
                ),
            ),
        );
    }
}
