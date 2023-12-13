<?php

namespace App\Documents\Versioning;

use DateTimeImmutable;

interface VersionInterface
{
    const DATE_FORMAT = 'Y-m-d\\TH:i:s.uP';

    /**
     * Checks if version name exists.
     */
    public function hasName(): bool;

    /**
     * Returns the name of the version.
     */
    public function getName(): ?string;

    /**
     * Returns an instance with the specified name.
     *
     * @return static
     */
    public function withName(string $name);

    /**
     * Return an instance without name.
     *
     * @return static
     */
    public function withoutName();

    /**
     * Checks if version comment exists.
     */
    public function hasComment(): bool;

    /**
     * Returns the version comment.
     */
    public function getComment(): ?string;

    /**
     * Returns an instance with the specified comment.
     *
     * @return static
     */
    public function withComment(string $comment);

    /**
     * Return an instance without comment.
     *
     * @return static
     */
    public function withoutComment();

    /**
     * Checks if version creation date exists.
     */
    public function hasCreationDate(): bool;

    /**
     * Returns the date when version was created.
     */
    public function getCreationDate(): ?DateTimeImmutable;

    /**
     * Returns an instance with the specified date of creation.
     *
     * @return static
     */
    public function withCreationDate(DateTimeImmutable $date);

    /**
     * Return an instance without date of creation.
     *
     * @return static
     */
    public function withoutCreationDate();

    /**
     * Checks if version context exists.
     */
    public function hasContext(): bool;

    /**
     * Returns the context.
     */
    public function getContext(): ContentContext;

    /**
     * Returns an instance with the specified context.
     *
     * @return static
     */
    public function withContext(ContentContext $context);

    /**
     * Return an instance without context.
     *
     * @return static
     */
    public function withoutContext();
}
