<?php

declare(strict_types=1);

namespace App\Users;

use DomainException;

final class PersonalName
{
    /**
     * The first name.
     *
     * @var null|string
     */
    private $firstName;

    /**
     * The last name.
     *
     * @var null|string
     */
    private $lastName;

    /**
     * The list of middle names.
     *
     * @var string[]
     */
    private $middleNames = array();

    /**
     * Creates the isntance of personal name.
     *
     * @param null|string $firstName
     * @param null|string $lastName
     * @param array       $middleNames
     */
    public function __construct(?string $firstName = null, ?string $lastName = null, array $middleNames = array())
    {
        $this->lastName = $lastName;
        $this->firstName = $firstName;
        foreach ($middleNames as $middleName) {
            if (!is_string($middleNames)) {
                throw new DomainException('The middle name expected to be string.');
            }

            $this->middleNames[] = $middleName;
        }
    }

    /**
     * Returns the string representation of the personal name.
     *
     * @return string
     */
    public function __toString()
    {
        $names = array_filter(array_merge(
            array($this->firstName),
            $this->middleNames,
            array($this->lastName)
        ));

        return trim(implode(' ', $names));
    }

    /**
     * Get the first name.
     *
     * @return null|string
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * Get the last name.
     *
     * @return null|string
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * Get the list of middle names.
     *
     * @return string[]
     */
    public function getMiddleNames(): array
    {
        return $this->middleNames;
    }
}
