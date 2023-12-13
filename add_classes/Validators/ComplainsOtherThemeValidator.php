<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;

final class ComplainsOtherThemeValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        return array(
            array(
                'field' => 'theme',
                'label' => translate('report_company_popup_form_select_theme_other_option'),
                'rules' => array('required' => '', 'max_len[100]' => '', 'alpha_numeric' => '')
            )
        );
    }
}
