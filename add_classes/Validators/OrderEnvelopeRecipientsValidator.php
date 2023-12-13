<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\FlatValidationData;
use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Common\Validation\ValidationDataInterface;
use App\Envelope\RecipientTypesAwareTrait;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DomainException;
use Symfony\Component\HttpFoundation\ParameterBag;
use User_Model;

use const App\Common\PUBLIC_DATE_FORMAT;

class OrderEnvelopeRecipientsValidator extends Validator
{
    use RecipientTypesAwareTrait;

    /**
     * The raw recipients list.
     *
     * @var mixed
     */
    private $recipientsList;

    /**
     * The number that indicates maximum amount of the recipients. If null then amount is unlimited.
     */
    private ?int $maxRecipients;

    /**
     * The date format for expiratio dates.
     */
    private string $dateFormat;

    /**
     * The users repository.
     */
    private User_Model $usersRepository;

    /**
     * The validation data for recipients.
     */
    private ValidationDataInterface $recipientsValidationData;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        ValidatorAdapter $validatorAdapter,
        User_Model $usersRepository,
        array $recipientsList = [],
        int $maxRecipients = null,
        string $dateFormat = PUBLIC_DATE_FORMAT,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->dateFormat = $dateFormat;
        $this->maxRecipients = $maxRecipients;
        $this->recipientsList = $recipientsList;
        $this->usersRepository = $usersRepository;
        $this->recipientsValidationData = new FlatValidationData();

