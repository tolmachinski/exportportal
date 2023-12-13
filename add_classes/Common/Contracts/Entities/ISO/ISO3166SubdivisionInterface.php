<?php

namespace App\Common\Contracts\Entities\ISO;

interface ISO3166SubdivisionInterface
{
    /**
     * Returns the name of subdivision.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the local name of subdivision.
     *
     * @return null|string
     */
    public function getLocalName();

    /**
     * Returns the subdivision code.
     *
     * @return string
     */
    public function getCode();

    /**
     * Returns type of subdivision.
     *
     * @return string
     */
    public function getType();

    /**
     * Returns the subdivision parent.
     *
     * @return null|string
     */
    public function getParent();
}
