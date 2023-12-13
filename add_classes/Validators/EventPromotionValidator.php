<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\FlatValidationData;
use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Common\Validation\ValidationDataInterface;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\ParameterBag;

class EventPromotionValidator extends Validator
{
    /**
     * The minimal date.
     */
    private \DateTimeInterface $minDate;

    /**
     * The maximum date.
     */
    private \DateTimeInterface $maxDate;

    /**
     * The list of intervals of dates.
     *
     * @var \DateTimeInterface[][]
     */
    private array $listOfIntervals;

    /**
     * The date format.
     */
    private string $dateFormat;

    /**
     * Creates legacy validator.
     */
    public function __construct(
        ValidatorAdapter $validator,
        \DateTimeInterface $minDate,
        \DateTimeInterface $maxDate,
        array $listOfIntervals = [],
        string $dateFormat = 'm/d/Y H:i',
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->minDate = $minDate;
        $this->maxDate = $maxDate;
        $this->listOfIntervals = $listOfIntervals;
        $this->dateFormat = $dateFormat;

        parent::__construct($validator, $messages, $labels, $fields);
    }

    /**
     * {@inheritdoc}
     */
    public function validate($validationData): bool
    {
        $fields = $this->getFields();
        $validationData = $validationData instanceof ValidationDataInterface ? $validationData : new FlatValidationData($validationData ?? []);
        $validationData->merge(new FlatValidationData([
            'interval' => (
                (new ArrayCollection([
                    $validationData->get($fields->get('startDate')) ?? null,
                    $validationData->get($fields->get('endDate')) ?? null,
                ]))->map(fn (?string $v) => null === $v ? $v : (DateTimeImmutable::createFromFormat($this->dateFormat, $v) ?: null))
            ),
        ]));

        return parent::validate($validationData);
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
                'field' => $fields->get('startDate'),
                'label' => $labels->get('startDate'),
                'rules' => $this->getStartDateRules($messages, $this->minDate, $this->maxDate),
            ],
            [
                'field' => $fields->get('endDate'),
                'label' => $labels->get('endDate'),
                'rules' => $this->getEndDateRules($messages, $this->minDate, $this->maxDate),
            ],
            [
                'field' => $fields->get('interval'),
                'label' => $labels->get('interval'),
                'rules' => $this->getIntervalRules($messages, $this->listOfIntervals),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'startDate' => 'start_date',
            'endDate'   => 'end_date',
            'interval'  => 'interval',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'startDate' => 'Start date',
            'endDate'   => 'End date',
            'interval'  => 'Interval',
        ];
    }

    /**
     * Returns the messages preset array.
     */
    protected function messages(): array
    {
        return [
            'startDate.required'      => '',
            'startDate.validDate'     => '',
            'startDate.lessThanMin'   => 'The field %s cannot be less than %s date',
            'startDate.greaterThaMax' => 'The field %s cannot be greater than %s date',
            'endDate.required'        => '',
            'endDate.validDate'       => '',
            'endDate.lessThanMin'     => 'The field %s cannot be less than %s date',
            'endDate.greaterThaMax'   => 'The field %s cannot be greater than %s date',
            'interval.malformed'      => 'The start and end dates are invalid.',
            'interval.mismatch'       => 'The start date cannot be less that the end date.',
            'interval.notFit'         => 'The provided date interval cannot be added - it overlaps with other promotions.',
        ];
    }

    protected function getDateFormat(): string
    {
        return $this->dateFormat;
    }

    /**
     * Get the start date validation rules.
     */
    protected function getStartDateRules(ParameterBag $messages, \DateTimeInterface $minDate, \DateTimeInterface $maxDate): array
    {
        return [
            'required'                             => $messages->get('startDate.required') ?? '',
            "valid_date[{$this->getDateFormat()}]" => $messages->get('startDate.validDate') ?? '',
            function (string $attr, $value, callable $fail) use ($messages, $minDate) {
                if (null !== $value && $this->makeDateFromRawValue($value) < $minDate) {
                    $fail(sprintf($messages->get('startDate.lessThanMin'), $attr, $minDate->format($this->getDateFormat())));
                }
            },
            function (string $attr, $value, callable $fail) use ($messages, $maxDate) {
                if (null !== $value && $this->makeDateFromRawValue($value) > $maxDate) {
                    $fail(sprintf($messages->get('startDate.greaterThaMax'), $attr, $maxDate->format($this->getDateFormat())));
                }
            },
        ];
    }

    /**
     * Get the end date validation rules.
     */
    protected function getEndDateRules(ParameterBag $messages, \DateTimeInterface $minDate, \DateTimeInterface $maxDate): array
    {
        return [
            'required'                             => $messages->get('endDate.required') ?? '',
            "valid_date[{$this->getDateFormat()}]" => $messages->get('endDate.validDate') ?? '',
            function (string $attr, $value, callable $fail) use ($messages, $minDate) {
                if (null !== $value && $this->makeDateFromRawValue($value) < $minDate) {
                    $fail(\sprintf($messages->get('endDate.lessThanMin'), $attr, $minDate->format($this->getDateFormat())));
                }
            },
            function (string $attr, $value, callable $fail) use ($messages, $maxDate) {
                if (null !== $value && $this->makeDateFromRawValue($value) > $maxDate) {
                    $fail(\sprintf($messages->get('endDate.greaterThaMax'), $attr, $maxDate->format($this->getDateFormat())));
                }
            },
        ];
    }

    /**
     * Get the rules for interval.
     */
    private function getIntervalRules(ParameterBag $messages, array $listOfIntervals): array
    {
        return [
            function (string $attr, Collection $newInterval, callable $fail) use ($messages) {
                if (2 !== \count(\array_filter($newInterval->getValues()))) {
                    $fail(\sprintf($messages->get('interval.malformed'), $attr));
                }
            },
            function (string $attr, Collection $newInterval, callable $fail) use ($messages) {
                $values = \array_filter($newInterval->getValues());
                if (2 === \count($values) && $values[0] > $values[1]) {
                    $fail(\sprintf($messages->get('interval.mismatch'), $attr));
                }
            },
            function (string $attr, Collection $newInterval, callable $fail) use ($messages, $listOfIntervals) {
                $values = \array_filter($newInterval->getValues());
                if (
                    2 === \count($values)
                    && $values[0] < $values[1]
                    && !$this->canInsertNewIntervalWithoutOverlaps(
                        $listOfIntervals,
                        $newInterval->map(fn ($v) => $this->makeDateFromRawValue($v))->getValues()
                    )
                ) {
                    $fail(\sprintf($messages->get('interval.notFit'), $attr));
                }
            },
        ];
    }

    /**
     * Makes the datetime object from raw value.
     *
     * @param null|\DateTimeInterface|string $value
     */
    private function makeDateFromRawValue($value): ?\DateTimeInterface
    {
        if (null === $value || $value instanceof \DateTimeImmutable) {
            return $value;
        }
        $dateTime = \DateTimeImmutable::createFromFormat($this->getDateFormat(), $value);
        if (!$dateTime) {
            $dateTime = \date_create_immutable($value);
        }

        if (!$dateTime) {
            throw new \RuntimeException(\sprintf('Could not convert the date value "%s" to the datetime object', $value ?? 'null'));
        }

        return $dateTime;
    }

    /**
     * Determines if the provided interval can be inserted into the existing list of intervals.
     */
    private function canInsertNewIntervalWithoutOverlaps(array $listOfIntervals, array $newInterval): bool
    {
        // Count the amount of intervals in the list
        $n = count($listOfIntervals);

        // If set is empty then simply insert $newInterval and return.
        if (0 === $n) {
            return true;
        }

        // New interval can be inserted on the edges of the list
        if ($newInterval[1] < $listOfIntervals[0][0] || $newInterval[0] > $listOfIntervals[$n - 1][1]) {
            return true;
        }

        // Check if we can insert new interval without overlaps with other intervals
        for ($i = 0; $i < $n; ++$i) {
            // Check if the new intervals overlaps with any interval in the list
            if (min($listOfIntervals[$i][1], $newInterval[1]) >= max($listOfIntervals[$i][0], $newInterval[0])) {
                return false;
            }
        }

        return true;
    }
}
