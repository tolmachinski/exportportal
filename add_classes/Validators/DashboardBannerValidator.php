<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Contracts\Group\GroupType;
use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Common\Validation\ValidationDataInterface;
use User_Groups_Model;

class DashboardBannerValidator extends Validator
{
    /** @var User_Groups_Model $userGroupsModel */
    protected $userGroupsModel;

    public function __construct(
        ValidatorAdapter $validatorAdapter,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->userGroupsModel = model(User_Groups_Model::class);

        parent::__construct($validatorAdapter, $messages, $labels, $fields);
    }

    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $fields = $this->getFields();
        $labels = $this->getLabels();

        return [
            'title' => [
                'field' => $fields->get('title'),
                'label' => $labels->get('title'),
                'rules' => $this->getTitleRules(),
            ],
            'subtitle' => [
                'field' => $fields->get('subtitle'),
                'label' => $labels->get('subtitle'),
                'rules' => $this->getSubtitleRules(),
            ],
            'link' => [
                'field' => $fields->get('link'),
                'label' => $labels->get('link'),
                'rules' => $this->getLinkRules(),
            ],
            'button' => [
                'field' => $fields->get('button'),
                'label' => $labels->get('button'),
                'rules' => $this->getButtonRules(),
            ],
            'user_groups' => [
                'field' => $fields->get('user_groups'),
                'label' => $labels->get('user_groups'),
                'rules' => $this->getUserGroupsRules(),

            ],
        ];
    }
    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'title'         => 'title',
            'subtitle'      => 'subtitle',
            'link'          => 'link',
            'button'        => 'button',
            'user_groups'   => 'user_groups',
        ];
    }
    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'title'        => 'Title',
            'subtitle'     => 'Subtitle',
            'link'         => 'Link',
            'button'       => 'Text Button',
            'user_groups'  => 'Groups',
        ];
    }
    /**
     * Get the title validation rule.
     */
    protected function getTitleRules(): array
    {
        return [
            'required'               => '',
            "max_len[255]"           => '',
        ];
    }
     /**
     * Get the subtitle validation rule.
     */
    protected function getSubtitleRules(): array
    {
        return [
            'required'               => '',
            "max_len[255]"           => '',
        ];
    }
     /**
     * Get the link validation rule.
     */
    protected function getLinkRules(): array
    {
        return [
            'required'               => '',
            "max_len[255]"           => '',
            'valid_url'              => '',
        ];
    }
     /**
     * Get the button validation rule.
     */
    protected function getButtonRules(): array
    {
        return [
            'required'               => '',
            "max_len[50]"            => '',
        ];
    }
    /**
     * Get the groups validation rule.
     */
    protected function getUserGroupsRules(): array
    {
        return [
            'required' => '',
            function (string $attr, $selectedUserGroupsIds, callable $fail) {
                if (!is_a($selectedUserGroupsIds, ValidationDataInterface::class)) {
                    $fail(translate('systmess_error_invalid_data'));

                    return;
                }

                $validUserGroupsIds = array_column(
                    $this->userGroupsModel->findAllBy([
                        'conditions' => [
                            'aliases' => GroupType::from(GroupType::EP_CLIENTS)->aliases(),
                        ],
                    ]),
                    'idgroup'
                );

                foreach ($selectedUserGroupsIds as $selectedId) {
                    if (!in_array($selectedId, $validUserGroupsIds)) {
                        $fail(translate('systmess_error_invalid_data'));

                        return;
                    }
                }

                if (count($selectedUserGroupsIds) !== count(array_unique(iterator_to_array($selectedUserGroupsIds->getIterator())))) {
                    $fail(translate('systmess_error_invalid_data'));

                    return;
                }
            },
        ];
    }
}
