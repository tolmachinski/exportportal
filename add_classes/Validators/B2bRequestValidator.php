<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Database\Model;
use App\Common\Contracts\B2B\B2bRequestLocationType;
use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use Symfony\Component\HttpFoundation\ParameterBag;
use TinyMVC_Library_validator;
use ValueError;

final class B2bRequestValidator extends Validator
{
    private Model $categoryRepository;
    private Model $partnersTypeRepository;

    public function __construct(ValidatorAdapter $validatorAdapter)
    {
        $this->categoryRepository = \model(\Item_Category_Model::class);
        $this->partnersTypeRepository = \model(\Partners_Types_Model::class);

        parent::__construct($validatorAdapter);
    }

    protected function rules(): array
    {
        $fields = $this->getFields();
        $labels = $this->getLabels();
        $messages = $this->getMessages();

        return [
            [
                'field' => $fields->get('p_type'),
                'label' => $labels->get('p_type'),
                'rules' => $this->getPartnerTypeRules($messages),
            ],
            [
                'field' => $fields->get('company_branch'),
                'label' => $labels->get('company_branch'),
                'rules' => $this->getCompanyBranchRules($messages),
            ],
            [
                'field' => $fields->get('title'),
                'label' => $labels->get('title'),
                'rules' => $this->getTitleRules($messages),
            ],
            [
                'field' => $fields->get('message'),
                'label' => $labels->get('message'),
                'rules' => $this->getMessageRules($messages),
            ],
            [
                'field' => $fields->get('accept_tc'),
                'label' => $labels->get('accept_tc'),
                'rules' => $this->getTermsRules($messages),
            ],
            [
                'field' => $fields->get('industriesSelected'),
                'label' => $labels->get('industriesSelected'),
                'rules' => $this->getIndustryRules($messages),
            ],
            [
                'field' => $fields->get('categoriesSelected'),
                'label' => $labels->get('categoriesSelected'),
                'rules' => $this->getCategoriesRules($messages, $fields),
            ],
            [
                'field' => $fields->get('type_location'),
                'label' => $labels->get('type_location'),
                'rules' => $this->getTypeLocationRules($messages),
            ],
            [
                'field' => $fields->get('tags'),
                'label' => $labels->get('tags'),
                'rules' => $this->getTagsRules($messages),
            ],
        ];
    }

    protected function fields(): array
    {
        return [
            'p_type'             => 'p_type',
            'company_branch'     => 'company_branch',
            'title'              => 'title',
            'message'            => 'message',
            'accept_tc'          => 'accept_tc',
            'industriesSelected' => 'industriesSelected',
            'categoriesSelected' => 'categoriesSelected',
            'type_location'      => 'type_location',
            'tags'               => 'tags',
        ];
    }

    protected function labels(): array
    {
        return [
            'p_type'             => 'Partner\'s type',
            'company_branch'     => 'Company/branch',
            'title'              => 'Title',
            'message'            => 'Message',
            'accept_tc'          => 'Terms and conditions',
            'industriesSelected' => 'Industry',
            'categoriesSelected' => 'Category',
            'type_location'      => 'Locate Your Partner(s)',
            'tags'               => 'Tags',
        ];
    }

    protected function messages(): array
    {
        return [
            'tags.notAllAreValid'        => translate('validation_b2b_request_tags_not_valid'),
            'type_location.invalid'      => translate('validation_b2b_request_not_valid_type_location'),
            'industriesSelected.invalid' => translate('validation_b2b_request_not_existing_industry'),
            'p_type.required'            => translate('validation_is_required'),
        ];
    }

    protected function getPartnerTypeRules(ParameterBag $messages): array
    {
        return [
            'required' => $messages->get('p_type.required') ?? '',
            'integer'  => $messages->get('p_type.integer') ?? '',
            function (string $attr, $value, callable $fail) use ($messages) {
                if(empty($value)){
                    return null;
                }
                if (0 === $this->partnersTypeRepository->countAllBy(['scopes' => ['id' => (int) $value]])) {
                    $fail(sprintf($messages->get('p_type.required', ''), $attr));
                }
            },
        ];
    }

    protected function getCompanyBranchRules(ParameterBag $messages): array
    {
        return [
            'required' => $messages->get('company_branch.required') ?? '',
        ];
    }

