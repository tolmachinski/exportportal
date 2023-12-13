<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\FlatValidationData;
use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Common\Validation\ValidationDataInterface;
use App\Envelope\RecipientTypesAwareTrait;
use Symfony\Component\HttpFoundation\ParameterBag;

class AdminRecipientDueDateValidator extends Validator
{
    use RecipientTypesAwareTrait;

    /**
     * The raw recipients list.
     *
     * @var mixed
     */
    private $recipientsList;

    /**
     * The validation data for recipients.
     */
    private ValidationDataInterface $recipientsValidationData;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        ValidatorAdapter $validatorAdapter,
        array $recipientsList = [],
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->recipientsList = $recipientsList;
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
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $messages = $this->getMessages();

        return $this->makeRecipientsRules($this->getRecipientsList(), $messages);
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return [
            'recipients.expiresAt.validDate' => 'The recipient due date #%s is invalid.',
            'recipients.expiresAt.minDate'   => 'The recipient min due date #%s is today',
            'recipients.expiresAt.maxDate'   => 'The recipient max due date #%s is ' . config('envelope_document_max_calendar_days', 60) . ' days from today',
            'recipients.expiresAt.required'  => 'The recipient due date #%s is required.',
        ];
    }

    /**
     * Get the validation data for recipients.
     */
    protected function getRecipientsValidationData(): ValidationDataInterface
    {
        return $this->recipientsValidationData;
    }

    private function makeRecipientsRules($recipientsList, ParameterBag $messages): array
    {
        $rules = [];
        $validationData = $this->getRecipientsValidationData();
        foreach ($recipientsList as $recipientIndex => $recipient) {
            $recipientDisplayIndex = $recipientIndex + 1;

            $validationData->set("recipients:list:{$recipientIndex}.expiresAt", $recipient['expiresAt'] ?? null);
            $rules[] = [
                'field' => "recipients:list:{$recipientIndex}.expiresAt",
                'rules' => [
                    'required'          => sprintf($messages->get('recipients.expiresAt.required'), $recipientDisplayIndex),
                    'valid_date[m/d/Y]' => sprintf($messages->get('recipients.expiresAt.validDate'), $recipientDisplayIndex),
                    function (string $attr, $value, callable $fail) use ($messages, $recipientDisplayIndex) {
                        if (
                            !empty($value)
                            && new \DateTime($value) < new \DateTime('today')
                        ) {
                            $fail(sprintf($messages->get('recipients.expiresAt.minDate'), $recipientDisplayIndex));
                        }
                    },
                    function (string $attr, $value, callable $fail) use ($messages, $recipientDisplayIndex) {
                        if (
                            !empty($value)
                            && new \DateTime($value) > new \DateTime((int) config('envelope_document_max_calendar_days', 60) . " days")
                        ) {
                            $fail(sprintf($messages->get('recipients.expiresAt.maxDate'), $recipientDisplayIndex));
                        }
                    },
                ],
            ];
        }

        return $rules;
    }
}
