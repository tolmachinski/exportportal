<?php

namespace App\Entities\Phones;

use App\Common\Contracts\Entities\CountryInterface;
use App\Common\Contracts\Entities\ISO\ISO3166Interface;
use App\Entities\ISO\ISO3166;
use DomainException;
use SplFileInfo;

final class Country implements CountryInterface
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
     * The country ISO 3166 values.
     *
     * @var null|ISO3166
     */
    private $iso3166;

    /**
     * The county flag.
     *
     * @var null|SplFileInfo
     */
    private $flag;

    /**
     * Creates the instance of the country.
     *
     * @param null|int              $id
     * @param null|string           $name
     * @param null|SplFileInfo      $flag
     * @param null|ISO3166Interface $iso3166
     */
    public function __construct($id = null, $name = null, SplFileInfo $flag = null, ISO3166Interface $iso3166 = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->flag = $flag;
        $this->iso3166 = $iso3166;
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
     * Get the country ISO 3166 values.
     *
     * @return null|ISO3166
     */
    public function getIso3166()
    {
        return $this->iso3166;
    }

    /**
     * Returns the instance with ISO3166 value.
     *
     * @param null|ISO3166 $iso3166 the country ISO 3166 values
     *
     * @return static
     */
    public function withIso3166($iso3166)
    {
        return $this->copyInstanceWithParameter('iso3166', $iso3166);
    }

    /**
     * Returns the instance without ISO3166 value.
     *
     * @return static
     */
    public function withoutIso3166()
    {
        return $this->copyInstanceWithParameter('iso3166', null);
    }

    /**
     * Returns the flag value.
     *
     * @return null|SplFileInfo
     */
    public function getFlag()
    {
        return $this->flag;
    }

    /**
     * Returns the instance with provided flag value.
     *
     * @param SplFileInfo $flag
     *
     * @return static
     */
    public function withFlag(SplFileInfo $flag)
    {
        return $this->copyInstanceWithParameter('flag', $flag);
    }

    /**
     * Returns the instance without flag value.
     *
     * @return static
     */
    public function withoutFlag()
    {
        return $this->copyInstanceWithParameter('flag', null);
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
