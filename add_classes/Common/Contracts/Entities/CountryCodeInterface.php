<?php

namespace App\Common\Contracts\Entities;

interface CountryCodeInterface extends ImmutableEntityInterface, IdAwareEntityInterface, NameAwareEntityInterface
{
    /**
     * Returns the country value.
     *
     * @return null|CountryInterface
     */
    public function getCountry();

    /**
     * Returns the instance with provided country value.
     *
     * @param CountryInterface $country
     *
     * @return static
     */
    public function withCountry(CountryInterface $country);

    /**
     * Returns the instance without country value.
     *
     * @return static
     */
    public function withoutCountry();
}
