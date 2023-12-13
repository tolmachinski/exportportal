<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Common\Validation\Standalone\AggregateValidator;
use App\Common\Validation\NestedValidationData;
use User_Model;

final class UserProfilePreferencesValidator extends Validator
{
    /**
     * List of merged validator.
     *
     * @var AggregateValidator
     */
    private $mergedValidators;

    /**
     * Indicates if extended set of rules is used.
     *
     * @var bool
     */
    private $extendedRules = false;

    /**
     * Creates the address validator.
     */
    public function __construct(ValidatorAdapter $validator, ?array $fields = null, ?array $messages = null, ?array $labels = null, bool $extended = false)
    {
        $this->extendedRules = $extended;
        $mergedValidators = [];
        if ($extended) {
            $mergedValidators[] = new PhoneValidator($validator, $messages['phone'] ?? [], $labels['phone'] ?? [], $fields['phone'] ?? []);
            if ($fields['fax'] ?? null) {
                $mergedValidators[] = new PhoneValidator($validator, $messages['fax'] ?? [], $labels['fax'] ?? [], $fields['fax'] ?? []);
            }
            $mergedValidators[] = new AddressValidator($validator, $messages['address'] ?? [], $labels['address'] ?? [], $fields['address'] ?? []);

        }
        $this->mergedValidators = new AggregateValidator($mergedValidators);

        parent::__construct($validator, $messages, $labels, $fields);
    }

    /**
     * {@inheritdoc}
     */
    public function validate($validationData): bool
    {
        $hasBaseViolations = !parent::validate($validationData);
        $hasAttachedViolations = !$this->mergedValidators->validate($validationData);
        if ($hasAttachedViolations) {
            $this->getViolations()->merge(
                $this->mergedValidators->getViolations()
            );
        }

        return !$hasBaseViolations && !$hasAttachedViolations;
    }

    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $messages = $this->getMessages();
        $labels = $this->getLabels();
        $fields = $this->getFields();
        $rules = [
            [
                'field' => 'fname',
                'label' => 'First Name',
                'rules' => ['required' => '', 'valid_user_name' => '', 'min_len[2]' => '', 'max_len[50]' => ''],
            ],
            [
                'field' => 'lname',
                'label' => 'Last Name',
                'rules' => ['required' => '', 'valid_user_name' => '', 'min_len[2]' => '', 'max_len[50]' => ''],
            ],
            [
                'field' => 'sync_with_accounts',
                'label' => 'Apply changes to my account(s)',
                'rules' => [
                    function (string $attr, $syncWithAccounts, callable $fail) {
                        if (null === $syncWithAccounts) {
                            return;
                        }

                        if (!$syncWithAccounts instanceof NestedValidationData) {
                            $fail(translate('systmess_error_invalid_data'));
                            return;
                        }

                        /** @var User_Model $userModel */
                        $userModel = model(User_Model::class);

                        $currentUserId = id_session();
                        $userAccounts = $userModel->get_related_users_by_id_principal(principal_id());
                        $userAccounts = array_column($userAccounts, null, 'idu');
                        $otherRelatedAccounts = array_diff_key($userAccounts, [$currentUserId => $currentUserId]);

                        foreach ($syncWithAccounts->getIterator() as $accountId) {
                            if (!isset($otherRelatedAccounts[$accountId])) {
                                $fail(translate('systmess_error_invalid_data'));
                            }
                        }
                    }
                ],
            ],
        ];

        return !$this->extendedRules ? $rules : array_merge($rules, [
            [
                'field' => 'description',
                'label' => 'Description',
                'rules' => ['max_len[1000]' => ''],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'city'       => 'City',
            'state'      => 'State / Region',
            'country'    => 'Country',
            'address'    => 'Address',
            'postalCode' => 'Zip/Postal Code',
        ];
    }
}
