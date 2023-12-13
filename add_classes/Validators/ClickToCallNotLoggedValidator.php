<?php

declare(strict_types=1);

namespace App\Validators;

use Symfony\Component\HttpFoundation\ParameterBag;
use App\Common\Validation\Legacy\Standalone\Validator;

final class ClickToCallNotLoggedValidator extends Validator
{
	protected const MIN_NAME_LENGTH = 2;

	protected const MAX_NAME_LENGTH = 50;

	protected const MAX_EMAIL_LENGTH = 100;

	/**
	 * {@inheritdoc}
	 */
	protected function rules(): array
	{
		$fields = $this->getFields();
		$labels = $this->getLabels();
		$messages = $this->getMessages();

		return [
			'fname'   => [
				'field' => $fields->get('fname'),
				'label' => $labels->get('fname'),
				'rules' => $this->getFirstNameRules(static::MIN_NAME_LENGTH, static::MAX_NAME_LENGTH, $messages),
			],
			'lname'   => [
				'field' => $fields->get('lname'),
				'label' => $labels->get('lname'),
				'rules' => $this->getLastNameRules(static::MIN_NAME_LENGTH, static::MAX_NAME_LENGTH, $messages),
			],
			'email' => [
				'field' => $fields->get('email'),
				'label' => $labels->get('email'),
				'rules' => $this->getEmailRules($messages),
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	protected function fields(): array
	{
		return [
			'fname'       => 'fname',
			'lname'       => 'lname',
			'email'       => 'email',
		];
	}

	/**
	 * {@inheritdoc}
	 */
	protected function labels(): array
	{
		return [
			'fname'       => 'First Name',
			'lname'       => 'Last Name',
			'email'       => 'Email',
		];
	}

	/**
	 * {@inheritdoc}
	 */
	protected function messages(): array
	{
		return [
			'country.valid' => translate('validation_country_valid'),
		];
	}

	/**
	 * Get the first name field validation rules.
	 */
	protected function getFirstNameRules(int $minLength, int $maxLength, ParameterBag $messages): array
	{
		return [
			'required'              => $messages->get('fname.required') ?? '',
			"min_len[{$minLength}]" => $messages->get('fname.minLength') ?? '',
			"max_len[{$maxLength}]" => $messages->get('fname.maxLength') ?? '',
			'valid_user_name'       => $messages->get('fname.validName') ?? '',
		];
	}

	/**
	 * Get the last name field validation rules.
	 */
	protected function getLastNameRules(int $minLength, int $maxLength, ParameterBag $messages): array
	{
		return [
			'required'              => $messages->get('lname.required') ?? '',
			"min_len[{$minLength}]" => $messages->get('lname.minLength') ?? '',
			"max_len[{$maxLength}]" => $messages->get('lname.maxLength') ?? '',
			'valid_user_name'       => $messages->get('lname.validName') ?? '',
		];
	}

	/**
	 * Get the email validation rule.
	 */
	protected function getEmailRules(ParameterBag $messages): array
	{
		return [
			'required'       => $messages->get('email.required') ?? '',
			'no_whitespaces' => $messages->get('email.noWhitespaces') ?? '',
			'valid_email'    => $messages->get('email.valid') ?? '',
			'max_len[254]'   => $messages->get('email.maxSize') ?? '',
		];
	}
}
