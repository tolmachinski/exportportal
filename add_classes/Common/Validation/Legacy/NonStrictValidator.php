<?php

namespace App\Common\Validation\Legacy;

use App\Common\Validation\ConstraintInterface;
use App\Common\Validation\DelegatedValidatorInterface;
use App\Common\Validation\Legacy\Constraints\NullConstraint;
use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Standalone\AbstractValidator;
use App\Common\Validation\Standalone\ValidatorInterface;
use Doctrine\Common\Collections\ArrayCollection;
use TinyMVC_Library_validator;

final class NonStrictValidator extends AbstractValidator
{
    /**
     * The validator-source.
     *
     * @var Validator
     */
    private $sourceValidator;

    /**
     * The list of ignored rules.
     *
     * @var Array<string,string[]>
     */
    private $ignoredRules = array('*' => array());

    /**
     * The list of the fields that will be skipped on wrapping.
     *
     * @var array
     */
    private $skippedFields = array();

    /**
     * The flag that determines if empty values must be skipped.
     *
     * @var bool
     */
    private $skipEmpty;

    /**
     * Creates instance of non-strict validator.
     *
     * @param Array<string,string[]> $ignoredRules
     */
    public function __construct(ValidatorInterface $sourceValidator, ?array $ignoredRules = array(), array $skippedFields = array(), bool $skipEmpty = false)
    {
        $this->skipEmpty = $skipEmpty;
        $this->ignoredRules = $ignoredRules;
        $this->skippedFields = $skippedFields;
        $this->sourceValidator = $sourceValidator;

        parent::__construct(
            $this->prepareConstraints($this->sourceValidator->getConstraints()),
            $sourceValidator instanceof DelegatedValidatorInterface
                ? $sourceValidator->getDelegatedValidator()
                : null
        );
    }

    /**
     * Gets the list of ignored rules.
     *
     * @return Array<string,string[]>
     */
    protected function getIgnoredRules(): array
    {
        return $this->ignoredRules ?? array('*' => array());
    }

    /**
     * Gets the list of the fields that will be skipped on wrapping.
     */
    protected function getSkippedFields(): array
    {
        return $this->skippedFields ?? array();
    }

    private function prepareConstraints(ConstraintListInterface $baseList): ConstraintListInterface
    {
        $constraints = new ConstraintList();
        $ignoredRules = $this->getIgnoredRules();
        $skippedFields = $this->getSkippedFields();
        /** @var ConstraintInterface $constraint */
        foreach ($baseList as $key => $constraint) {
            // If rules are names - then we can skip wrapping the fields right here.
            if (is_string($key) && in_array($key, $skippedFields)) {
                $constraints->set($key, clone $constraint);

                continue;
            }

            if ($constraint instanceof NullConstraint) {
                // In fact we can filter only legacy rules, the constraints created with classes are not yet supported
                /** @var NullConstraint $constraint */
                $ruleset = $constraint->getMetadata();

                // In the case when rules are not named, we can try to skip field here by rule 'field' value.
                if (in_array($ruleset['field'], $skippedFields)) {
                    $constraints = clone $constraint;
                } else {
                    // Else, just wrap the rules
                    $ruleset['rules'] = $this->wrapValidationRules($ruleset['rules'] ?? array(), $ignoredRules[$key] ?? $ignoredRules['*'] ?? array());
                    if (empty($ruleset['rules'])) {
                        continue;
                    }

                    $constraint = new NullConstraint($ruleset);
                }
            }

            $constraints->set($key, $constraint);
        }

        return $constraints;
    }

    /**
     * Wraps around validation rules.
     */
    private function wrapValidationRules(array $rules, array $skip = array()): array
    {
        $ruleset = (new ArrayCollection($rules))
            ->filter(function ($callback, $key) use ($skip) {
                return !((is_string($key) && in_array($key, $skip, true)) || (is_callable($callback) && in_array($key, $skip, true)));
            })
        ;

        $wrapped = array();
        foreach ($ruleset as $key => $callback) {
            $args = array();
            $isStatic = true;
            if (is_string($key)) {
                $rule = $key;
                $message = !empty($callback) ? $callback : null;
                if (preg_match('/(.*?)\\[(.*?)\\]/', $rule, $match)) {
                    $rule = $match[1];
                    $args[] = $match[2] ?? null;
                }
            } elseif (is_callable($callback)) {
                $rule = $callback;
                $isStatic = false;
            } else {
                continue;
            }

            $wrapped[] = function (string $attr, $value, callable $fail, TinyMVC_Library_validator $validator) use ($rule, $args, $isStatic, $message) {
                if ($this->skipEmpty && (null === $value || '' === $value)) {
                    return;
                }

                if ($isStatic) {
                    if (method_exists($validator, $rule) && !$validator->{$rule}($value, ...$args)) {
                        $fail(sprintf(
                            str_replace('%d', '%s', $message ?? $validator->get_rule_message($rule)),
                            $attr,
                            ...$args
                        ));
                    }
                } else {
                    $rule->call($this, $attr, $value, $fail, $validator);
                }
            };
        }

        return $wrapped;
    }
}
