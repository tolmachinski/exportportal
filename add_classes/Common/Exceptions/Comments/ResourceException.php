<?php

namespace App\Common\Exceptions\Comments;

/**
 * Thrown when an error occurred when working with comment record.
 */
class ResourceException extends CommentException
{
    /**
     * Thrown when specified type is not defined.
     */
    public static function typeNotDefined(): self
    {
        return new static('The specified type is not defined.');
    }

    /**
     * Thrown when model option for specified type is not defined.
     *
     * @return self
     */
    public static function typeModelOptionIsNotDefined(string $option, string $type): void
    {
        throw new static(
            \sprintf(
                'The %s for supported type "%s" is not found',
                $option,
                $type
            )
        );
    }

    /**
     * Thrown when calling not allowed model method.
     */
    public static function modelMethodCallNotAllowed(string ...$allowed): self
    {
        return new static(
            sprintf(
                'This model cannot be used to read resources directly. Pleas use one of the methods that idicates type: "%s"',
                ...$allowed
            )
        );
    }

    /**
     * Thrown when calling model method without type.
     */
    public static function modelMethodCallRequiresType(): self
    {
        throw new static(
            'This model cannot be used to read resources directly. Pleas specify type of the record.'
        );
    }

    /**
     * Thrown when calling non-read-only methods.
     */
    public static function modelReadOnly(): self
    {
        throw new static(
            'This model cannot be used to read resources directly. Pleas specify type of the record.'
        );
    }
}
