<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Database\Model;
use App\Common\Validation\FlatValidationData;
use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Common\Validation\ValidationDataInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\ParameterBag;
use Countries_Model;

class B2bRequestPartnerCountriesValidator extends Validator
{
    /**
     * The raw countries list.
     *
     * @var mixed
     */
    private $countriesList;

    /**
     * The number that indicates maximum amount of the countries. If null then amount is unlimited.
     */
    private ?int $maxCountries;

    /**
     * The validation data for countries.
     */
    private ValidationDataInterface $countriesValidationData;

    /**
     * The codes repository.
     */
    private Model $countryRepository;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        ValidatorAdapter $validatorAdapter,
        array $countriesList = [],
        ?int $maxCountries = null,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->maxCountries = $maxCountries;
        $this->countriesList = $countriesList;
        $this->countriesValidationData = new FlatValidationData();

        $this->countryRepository = model(Countries_Model::class);
        parent::__construct($validatorAdapter, $messages, $labels, $fields);
    }

    /**
     * {@inheritdoc}
     */
    public function validate($validationData): bool
    {
        $validationData = $validationData instanceof ValidationDataInterface ? $validationData : new FlatValidationData($validationData ?? []);
        $validationData->merge($this->getCountriesValidationData());

        return parent::validate($validationData);
    }

    /**
     * Get the raw countries list.
     *
     * @var mixed
     */
    public function getCountriesList()
    {
        return $this->countriesList;
    }

    /**
     * Get the number that indicates maximum amount of the countries. If null then amount is unlimited.
     */
    public function getMaxCountries(): ?int
    {
        return $this->maxCountries;
    }

    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $messages = $this->getMessages();

        return $this->makeCountriesRules($this->getCountriesList(), $this->getMaxCountries(), $messages);
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return [
            'countries.required'            => translate('validation_b2b_at_least_one_country_required'),
            'countries.notEmpty'            => translate('validation_b2b_country_list_empty'),
            'countries.duplicate'           => translate('validation_b2b_country_has_duplicates'),
            'countries.country.required'    => translate('validation_b2b_country_required_for_one'),
            'countries.country.notExisting' => translate('validation_b2b_country_not_exist'),
        ];

    }

    /**
     * Get the validation data for countries.
     */
    protected function getCountriesValidationData(): ValidationDataInterface
    {
        return $this->countriesValidationData;
    }

    private function makeCountriesRules($countriesList, ?int $maxCountries, ParameterBag $messages): array
    {
        if (empty($countriesList)) {
            return [
                [
                    'field' => 'countries',
                    'rules' => [
                        'required' => $messages->get('countries.required'),
                    ],
                ],
            ];
        }

        $rules = [];
        $validationData = $this->getCountriesValidationData();
        $validationData->set('countries', is_array($countriesList) ? new ArrayCollection($countriesList) : $countriesList);

        $rules[] = [
            'field' => 'countries',
            'rules' => [
                'required' => $messages->get('countries.required'),
                function (string $attr, Collection $value, callable $fail) use ($messages) {
                    if (
                        $value->count() > 0
                        && array_unique($value->getValues()) != $value->getValues()
                    ) {
                        $fail($messages->get('countries.duplicate'));
                    }
                },
            ],
        ];

        if (!is_array($countriesList)) {
            return $rules;
        }

        foreach ($countriesList as $countryIndex => $country)
        {
            $countryDisplayIndex = $countryIndex + 1;
            $validationData->set("countries:{$countryIndex}.country", $country ?? null);
            $rules[] = [
                'field' => "countries:{$countryIndex}.country",
                'rules' => [
                    'required' => sprintf($messages->get('countries.country.required'), $countryDisplayIndex),
                    function (string $attr, $value, callable $fail) use ($messages, $countryDisplayIndex) {
                        if (empty($value)) {
                            return;
                        }

                        if (empty($this->countryRepository->findOne($value))) {
                            $fail(sprintf($messages->get('countries.country.notExisting'), $countryDisplayIndex));
                        }
                    },
                ],
            ];
        }
        return $rules;
    }
}
