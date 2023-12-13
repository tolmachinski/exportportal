<?php

namespace App\Common\Contracts\Entities;

use App\Common\Contracts\Entities\ISO\ISO3166Interface;
use SplFileInfo;

interface CountryInterface extends ImmutableEntityInterface, IdAwareEntityInterface, NameAwareEntityInterface
{
    /**
     * Get the country ISO 3166 values.
     *
     * @return null|ISO3166Interface
     */
    public function getIso3166();

    /**
     * Returns the instance with ISO3166 value.
     *
     * @param null|ISO3166Interface $iso3166 the country ISO 3166 values
     *
     * @return static
     */
    public function withIso3166($iso3166);

    /**
     * Returns the instance without ISO3166 value.
     *
     * @return static
     */
    public function withoutIso3166();

    /**
     * Returns the flag value.
     *
     * @return null|SplFileInfo
     */
    public function getFlag();

    /**
     * Returns the instance with provided flag value.
     *
     * @param SplFileInfo $flag
     *
     * @return static
     */
    public function withFlag(SplFileInfo $flag);

    /**
     * Returns the instance without flag value.
     *
     * @return static
     */
    public function withoutFlag();
}
