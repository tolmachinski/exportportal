<?php

namespace App\Documents\Versioning;

use DateTimeImmutable;

trait VersionTrait
{
    /**
     * The name of the version.
     *
     * @var string
     */
    private $name;

    /**
     * The version comment.
     *
     * @var string
     */
    private $comment;

    /**
     * The content context.
     *
     * @var ContentContext
     */
    private $context;

    /**
     * The date when version was created.
     *
     * @var \DateTimeImmutable
     */
    private $creationDate;

    /**
     * Checks if cersion name exists.
     */
    public function hasName(): bool
    {
        return null !== $this->name;
    }

    /**
     * Returns the name of the version.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Returns an instance with the specified name.
     *
     * @return static
     */
    public function withName(string $name)
    {
        $new = clone $this;
        $new->name = !empty($name) ? (string) $name : null;

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
     * Checks if version comment exists.
     */
    public function hasComment(): bool
    {
        return null !== $this->comment;
    }

    /**
     * Returns the version comment.
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * Returns an instance with the specified comment.
     *
     * @return static
     */
    public function withComment(string $comment)
    {
        $new = clone $this;
        $new->comment = !empty($comment) ? (string) $comment : null;

        return $new;
    }

    /**
     * Return an instance without comment.
     *
     * @return static
     */
    public function withoutComment()
    {
        $new = clone $this;
        $new->comment = null;

        return $new;
    }

    /**
     * Checks if version context exists.
     */
    public function hasContext(): bool
    {
        return null !== $this->context && $this->context->count() > 0;
    }

    /**
     * Returns the context.
     */
    public function getContext(): ContentContext
    {
        if (null === $this->context) {
            $this->context = new ContentContext();
        }

        return $this->context;
    }

    /**
     * Returns an instance with the specified context.
     *
     * @return static
     */
    public function withContext(ContentContext $context)
    {
        $new = clone $this;
        $new->context = $context;

        return $new;
    }

    /**
     * Return an instance without context.
     *
     * @return static
     */
    public function withoutContext()
    {
        $new = clone $this;
        $new->context = new ContentContext();

        return $new;
    }

    /**
     * Checks if version creation date exists.
     */
    public function hasCreationDate(): bool
    {
        return null !== $this->creationDate;
    }

    /**
     * Returns the date when version was created.
     */
    public function getCreationDate(): ?DateTimeImmutable
    {
        return $this->creationDate;
    }

    /**
     * Returns an instance with the specified date of creation.
     *
     * @return static
     */
    public function withCreationDate(DateTimeImmutable $date)
    {
        $new = clone $this;
        $new->creationDate = $date;

        return $new;
    }

    /**
     * Return an instance without date of creation.
     *
     * @return static
     */
    public function withoutCreationDate()
    {
        $new = clone $this;
        $new->creationDate = null;

        return $new;
    }
}
