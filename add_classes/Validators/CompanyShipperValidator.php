<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;

final class CompanyShipperValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        return array(
            array(
                'field' => 'company_offices_number',
                'label' => 'Number of Office Locations',
                'rules' => array('required' => '', 'natural' => ''),
            ),
            array(
                'field' => 'company_teu',
                'label' => 'Annual full container load volume (TEU\'s)',
                'rules' => array('required' => '', 'natural' => ''),
            ),
            array(
                'field' => 'company_duns',
                'label' => 'DUNS number',
                'rules' => array('possible_duns' => ''),
            ),
        );
    }
}
