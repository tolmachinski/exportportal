<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\ConstraintInterface;
use App\Common\Validation\Legacy\Constraints\NullConstraint;
use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use Symfony\Component\HttpFoundation\ParameterBag;

final class CompanyNamesValidator extends Validator
{
    /**
     * The flag that indicates that the legal name must be checked.
     *
     * @var bool
     */
    private $checkLegalName;

    /**
     * The flag that indicates that the display name must be checked.
     *
     * @var bool
     */
    private $checkDisplayName;

    /**
     * Creates the phone validator.
     */
    public function __construct(
        ValidatorAdapter $validator,
        bool $checkLegalName = true,
        bool $checkDisplayName = true,
        ?array $fields = null,
        ?array $labels = null,
        ?array $messages = null
    ) {
        $this->checkLegalName = $checkLegalName;
        $this->checkDisplayName = $checkDisplayName;

        parent::__construct($validator, $messages, $labels, $fields);
    }

    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $labels = $this->getLabels();
        $fields = $this->getFields();

        return \array_filter(
            [
                $this->checkLegalName ? $this->getLegalNameConstraint($fields, $labels) : null,
                $this->checkDisplayName ? $this->getDisplayNameConstraint($fields, $labels) : null,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'legalName'   => 'Company name',
            'displayName' => 'Display company name',
        ];
    }

    /**
     * Returns the legal name constraint.
     */
    private function getLegalNameConstraint(ParameterBag $fields, ParameterBag $labels): ConstraintInterface
    {
        return new NullConstraint(
            [
                'field' => $fields->get('legalName') ?? 'legalName',
                'label' => $labels->get('legalName'),
                'rules' => [
                    'required'      => '',
                    'company_title' => '',
                    'min_len[3]'    => '',
                    'max_len[50]'   => '',
                ],
            ]
        );
    }

    /**
     * Returns the display name constraint.
     */
    private function getDisplayNameConstraint(ParameterBag $fields, ParameterBag $labels): ConstraintInterface
    {
        return new NullConstraint(
            [
                'field' => $fields->get('displayName') ?? 'displayName',
                'label' => $labels->get('displayName'),
                'rules' => [
                    'required'      => '',
                    'company_title' => '',
                    'min_len[3]'    => '',
                    'max_len[50]'   => '',
                ],
            ]
        );
    }
}
