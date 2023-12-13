<?php

declare(strict_types=1);

namespace App\Common\Database\Concerns;

/**
 * Allows for the model to clear nullable attributes.
 */
trait ClearsNullables
{
    /**
     * The attributes that aren't mass assignable.
     */
    protected array $nullable = [];

    /**
     * The list of original nullable attributes.
     */
    private array $originalNullable = [];

    /**
     * Determines if attribute is nullable.
     */
    public function isNullable(string $key): bool
    {
        return in_array($key, $this->getNullable()) || $this->isTotallyNullable();
    }

    /**
     * Determines if attribute is tottaly nullable.
     */
    public function isTotallyNullable(): bool
    {
        return $this->getNullable() == ['*'];
    }

    /**
     * Get the list of attributes that can have NULL.
     */
    public function getNullable(): array
    {
        return $this->nullable;
    }

    /**
     * Merge new list of nullable attributes with existing ones.
     */
    public function mergeNullable(array $nullable): self
    {
        $this->nullable = array_merge($this->nullable, $nullable);

        return $this;
    }

    /**
     * Resets the list of nullable attributes.
     */
    public function resetNullable(): self
    {
        $this->nullable = $this->originalNullable;

        return $this;
    }

    /**
     * Filters the nullable attributes.
     */
    protected function filterNullableAttributes(array $attributes): array
    {
        if ($this->isTotallyNullable()) {
            return $attributes;
        }

        if (count($this->getNullable()) > 0) {
            return array_intersect_key($attributes, array_flip($this->getNullable()));
        }

        return [];
    }

    /**
     * Clears the nullable attributes.
     */
    protected function clearNullAttributes(array $attributes): array
    {
        if ($this->isTotallyNullable() || empty($nullables = array_flip($this->getNullable()))) {
            return $attributes;
        }

        $cleared = [];
        foreach ($attributes as $key => $value) {
            if (null === $value && !isset($nullables[$key])) {
                continue;
            }

            $cleared[$key] = $value;
        }

        return $cleared;
    }

    /**
     * Boots the nullable concern.
     */
    private function bootNullableConcern(): void
    {
        $this->cacheOriginalNullableValues();
    }

    /**
     * Caches originla values of the static::$nullable.
     */
    private function cacheOriginalNullableValues(): void
    {
        $this->originalNullable = $this->nullable;
    }
}
