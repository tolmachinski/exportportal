<?php

namespace App\Entities\ISO;

final class ISO3166Subdivision
{
    /**
     * The name of subdivision.
     *
     * @var string
     */
    private $name;

    /**
     * The local name of subdivision.
     *
     * @var null|string
     */
    private $localName;

    /**
     * The subdivision code.
     *
     * @var string
     */
    private $code;

    /**
     * Type of subdivision.
     *
     * @var string
     */
    private $type;

    /**
     * The subdivision parent.
     *
     * @var null|string
     */
    private $parent;

    public function __construct(
        $name,
        $code,
        $parent,
        $type = null,
        $localName = null
    ) {
        $this->name = $name;
        $this->code = $code;
        $this->type = $type;
        $this->parent = $parent;
        $this->localName = $localName;
    }

    /**
     * Returns the name of subdivision.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the local name of subdivision.
     *
     * @return null|string
     */
    public function getLocalName()
    {
        return $this->localName;
    }

    /**
     * Returns the subdivision code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Returns type of subdivision.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns the subdivision parent.
     *
     * @return null|string
     */
    public function getParent()
    {
        return $this->parent;
    }
}
