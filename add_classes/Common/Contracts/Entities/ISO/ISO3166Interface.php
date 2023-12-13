<?php

namespace App\Common\Contracts\Entities\ISO;

interface ISO3166Interface
{
    /**
     * Returns the Alpha-2 code.
     *
     * @return null|string
     */
    public function getAlpha2();

    /**
     * Returns the Alpha-3 code.
     *
     * @return null|string
     */
    public function getAlpha3();

    /**
     * Returns the numeric code.
     *
     * @return null|string
     */
    public function getNumeric();

    /**
     * Returns the list of subdivisions.
     *
     * @return ISO3166SubdivisionInterface[]
     */
    public function getSubdivisions();
}