    protected function getTitleRules(ParameterBag $messages, int $minLength = 3, int $maxLength = 255): array
    {
        return [
            'required'              => $messages->get('title.required') ?? '',
            "min_len[{$minLength}]" => $messages->get('title.min_len') ?? '',
            "max_len[{$maxLength}]" => $messages->get('title.max_len') ?? '',
        ];
    }

    protected function getMessageRules(ParameterBag $messages, int $maxLength = 3000): array
    {
        return [
            'required'                   => $messages->get('message.required') ?? '',
            "html_max_len[{$maxLength}]" => $messages->get('message.html_max_len') ?? '',
        ];
    }

    protected function getTermsRules(ParameterBag $messages): array
    {
        return [
            'required' => $messages->get('accept_tc.required') ?? '',
        ];
    }

    protected function getIndustryRules(ParameterBag $messages): array
    {
        return [
            'required' => $messages->get('industriesSelected.required') ?? '',
            function (string $attr, $value, callable $fail) use ($messages) {
                if (empty($value)) {
                    return;
                }

                if (is_a($value, \App\Common\Validation\NestedValidationData::class)) {
                    $seletedIndustries = iterator_to_array($value->getIterator());
                }

                $countSelectedIndustries = count($seletedIndustries);

                $seletedIndustries = array_filter(array_unique(array_map(
                    fn ($industryId) => (int) $industryId,
                    $seletedIndustries
                )));

                $industriesCount = $this->categoryRepository->countAllBy([
                    'conditions' => [
                        'ids'    => $seletedIndustries,
                        'parent' => 0,
                    ],
                ]);

                if ($industriesCount != $countSelectedIndustries) {
                    $fail($messages->get('industriesSelected.invalid'));
                }
            },
        ];
    }

    protected function getCategoriesRules(ParameterBag $messages, ParameterBag $fields): array
    {
        return [
            'required' => $messages->get('categoriesSelected.required') ?? '',
            function (string $attr, $value, callable $fail) use ($messages, $fields) {
                if (empty($value)) {
                    return;
                }

                /** @var \NestedValidationData $seletedIndustries */
                $seletedIndustries = $this->getValidationData()->get($fields->get('industriesSelected'));

                if (is_a($seletedIndustries, \App\Common\Validation\NestedValidationData::class)) {
                    $seletedIndustries = iterator_to_array($seletedIndustries->getIterator());
                }

                if (is_a($value, \App\Common\Validation\NestedValidationData::class)) {
                    $selectedCategories = iterator_to_array($value->getIterator());
                }
                $countSelectedCategories = count($selectedCategories);

                $selectedCategories = array_filter(array_unique(array_map(
                    fn ($categoryId) => (int) $categoryId,
                    $selectedCategories
                )));

                $countValidCategories = $this->categoryRepository->countAllBy([
                    'conditions' => [
                        'ids'       => $selectedCategories,
                        'parentIds' => $seletedIndustries,
                    ],
                ]);

                if ($countSelectedCategories != $countValidCategories) {
                    $fail($messages->get('industriesSelected.invalid'));
                }
            },
        ];
    }

    protected function getTypeLocationRules(ParameterBag $messages): array
    {
        return [
            'required' => $messages->get('type_location.required') ?? '',
            function (string $attr, $value, callable $fail) use ($messages) {
                if (null === $value) {
                    return;
                }

                try {
                    B2bRequestLocationType::from($value);
                } catch (ValueError $e) {
                    $fail($messages->get('type_location.invalid'));
                }
            },
        ];
    }

    protected function getTagsRules(ParameterBag $messages): array
    {
        return [
            'required' => $messages->get('tags.required') ?? '',
            function (string $attr, $tags, callable $fail, TinyMVC_Library_validator $validator) use ($messages) {
                $tags = explode(';', $tags);
                foreach ($tags as $tag) {
                    $tag = \cleanInput($tag);
                    if ($validator->valid_tag($tag)) {
                        $validTags[] = $tag;
                    }
                }

                if (count($tags) != count($validTags)) {
                    $fail($messages->get('tags.notAllAreValid'));
                }
            },
        ];
    }
}
