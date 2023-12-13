<?php

namespace App\Entities\Phones;

use App\Common\Contracts\Entities\CountryCodeInterface;
use App\Common\Contracts\Entities\CountryInterface;
use App\Common\Contracts\Entities\Phone\PatternsAwareInterface;
use DomainException;

final class CountryCode implements CountryCodeInterface, PatternsAwareInterface
{
    /**
     * The coutry code ID value.
     *
     * @var null|int
     */
    private $id;

    /**
     * The country code name value.
     *
     * @var null|string
     */
    private $name;

    /**
     * The county.
     *
     * @var null|CountryInterface
     */
    private $country;

    /**
     * the phone patterns.
     *
     * @var null|string[]
     */
    private $phonePatterns;

    /**
     * Creates the instance of the country code.
     *
     * @param int              $id
     * @param string           $name
     * @param CountryInterface $country
     */
    public function __construct($id = null, $name = null, CountryInterface $country = null, ?array $phonePatterns = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->country = $country;
        $this->phonePatterns = $phonePatterns;
    }

    /**
     * Returns the instance ID value.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the instance with provided ID value.
     *
     * @param int $id
     *
     * @throws DomainException if value is not of type int
     *
     * @return static
     */
    public function withId($id)
    {
        if ('integer' !== gettype($id)) {
            throw new DomainException(sprintf('The argument 1 in the function %s() must be of type int.', __METHOD__));
        }

        return $this->copyInstanceWithParameter('id', $id);
    }

    /**
     * Returns the instance without ID value.
     *
     * @return static
     */
    public function withoutId()
    {
        return $this->copyInstanceWithParameter('id', null);
    }

    /**
     * Returns the name value.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the instance with provided name value.
     *
     * @param string $name
     *
     * @throws DomainException if value is not of type string
     *
     * @return static
     */
    public function withName($name)
    {
        if ('string' !== gettype($name)) {
            throw new DomainException(sprintf('The argument 1 in the function %s() must be of type string.', __METHOD__));
        }

        return $this->copyInstanceWithParameter('name', $name);
    }

    /**
     * Returns the instance without name value.
     *
     * @return static
     */
    public function withoutName()
    {
        return $this->copyInstanceWithParameter('name', null);
    }

    /**
     * Returns the country value.
     *
     * @return null|CountryInterface
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Returns the instance with provided country value.
     *
     * @return static
     */
    public function withCountry(CountryInterface $country)
    {
        return $this->copyInstanceWithParameter('name', $country);
    }

    /**
     * Returns the instance without country value.
     *
     * @return static
     */
    public function withoutCountry()
    {
        return $this->copyInstanceWithParameter('country', null);
    }

    /**
     * Returns the pattern by provided type.
     */
    public function getPattern(?int $type): ?string
    {
        return $this->phonePatterns[$type] ?? null;
    }

    /**
     * Returns the phone patterns.
     */
    public function getPatterns(): array
    {
        return $this->phonePatterns ?? array();
    }

    /**
     * Returns the instance with provided phone patterns.
     *
     * @return static
     */
    public function withPatterns(array $phonePatterns): PatternsAwareInterface
    {
        return $this->copyInstanceWithParameter('phonePatterns', $phonePatterns);
    }

    /**
     * Returns the instance without phone patterns.
     *
     * @return static
     */
    public function withoutPatterns(): PatternsAwareInterface
    {
        return $this->copyInstanceWithParameter('phonePatterns', null);
    }

    /**
     * Makes the copy of the instance with the provided paramter value.
     *
     * @param string $paramterName
     * @param mixed  $value
     *
     * @return static
     */
    private function copyInstanceWithParameter($paramterName, $value = null)
    {
        $newInstance = clone $this;
        $newInstance->{$paramterName} = $value;

        return $newInstance;
    }
}
