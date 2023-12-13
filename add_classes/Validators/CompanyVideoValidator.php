<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;

final class CompanyVideoValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        return array(
            array(
                'field' => 'video',
                'label' => 'Video',
                'rules' => array(
                    'valid_url'    => '',
                    'max_len[200]' => '',
                    function ($attr, $url, $fail) {
                        if (empty($url)) {
                            return;
                        }

                        if (false === library('videothumb')->getVID($url)) {
                            $fail(library('videothumb')->lexicon('video_err_nf'));
                        }
                    },
                ),
            ),
        );
    }
}