        parent::__construct($validatorAdapter, $messages, $labels, $fields);
    }

    /**
     * {@inheritdoc}
     */
    public function validate($validationData): bool
    {
        $validationData = $validationData instanceof ValidationDataInterface ? $validationData : new FlatValidationData($validationData ?? []);
        $validationData->merge($this->getRecipientsValidationData());

        return parent::validate($validationData);
    }

    /**
     * Get the raw recipients list.
     *
     * @var mixed
     */
    public function getRecipientsList()
    {
        return $this->recipientsList;
    }

    /**
     * Get the number that indicates maximum amount of the recipients. If null then amount is unlimited.
     */
    public function getMaxRecipients(): ?int
    {
        return $this->maxRecipients;
    }

    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $messages = $this->getMessages();

        return $this->makeRecipientsRules($this->getRecipientsList(), $this->getMaxRecipients(), $messages);
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return [
            'recipients.required'                        => 'At least one recipient is required.',
            'recipients.malformed'                       => 'The recipients list is malformed. Please try to select them anew.',
            'recipients.notEmpty'                        => 'The recipients list is empty. Please try to select them anew.',
            'recipients.tooMuch'                         => 'The recipients list must contain to more than %s entries. Please try to select them anew.',
            'recipients.type.requred'                    => 'The recipient type #%s is required.',
            'recipients.type.invalidType'                => 'The recipient type #%s is invalid.',
            'recipients.expiresAt.validDate'             => 'The recipient due date #%s is invalid.',
            'recipients.expiresAt.minDate'               => 'The recipient min due date #%s is today',
            'recipients.expiresAt.maxDate'               => 'The recipient max due date #%s is ' . config('envelope_document_max_calendar_days', 60) . ' days from today',
            'recipients.order.requred'                   => 'The recipient order #%s is required.',
            'recipients.order.invalidType'               => 'The recipient order #%s is invalid.',
            'recipients.order.groups.tooMuch'            => 'The recipients cannot have the same order.',
            'recipients.assignee.requred'                => 'The recipient #%s is required.',
            'recipients.assignee.invalidType'            => 'The recipient #%s is malformed. Please try to select them anew.',
            'recipients.assignees.notFound'              => 'At least one of the recipients is not found. Please try to select them anew.',
        ];
    }

    /**
     * Get the validation data for recipients.
     */
    protected function getRecipientsValidationData(): ValidationDataInterface
    {
        return $this->recipientsValidationData;
    }

    private function makeRecipientsRules($recipientsList, ?int $maxRecipients, ParameterBag $messages): array
    {
        $rules = [];
        $validationData = $this->getRecipientsValidationData();
        $validationData->set('recipients:list', is_array($recipientsList) ? new ArrayCollection($recipientsList) : $recipientsList);
        if (empty($recipientsList)) {
            return [
                [
                    'field' => 'recipients:list',
                    'rules' => [
                        'required' => $messages->get('recipients.required'),
                    ],
                ],
            ];
        }

        $rules[] = [
            'field' => 'recipients:list',
            'rules' => [
                'required' => $messages->get('recipients.required'),
                function (string $attr, $value, callable $fail) use ($messages) {
                    if (!empty($value) && !$value instanceof Collection) {
                        $fail(sprintf($messages->get('recipients.malformed'), $attr));
                    }
                },
                function (string $attr, $value, callable $fail) use ($messages) {
                    if (!empty($value) && $value instanceof Collection && 0 === $value->count()) {
                        $fail(sprintf($messages->get('recipients.notEmpty'), $attr));
                    }
                },
                function (string $attr, $value, callable $fail) use ($messages, $maxRecipients) {
                    if (
                        !empty($value)
                        && $value instanceof Collection
                        && null !== $maxRecipients
                        && $value->count() > $maxRecipients
                    ) {
                        $fail(sprintf($messages->get('recipients.tooMuch'), $maxRecipients));
                    }
                },
            ],
        ];

        if (!is_array($recipientsList)) {
            return $rules;
        }

        $validationData->set('recipients:list:order.groups', new ArrayCollection(\arrayByKey($recipientsList, 'order', true)));
        $rules[] = [
            'field' => 'recipients:list:order.groups',
            'rules' => [
                function (string $attr, Collection $groups, callable $fail) use ($messages) {
                    if ($groups->isEmpty()) {
                        return;
                    }

                    foreach ($groups as $group) {
                        if (\count($group) > 1) {
                            $fail(sprintf($messages->get('recipients.order.groups.tooMuch'), $attr));

                            return;
                        }
                    }
                },
            ],
        ];
        $knownAssignees = [];
        foreach ($recipientsList as $recipientIndex => $recipient) {
            $recipientDisplayIndex = $recipientIndex + 1;

            $validationData->set("recipients:list:{$recipientIndex}.type", $recipient['type'] ?? null);
            $rules[] = [
                'field' => "recipients:list:{$recipientIndex}.type",
                'rules' => [
                    'required' => sprintf($messages->get('recipients.type.requred'), $recipientDisplayIndex),
                    function (string $attr, $type, callable $fail) use ($messages, $recipientDisplayIndex) {
                        if (empty($type)) {
                            return;
                        }

                        try {
                            $this->assertValidRecipientType((string) $type);
                        } catch (DomainException $e) {
                            $fail(sprintf($messages->get('recipients.type.invalidType'), $recipientDisplayIndex));
                        }
                    },
                ],
            ];

            $validationData->set("recipients:list:{$recipientIndex}.order", $recipient['order'] ?? null);
            $rules[] = [
                'field' => "recipients:list:{$recipientIndex}.order",
                'rules' => [
                    'required' => sprintf($messages->get('recipients.order.requred'), $recipientDisplayIndex),
                    'integer'  => sprintf($messages->get('recipients.order.invalidType'), $recipientDisplayIndex),
                ],
            ];

            $validationData->set("recipients:list:{$recipientIndex}.expiresAt", $recipient['expiresAt'] ?? null);
            $rules[] = [
                'field' => "recipients:list:{$recipientIndex}.expiresAt",
                'rules' => [
                    "valid_date[{$this->dateFormat}]" => sprintf($messages->get('recipients.expiresAt.validDate'), $recipientDisplayIndex),
                    function (string $attr, $value, callable $fail) use ($messages, $recipientDisplayIndex) {
                        if (empty($value)) {
                            return;
                        }

                        try {
                            $expirationDate = DateTimeImmutable::createFromFormat($this->dateFormat, (string) $value);
                        } catch (\Throwable $e) {
                            $expirationDate = null;
                        } finally {
                            $expirationDate = $expirationDate ?: null;
                        }

                        if (null !== $expirationDate && $expirationDate < new DateTimeImmutable()) {
                            $fail(sprintf($messages->get('recipients.expiresAt.validDate'), $recipientDisplayIndex));

                            return;
                        }
                    },
                    function (string $attr, $value, callable $fail) use ($messages, $recipientDisplayIndex) {
                        if (empty($value)) {
                            return;
                        }

                        try {
                            $expirationDate = DateTimeImmutable::createFromFormat($this->dateFormat, (string) $value);
                        } catch (\Throwable $e) {
                            $expirationDate = null;
                        } finally {
                            $expirationDate = $expirationDate ?: null;
                        }

                        if (null !== $expirationDate && $expirationDate > new DateTimeImmutable((int) config('envelope_document_max_calendar_days', 60) . ' days')) {
                            $fail(sprintf($messages->get('recipients.expiresAt.maxDate'), $recipientDisplayIndex));

                            return;
                        }
                    },
                ],
            ];

            $knownAssignees[] = $recipient['assignee'] ?? null;
            $validationData->set("recipients:list:{$recipientIndex}.assignee", $recipient['assignee'] ?? null);
            $rules[] = [
                'field' => "recipients:list:{$recipientIndex}.assignee",
                'rules' => [
                    'required' => sprintf($messages->get('recipients.assignee.requred'), $recipientDisplayIndex),
                    'integer'  => sprintf($messages->get('recipients.assignee.invalidType'), $recipientDisplayIndex),
                ],
            ];
        }

        $knownAssignees = \array_map(fn ($assignee) => $assignee, array_filter($knownAssignees, fn ($assignee) => null !== $assignee));
        if (!empty($knownAssignees)) {
            $validationData->set('recipients:list:assignees', new ArrayCollection($knownAssignees));
            $rules[] = [
                'field' => 'recipients:list:assignees',
                'rules' => [
                    function (string $attr, Collection $assignees, callable $fail) use ($messages) {
                        if ($assignees->isEmpty()) {
                            return;
                        }

                        if (empty($this->usersRepository->getSimpleUsers(\array_map(fn ($id) => (int) $id, $assignees->getValues())))) {
                            $fail(sprintf($messages->get('recipients.assignees.notFound')));
                        }
                    },
                ],
            ];
        }

        return $rules;
    }
}
