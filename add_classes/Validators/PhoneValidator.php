<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Database\Model;
use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\HttpFoundation\ParameterBag;

final class PhoneValidator extends Validator
{
    /**
     * The flag that indicates that the field is required.
     */
    private bool $required;

    /**
     * The codes repository.
     */
    private Model $codesRepository;

    /**
     * The cached phone codes.
     */
    private array $phoneCodes = [];

    /**
     * Creates the validator.
     */
    public function __construct(
        ValidatorAdapter $validator,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null,
        bool $required = true
    ) {
        $this->required = $required;
        $this->codesRepository = \model(\Phone_Codes_Model::class);

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
                'field' => $fields->get('code') ?? 'code',
                'label' => $labels->get('code'),
                'rules' => $this->getPhoneCodesRules($messages, $this->required),
            ],
            [
                'field' => $fields->get('phone') ?? 'phone',
                'label' => $labels->get('phone'),
                'rules' => $this->getPhoneRules($messages, $fields, $this->required),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'code'  => 'code',
            'phone' => 'phone',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'code'  => 'Phone code',
            'phone' => 'Phone',
        ];
    }

    /**
     * Get the phone code rules.
     */
    private function getPhoneCodesRules(ParameterBag $messages, bool $isRequired): array
    {
        $rules = [];
        if ($isRequired) {
            $rules['required'] = $messages->get('code.required') ?? '';
        }
        $rules[] = function ($attr, $phoneCodeId, $fail) use ($messages) {
            if (empty($phoneCodeId)) {
                return;
            }

            if (null === $this->getPhoneCode((int) $phoneCodeId)) {
                $fail($messages->get('code.invalid'));
            }
        };

        return $rules;
    }

    /**
     * Get the phone code rules.
     */
    private function getPhoneRules(ParameterBag $messages, ParameterBag $fields, bool $isRequired): array
    {
        $rules = [];
        if ($isRequired) {
            $rules['required'] = $messages->get('code.required') ?? '';
        }
        $rules[] = function ($attr, $phone, $fail) use ($fields, $messages, $isRequired) {
            if (!$isRequired && empty($phone)) {
                return;
            }

            $phoneUtil = PhoneNumberUtil::getInstance();
            $phoneCode = $this->getPhoneCode((int) $this->getValidationData()->get($fields->get('code') ?? 'code'));
            $rawNumber = trim(\sprintf('%s %s', $phoneCode['ccode'] ?? '', $phone ?? ''));

            try {
                if (!$phoneUtil->isViablePhoneNumber($rawNumber)) {
                    $fail($messages->get('phone.invalid'));
                }
                if (!$phoneUtil->isValidNumber($phoneUtil->parse($rawNumber))) {
                    $fail($messages->get('phone.unacceptable'));
                }
            } catch (NumberParseException $e) {
                $fail($messages->get('phone.invalid'));
            }
        };

        return $rules;
    }

    /**
     * Get cached phone code.
     */
    private function getPhoneCode(int $phoneCodeId): ?array
    {
        if (!isset($this->phoneCodes[$phoneCodeId])) {
            $this->phoneCodes[$phoneCodeId] = $this->codesRepository->findOne($phoneCodeId);
        }

        return $this->phoneCodes[$phoneCodeId];
    }
}
