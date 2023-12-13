<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;

final class SampleOrderTrackingInfoValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        return array(
            array(
                'rules' => array('required' => '', 'max_len[1000]' => ''),
                'field' => 'track_info',
                'label' => 'Tracking info',
            ),
        );
    }
}
