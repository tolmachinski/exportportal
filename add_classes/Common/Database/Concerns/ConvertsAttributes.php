<?php

declare(strict_types=1);

namespace App\Common\Database\Concerns;

use App\Common\Database\Types\Types as CustomTypes;
use Doctrine\DBAL\Types\Type;

/**
 * Allows the model to convert attributes.
 */
trait ConvertsAttributes
{
    /**
     * Converts datetime attribute to the databse value.
     *
     * @param \DateTimeInterface|int|string $datetime
     */
    protected function convertDatetimeAttributeToDatabaseValue($datetime, ?string $format = null): ?string
    {
        if (null === $datetime) {
            return $datetime;
        }

        $type = Type::getType(CustomTypes::DATETIME_LEGACY);
        if (null !== $format) {
            return $type->convertToPHPValue($datetime, $this->getPlatform())->format($format);
        }

        return $type->convertToDatabaseValue($datetime, $this->getPlatform());
    }

    /**
     * Transform the set of attributes into platform-conpatible array of values.
     */
    protected function attributesToArray(array $attributes): array
    {
        // First we will append dates to the list if they are suppoorted
        $attributes = $this->updateTimestamps($attributes);

        // Next step is to cast the attributes values into the DB-compatible ones
        // and clear all NULL values that are not protected.
        return $this->clearNullAttributes(
            $this->castAttributesToDatabase($attributes)
        );
    }

    /**
     * Performs pre-fill operation type (insert, update) attributes processing.
     */
    protected function preFillAttributesProcessing(array $attributes): array
    {
        // Here we need only unguarded attributes, so everything else
        // will be dropped with impunity.
        return $this->attributesToArray(
            $this->filterUnGuardedAttributes(
                $attributes
            )
        );
    }

    /**
     * Performs pre-fill operation type (insert, update) attributes processing in unguarded mode.
     */
    protected function forcePreFillAttributesProcessing(array $attributes): array
    {
        // Wrap-up call into unprotected mode
        return $this->runUnguarded(function () use ($attributes) {
            return $this->preFillAttributesProcessing(
                $attributes
            );
        });
    }

    /**
     * Restores native values in the raw attributes list.
     */
    protected function restoreAttributesList(array $list): array
    {
        $casted = $list;
        if ($this->isCastEnabled()) {
            $casted = array_map(
                fn (array $attributes): array => $this->castAttributesToNative($attributes),
                $list
            );
        }

        return $casted;
    }
}
