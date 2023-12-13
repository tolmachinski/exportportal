<?php

declare(strict_types=1);

namespace App\Users;

use App\Common\File\File;
use App\Users\PersonalName;

trait PersonTrait
{
    /**
     * The person's ID.
     *
     * @var null|int
     */
    private $id;

    /**
     * The person's name.
     *
     * @var null|PersonalName
     */
    private $name;

    /**
     * The person's photo.
     *
     * @var File
     */
    private $photo;

    /**
     * Checks if person ID exists.
     *
     * @return bool
     */
    public function hasId()
    {
        return null !== $this->id;
    }

    /**
     * Returns the ID of the person.
     *
     * @return null|int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns an instance with the specified ID.
     *
     * @param int   $name
     * @param mixed $id
     *
     * @return static
     */
    public function withId($id)
    {
        $new = clone $this;
        $new->id = !empty($id) ? (int) $id : null;

        return $new;
    }

    /**
     * Return an instance without ID.
     *
     * @return static
     */
    public function withoutId()
    {
        $new = clone $this;
        $new->id = null;

        return $new;
    }

    /**
     * Checks if person name exists.
     *
     * @return bool
     */
    public function hasName()
    {
        return null !== $this->name;
    }

    /**
     * Returns the name of the person.
     *
     * @return null|PersonalName
     */
    public function getName(): ?PersonalName
    {
        return $this->name;
    }

    /**
     * Returns an instance with the specified name.
     *
     * @param PersonalName $name
     *
     * @return static
     */
    public function withName(PersonalName $name)
    {
        $new = clone $this;
        $new->name = $name;

        return $new;
    }

    /**
     * Return an instance without name.
     *
     * @return static
     */
    public function withoutName()
    {
        $new = clone $this;
        $new->name = null;

        return $new;
    }

    /**
     * Checks if person photo exists.
     *
     * @return bool
     */
    public function hasPhoto()
    {
        return null !== $this->photo;
    }

    /**
     * Returns the photo of the person.
     *
     * @return null|File
     */
    public function getPhoto(): ?File
    {
        return $this->photo;
    }

    /**
     * Returns an instance with the specified photo.
     *
     * @param File $photo
     *
     * @return static
     */
    public function withPhoto(File $photo)
    {
        $new = clone $this;
        $new->photo = $photo;

        return $new;
    }

    /**
     * Return an instance without photo.
     *
     * @return static
     */
    public function withoutPhoto()
    {
        $new = clone $this;
        $new->photo = null;

        return $new;
    }
}
