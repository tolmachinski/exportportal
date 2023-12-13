<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\FlatValidationData;
use App\Common\Validation\Legacy\Standalone\Validator;

final class DraftFileValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        return array(
            array(
                'field' => 'title',
                'label' => 'Title',
                'rules' => array(
                    'required' => '',
                ),
            ),
            array(
                'field' => 'file',
                'label' => 'File',
                'rules' => array(
                    'required' => '',
                    function ($attr, $value, $fail) {
                        if (empty($value) || !file_exists($value)) {
                            $fail(translate('systmess_error_uploaded_file_not_found'));
                        }
                    },
                ),
            ),
            array(
                'field' => 'records',
                'rules' => array(
                    function (string $attr, ?FlatValidationData $value, callable $fail) {
                        if (null === $value) {
                            return;
                        }

                        $records = $value->get('entries') ?? array();
                        $hasTitles = $value->get('has_titles') ?? false;
                        $baseAmount = (int) config('item_drafts_allowed_lines_amount', 100);
                        $linesAmount = $baseAmount + (int) $hasTitles;
                        if (empty($records)) {
                            $fail(translate('systmess_error_uploaded_file_cannot_be_empty'));
                        }
                        if (count($records) > $linesAmount) {
                            $fail(translate('systmess_error_max_lines_of_uploaded_file', ['{{COUNT_OF_LINES}}' => $baseAmount]));
                        }
                    },
                ),
            ),
        );
    }
}
