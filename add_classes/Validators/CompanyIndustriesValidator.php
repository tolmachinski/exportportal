<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use Doctrine\Common\Collections\Collection;

final class CompanyIndustriesValidator extends Validator
{
    private $limit;
    private $limit_strict;

    /**
     * Creates the phone validator.
     */
    public function __construct(
        ValidatorAdapter $validator,
        ?int $limit = 0,
        ?int $limit_strict = 0,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->limit = $limit;
        $this->limit_strict = $limit_strict;

        parent::__construct($validator, $messages, $labels, $fields);
    }

    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $rules = array(
            function ($attr, Collection $industries, $fail) {
                if ($industries->isEmpty()) {
                    $fail(translate('validation_company_industry_required'));
                }
            },
            function ($attr, Collection $industries, $fail) {
                if ($industries->isEmpty()) {
                    return;
                }

                $found_industries = model('category')->getCategories(array('columns' => 'category_id', 'cat_list' => $industries->getValues()));
                if (count($found_industries) !== $industries->count()) {
                    $fail(translate('validation_company_industry_not_found'));
                }
            },
        );

        if ($this->limit > 0) {
            $rules[] = function ($attr, Collection $industries, $fail) {
                $limit = $this->limit;

                if ($industries->count() > $limit) {
                    $fail(translate('multipleselect_max_industries', array('[COUNT]' => $limit)));
                }
            };
        }

        if ($this->limit_strict > 0) {
            $rules[] = function ($attr, Collection $industries, $fail) {
                $limit_strict = $this->limit_strict;

                if ($industries->count() !== $limit_strict) {
                    $fail(translate('pre_registration_input_message_industry_select', array('[COUNT]' => $limit_strict)));
                }
            };
        }

        return array(
            array(
                'field' => $this->getFields()->get('industries') ?? 'industries',
                'label' => 'Industries of the items you can deliver',
                'rules' => $rules,
            ),
        );
    }
}
