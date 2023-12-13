<?php

namespace App\Entities\ISO;

use App\Common\Contracts\Entities\ISO\ISO3166Interface;
use DomainException;

final class ISO3166 implements ISO3166Interface
{
    /**
     * The Alpha-2 code.
     *
     * @var null|string
     */
    private $alpha2;

    /**
     * The Alpha-3 code.
     *
     * @var null|string
     */
    private $alpha3;

    /**
     * The numeric code.
     *
     * @var null|string
     */
    private $numeric;

    /**
     * The list of subdivisions.
     *
     * @var array<ISO3166Subdivision>
     */
    private $subdivisions;

    public function __construct(
        $alpha2,
        $alpha3,
        $numeric = null,
        array $subdivisions = array()
    ) {
        $this->alpha2 = $alpha2;
        $this->alpha3 = $alpha3;
        $this->numeric = $numeric;

        foreach ($subdivisions as $subdivision) {
            if (!$subdivision instanceof ISO3166Subdivision) {
                throw new DomainException(sprintf('The subdivision must be instance of "%s"', ISO3166Subdivision::class));
            }

            $this->subdivisions[] = $subdivision;
        }
    }

    /**
     * Returns the Alpha-2 code.
     *
     * @return null|string
     */
    public function getAlpha2()
    {
        return $this->alpha2;
    }

    /**
     * Returns the Alpha-3 code.
     *
     * @return null|string
     */
    public function getAlpha3()
    {
        return $this->alpha3;
    }

    /**
     * Returns the numeric code.
     *
     * @return null|string
     */
    public function getNumeric()
    {
        return $this->numeric;
    }

    /**
     * Returns the list of subdivisions.
     *
     * @return array<ISO3166Subdivision>
     */
    public function getSubdivisions()
    {
        return $this->subdivisions;
    }
}
