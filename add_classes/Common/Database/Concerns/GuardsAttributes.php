<?php

declare(strict_types=1);

namespace App\Common\Database\Concerns;

/**
 * Allows for the model to guard attributes.
 *
 * One must use GuardsAttributes::$guarded to indicate guarded attributes.
 *
 * The project-specific implementation of the Laravel GuardAttributes
 * ({@link https://github.com/laravel/framework/blob/master/src/Illuminate/Database/Eloquent/Concerns/GuardsAttributes.php})
 * trait with local flavor and changes.
 */
trait GuardsAttributes
{
    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [];

    /**
     * Indicates if all mass assignment is enabled.
     */
    protected bool $unguarded = false;

    /**
     * The list of original guarded attributes.
     */
    private array $originalGuarded = [];

    /**
     * Determines if model is unguarded.
     */
    public function isUnguarded(): bool
    {
        return $this->unguarded;
    }

    /**
     * Determine if the model is totally guarded.
     */
    public function isTotallyGuarded(): bool
    {
        return false === $this->unguarded && $this->getGuarded() == ['*'];
    }

    /**
     * Determines if attribute is guarded.
     */
    public function isGuarded(string $key): bool
    {
        // If whole model is unguarded, then every attribute is unguarded as well.
        if ($this->unguarded) {
            return false;
        }

        return in_array($key, $this->getGuarded()) || $this->getGuarded() == ['*'];
    }

    /**
     * Get the list of attributes that aren't mass assignable.
     */
    public function getGuarded(): array
    {
        return $this->guarded;
    }

    /**
     * Merge new list of guarded attributes with existing ones.
     */
    public function mergeGuarded(array $guarded): self
    {
        $this->guarded = array_merge($this->guarded, $guarded);

        return $this;
    }

    /**
     * Resets the list of guarded attributes.
     */
    public function resetGuarded(): self
    {
        $this->guarded = $this->originalGuarded;

        return $this;
    }

    /**
     * Makes the attributes not mass assignable.
     */
    public function guard(array $guarded): self
    {
        $this->guarded = $guarded;

        return $this;
    }

    /**
     * Makes the model unguarded.
     */
    public function unguard(): self
    {
        $this->unguarded = true;

        return $this;
    }

    /**
     * Makes the model unguarded.
     */
    public function reguard(): self
    {
        $this->unguarded = false;

        return $this;
    }

    /**
     * Runs the callable while being uguarded.
     *
     * @return mixed
     */
    public function runUnguarded(callable $callback)
    {
        if ($this->unguarded) {
            return $callback();
        }

        $this->unguard();

        try {
            return $callback();
        } finally {
            $this->reguard();
        }
    }

    /**
     * Filters the guarded attributes.
     */
    protected function filterGuardedAttributes(array $attributes): array
    {
        if ($this->isTotallyGuarded()) {
            return $attributes;
        }

        if (!$this->unguarded && count($this->getGuarded()) > 0) {
            return array_intersect_key($attributes, array_flip($this->getGuarded()));
        }

        return [];
    }

    /**
     * Filters the guarded attributes.
     */
    protected function filterUnGuardedAttributes(array $attributes): array
    {
        if ($this->isTotallyGuarded()) {
            return [];
        }

        if (!$this->unguarded && count($this->getGuarded()) > 0) {
            return array_diff_key($attributes, array_flip($this->getGuarded()));
        }

        return $attributes;
    }

    /**
     * Boots the guard concern.
     */
    private function bootGuardConcern(): void
    {
        $this->cacheOriginalGuardedValues();
    }

    /**
     * Caches originla values of the static::$guarded.
     */
    private function cacheOriginalGuardedValues(): void
    {
        $this->originalGuarded = $this->guarded;
    }
}
