<?php

declare(strict_types=1);

namespace App\Common\Database\Concerns;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;

/**
 * Allows for the model to append timestapm attributes.
 *
 * The project-specific implementation of the Laravel HasTimestamps
 * ({@link https://github.com/laravel/framework/blob/master/src/Illuminate/Database/Eloquent/Concerns/HasTimestamps.php})
 * trait with local flavor and changes.
 */
trait AppendsTimestamps
{
    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = false;

    /**
     * Determines if model uses timestamps.
     */
    public function usesTimestamps(): bool
    {
        return $this->timestamps;
    }

    /**
     * Get a fresh timestamp for the model.
     */
    public function freshTimestamp(): DateTimeInterface
    {
        return new DateTimeImmutable();
    }

    /**
     * Get the name of the "created at" column.
     */
    protected function getCreatedAtColumn(): ?string
    {
        return constant('static::CREATED_AT') ?? null;
    }

    /**
     * Get the name of the "updated at" column.
     */
    protected function getUpdatedAtColumn(): ?string
    {
        return constant('static::UPDATED_AT') ?? null;
    }

    /**
     * Updates timestamps in the set of attributes. Allows to skip dates if they already exist.
     */
    protected function updateTimestamps(array $attributes): array
    {
        if (!$this->usesTimestamps()) {
            return $attributes;
        }

        $updatedAtColumn = $this->getUpdatedAtColumn();
        if (null !== $updatedAtColumn && !isset($attributes[$updatedAtColumn])) {
            $attributes[$updatedAtColumn] = $this->freshTimestamp();
        }

        $createdAtColumn = $this->getCreatedAtColumn();
        if (null !== $createdAtColumn && !isset($attributes[$createdAtColumn])) {
            $attributes[$createdAtColumn] = $this->freshTimestamp();
        }

        return $attributes;
    }

    /**
     * Boots the casts concern.
     */
    private function bootTimestampsConcern(): void
    {
        if ($this->usesTimestamps()) {
            $additionalCasts = [];
            foreach (array_filter([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn()]) as $key) {
                $additionalCasts[$key] = Types::DATETIME_IMMUTABLE;
            }

            $this->mergeCasts($additionalCasts);
        }
    }
}
