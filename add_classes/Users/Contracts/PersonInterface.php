<?php

declare(strict_types=1);

namespace App\Users\Contracts;

use App\Common\File\File;
use App\Users\PersonalName;

interface PersonInterface extends GroupMemberInterface
{
    /**
     * Checks if person ID exists.
     *
     * @return bool
     */
    public function hasId();

    /**
     * Returns the ID of the person.
     *
     * @return null|int
     */
    public function getId();

    /**
     * Returns an instance with the specified ID.
     *
     * @param int   $name
     * @param mixed $id
     *
     * @return static
     */
    public function withId($id);

    /**
     * Return an instance without ID.
     *
     * @return static
     */
    public function withoutId();

    /**
     * Checks if person name exists.
     *
     * @return bool
     */
    public function hasName();

    /**
     * Returns the name of the person.
     *
     * @return null|PersonalName
     */
    public function getName(): ?PersonalName;

    /**
     * Returns an instance with the specified name.
     *
     * @param PersonalName $name
     *
     * @return static
     */
    public function withName(PersonalName $name);

    /**
     * Return an instance without name.
     *
     * @return static
     */
    public function withoutName();

    /**
     * Checks if person photo exists.
     *
     * @return bool
     */
    public function hasPhoto();

    /**
     * Returns the photo of the person.
     *
     * @return null|File
     */
    public function getPhoto(): ?File;

    /**
     * Returns an instance with the specified photo.
     *
     * @param File $photo
     *
     * @return static
     */
    public function withPhoto(File $photo);

    /**
     * Return an instance without photo.
     *
     * @return static
     */
    public function withoutPhoto();
}
