<?php

declare(strict_types=1);

namespace App\Validators;

use Symfony\Component\HttpFoundation\ParameterBag;
use App\Common\Validation\Legacy\Standalone\Validator;
use Timezone_Model;

final class ClickToCallMainDataValidator extends Validator
{
	protected const MAX_REASON_LENGTH = 250;

	/**
	 * {@inheritdoc}
	 */
	protected function rules(): array
	{
		$fields = $this->getFields();
		$labels = $this->getLabels();
		$messages = $this->getMessages();

		return [
			'timezone'   => [
				'field' => $fields->get('timezone'),
				'label' => $labels->get('timezone'),
				'rules' => $this->getTimezoneRules($messages),
			],
			'message' => [
				'field' => $fields->get('message'),
				'label' => $labels->get('message'),
				'rules' => $this->getMessageRules(static::MAX_REASON_LENGTH, $messages),
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	protected function fields(): array
	{
		return [
			'fname'       => 'timezone',
			'lname'       => 'message',
		];
	}

	/**
	 * {@inheritdoc}
	 */
	protected function labels(): array
	{
		return [
			'fname'       => 'Timezone',
			'lname'       => 'Message',
		];
	}

	/**
	 * {@inheritdoc}
	 */
	protected function messages(): array
	{
		return [
			'timezone.valid' => translate('validation_not_valid_timezone'),
		];
	}

	/**
	 * Get the email validation rule.
	 */
	protected function getTimezoneRules(ParameterBag $messages): array
	{
		return [
			'required' => $messages->get('country.required') ?? '',
			function ($attr, $value, $fail) use ($messages) {
				/** @var $timezoneModel Timezone_Model::class*/
				$timezoneModel = model(Timezone_Model::class);

				if (!empty($value) && !$timezoneModel->findOne($value)) {
					$fail(sprintf($messages->get('timezone.valid', ''), $attr));
				}
			},
		];
	}

	/**
	 * Get the comment validation rule.
	 */
	protected function getMessageRules(int $maxLength, ParameterBag $messages): array
	{
		return [
			'required'               => $messages->get('message.required') ?? '',
			"max_len[{$maxLength}]"  => $messages->get('message.maxLength') ?? '',
		];
	}
}
