<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Database\Model;
use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Common\Validation\NestedValidationData;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @author Anton Zencenco
 */
class ProfileAdditionalInformationValidation extends Validator
{
    /**
     * The maximum comment length value.
     */
    private int $maxDescriptionLength;

    /**
     * The users repository.
     */
    private Model $usersRepository;

    /**
     * The principal ID value.
     */
    private int $principalId;

    /**
     * The user ID value.
     */
    private int $userId;

    /**
     * Creates the validator.
     */
    public function __construct(
        ValidatorAdapter $validator,
        Model $usersRepository,
        int $principalId,
        int $userId,
        int $maxDescriptionLength = 1000,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->userId = $userId;
        $this->principalId = $principalId;
        $this->usersRepository = $usersRepository;
        $this->maxDescriptionLength = $maxDescriptionLength;

        parent::__construct($validator, $messages, $labels, $fields);
    }

    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $fields = $this->getFields();
        $labels = $this->getLabels();
        $messages = $this->getMessages();

        return [
            [
                'field' => $fields->get('description'),
                'label' => $labels->get('description'),
                'rules' => $this->getDescriptionRules($messages, $this->maxDescriptionLength),
            ],
            [
                'field' => $fields->get('sync'),
                'label' => $labels->get('sync'),
                'rules' => $this->getSyncRules($messages, $this->usersRepository, $this->principalId, $this->userId),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'description' => 'description',
            'sync'        => 'sync_with_accounts',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'description' => 'Description',
            'sync'        => 'Apply changes to my account(s)',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return [
            'sync.invalid'      => translate('systmess_error_invalid_data'),
            'sync.userNotFound' => translate('systmess_error_invalid_data'),
        ];
    }

    /**
     * Get the description field validation rules.
     */
    protected function getDescriptionRules(ParameterBag $messages, int $maxLength): array
    {
        return [
            "max_len[{$maxLength}]" => $messages->get('documents.maxLength') ?? '',
        ];
    }

    /**
     * Get sync field rules.
     */
    protected function getSyncRules(ParameterBag $messages, Model $usersRepository, int $principalId, int $userId): array
    {
        return [
            function (string $attr, $syncWithAccounts, callable $fail) use ($messages, $usersRepository, $principalId, $userId) {
                if (null === $syncWithAccounts) {
                    return;
                }

                if (!$syncWithAccounts instanceof NestedValidationData) {
                    $fail($messages->get('sync.invalid') ?? '');

                    return;
                }

                $relatedAccounts = $usersRepository->findAllBy([
                    'columns' => [$usersRepository->getPrimaryKey()],
                    'scopes'  => ['principal' => $principalId, 'notId' => $userId],
                ]);
                $userAccounts = array_column($relatedAccounts, $usersRepository->getPrimaryKey(), $usersRepository->getPrimaryKey());
                foreach ($syncWithAccounts->getIterator() as $accountId) {
                    if (!isset($userAccounts[$accountId])) {
                        $fail($messages->get('sync.userNotFound') ?? '');
                    }
                }
            },
        ];
    }
}
