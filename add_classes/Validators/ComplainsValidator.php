<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;

final class ComplainsValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        return array(
            array(
                'field' => 'id_theme',
                'label' => translate('report_company_popup_form_theme_label'),
                'rules' => array('required' => '')
            ),
            array(
                'field' => 'text',
                'label' => translate('report_company_popup_form_message_label'),
                'rules' => array('required' => '', 'max_len[500]' => '')
            )
        );
    }
}
