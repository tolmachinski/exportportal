<?php

declare(strict_types=1);

namespace App\Common\Database\Concerns;

use App\Common\Database\AttributeCastInterface;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Types\Type;
use ExportPortal\Enum\EnumCase;

trait CastsAttributes
{
    /**
     * The attributes that should be cast.
     */
    protected array $casts = [];

    /**
     * The list of original casts.
     */
    private array $originalCasts = [];

    /**
     * The cached types.
     *
     * @var array<string,AttributeCastInterface|Type>
     */
    private array $cachedTypes = [];

    /**
     * The flag that determines if nested cast is disabled.
     */
    private bool $nestedCastDisabled = false;

    /**
     * The list of primitive cast types.
     */
    private static array $primitiveCastTypes = [];

    /**
     * Determine if cast is enabled.
     */
    public function isCastEnabled(): bool
    {
        return !empty($this->casts);
    }

    /**
     * Determine if nested cast (for relation) is disabled.
     */
    public function isNestedCastDisabled(): bool
    {
        return $this->nestedCastDisabled;
    }

    /**
     * Determine if attribute key has cast.
     */
    public function hasCast(string $key, ?array $types = null): bool
    {
        if (!array_key_exists($key, $this->getCasts())) {
            return false;
        }

        return null !== $types ?: true;
    }

    /**
     * Gets the attributes that should be cast.
     */
    public function getCasts(): array
    {
        return $this->casts;
    }

    /**
     * Merges the additional that should be cast.
     */
    public function mergeCasts(array $casts): self
    {
        $this->casts = array_merge($this->casts, $casts);

        return $this;
    }

    /**
     * Resets the list of casts.
     */
    public function resetCasts(): self
    {
        $this->casts = $this->originalCasts;

        return $this;
    }

    /**
     * Clears the list of casts.
     */
    public function clearCasts(): self
    {
        $this->casts = [];

        return $this;
    }

    /**
     * Runs the callable with provided casts.
     *
     * @return mixed
     */
    public function runWithCasts(callable $callback, array $casts)
    {
        $currentCasts = $this->getCasts();
        $this->mergeCasts($casts);

        try {
            return $callback();
        } finally {
            $this->clearCasts()->mergeCasts($currentCasts);
        }
    }

    /**
     * Runs the callable without mentioned casts.
     *
     * @return mixed
     */
    public function runWithoutCasts(callable $callback, array $castKeys)
    {
        $currentCasts = $this->getCasts();
        $this->clearCasts()->mergeCasts(\array_diff_key($currentCasts, \array_flip(\array_values($castKeys))));

        try {
            return $callback();
        } finally {
            $this->clearCasts()->mergeCasts($currentCasts);
        }
    }

    /**
     * Runs the callable without all available casts.
     *
     * @return mixed
     */
    public function runWithoutAllCasts(callable $callback, bool $nested = false)
    {
        $currenFlag = $this->nestedCastDisabled;
        $currentCasts = $this->getCasts();
        $this->clearCasts();
        $this->nestedCastDisabled = $nested;

        try {
            return $callback();
        } finally {
            $this->clearCasts()->mergeCasts($currentCasts);
            $this->nestedCastDisabled = $currenFlag;
        }
    }

    /**
     * Casts the set of attributes to the database values.
     */
    public function castAttributesToDatabase(array $attributes): array
    {
        $casted = $attributes;
        if ($this->isCastEnabled()) {
            foreach ($attributes as $key => $value) {
                $casted[$key] = $this->castAttributeToDatabaseValue($key, $value, $attributes);
            }
        }

        return $casted;
    }

    /**
     * Casts the set of attributes to the native values.
     */
    public function castAttributesToNative(?array $attributes): ?array
    {
        $casted = $attributes;
        if ($this->isCastEnabled() && is_array($attributes)) {
            foreach ($attributes as $key => $value) {
                $casted[$key] = $this->castAttributeToNativeValue($key, $value, $casted);
            }
        }

        return $casted;
    }

    /**
     * Casts one attribute to database values.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    protected function castAttributeToDatabaseValue(string $key, $value, array $attributes = [])
    {
        $platform = $this->getPlatform();

        if ($this->isTypeCast($key)) {
            return $this->getCachedCasterType($key)->convertToDatabaseValue($value, $platform);
        }

        if ($this->isCastableEnum($key)) {
            return $this->getEnumCastableAttributeValue($key, $value)->value;
        }

        if ($this->isCastableClass($key)) {
            // TODO: resolve issue - what to do if returned array???
            return $this->getCachedCasterClass($key)->set($platform, $this, $key, $value, $attributes);
        }

        return $value;
    }

    /**
     * Casts one attribute to native values.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    protected function castAttributeToNativeValue(string $key, $value, array $attributes = [])
    {
        $platform = $this->getPlatform();

        if ($this->isTypeCast($key)) {
            return $this->getCachedCasterType($key)->convertToPHPValue($value, $platform);
        }

        if ($this->isCastableEnum($key)) {
            return $this->getEnumCastableAttributeValue($key, $value);
        }

        if ($this->isCastableClass($key)) {
            return $this->getCachedCasterClass($key)->get($platform, $this, $key, $value, $attributes);
        }

        return $value;
    }

    /**
     * Determines if the cast belongs to the known DBAL types.
     */
    protected function isTypeCast(string $key): bool
    {
        if (!array_key_exists($key, $casts = $this->getCasts())) {
            return false;
        }

        $castType = $casts[$key];
        if (in_array($castType, static::$primitiveCastTypes)) {
            return true;
        }

        // If we failed to find cached types, then we will try to see if Doctrine has this type registered
        // This way all primitive cast types will be slowly added to the pool
        // and we dont't need to seek for them every time.
        // Plus, it works for ALL models as well.
        if (Type::hasType($castType) || $this->getPlatform()->hasDoctrineTypeMappingFor($castType)) {
            static::$primitiveCastTypes[] = $castType;

            return true;
        }

        return false;
    }

    /**
     * Determinest if cast is of a custom class.
     */
    protected function isCastableClass(string $key): bool
    {
        if (!array_key_exists($key, $casts = $this->getCasts())) {
            return false;
        }

        $castType = $casts[$key];
        $castType = $this->parseCastName($castType);
        if ($this->isTypeCast($castType)) {
            return false;
        }

        if (!class_exists($castType) || !is_subclass_of($castType, AttributeCastInterface::class)) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the given key is cast using an enum.
     */
    protected function isCastableEnum(string $key): bool
    {
        if (!array_key_exists($key, $casts = $this->getCasts())) {
            return false;
        }

        $castType = $casts[$key];
        if ($this->isTypeCast($castType)) {
            return false;
        }

        // Support enum polifyll
        // It must run BEFORE native function check
        if (\class_exists(EnumCase::class) && is_a($castType, EnumCase::class, true)) {
            return true;
        }
        // Support native PHP8.1 enums
        if (function_exists('enum_exists') && \enum_exists($castType)) {
            return true;
        }

        return false;
    }

    /**
     * Parses the caster class name.
     */
    protected function parseCastName(string $cast): string
    {
        return false === strpos($cast, ':') ? $cast : explode(':', $cast, 2)[0];
    }

    /**
     * Resolves the type instance.
     */
    protected function resolveCasterType(string $key): Type
    {
        $castType = $this->getCasts()[$key];
        if (is_object($castType)) {
            return $castType;
        }

        $platform = $this->getPlatform();

        try {
            $typeName = $platform->getDoctrineTypeMapping($castType);
        } catch (DBALException $exception) {
            $typeName = $castType;
        }

        return Type::getType($typeName);
    }

    /**
     * Resolves caster instance.
     */
    protected function resolveCasterClass(string $key): AttributeCastInterface
    {
        $castType = $this->getCasts()[$key];
        if (is_object($castType)) {
            return $castType;
        }

        $arguments = [];
        if (is_string($castType) && false !== strpos($castType, ':')) {
            $segments = explode(':', $castType, 2);
            $castType = $segments[0];
            $arguments = explode(',', $segments[1]);
        }

        return new $castType(...$arguments);
    }

    protected function getCachedCasterType(string $key): ?Type
    {
        if (!isset($this->cachedTypes[$key])) {
            $this->cachedTypes[$key] = $this->resolveCasterType($key);
        }

        if (!($this->cachedTypes[$key] instanceof Type)) {
            return null;
        }

        return $this->cachedTypes[$key];
    }

    /**
     * Returns the cached caster class.
     */
    protected function getCachedCasterClass(string $key): ?AttributeCastInterface
    {
        if (!isset($this->cachedTypes[$key])) {
            $this->cachedTypes[$key] = $this->resolveCasterClass($key);
        }

        if (!($this->cachedTypes[$key] instanceof AttributeCastInterface)) {
            return null;
        }

        return $this->cachedTypes[$key];
    }

    /**
     * Cast the given attribute to an enum.
     *
     * @param EnumCase|mixed $value
     *
     * @return EnumCase|mixed
     */
    protected function getEnumCastableAttributeValue(string $key, $value)
    {
        if (null === $value) {
            return null;
        }

        $castType = $this->getCasts()[$key];
        if ($value instanceof $castType) {
            return $value;
        }

        return $castType::from($value);
    }

    /**
     * Boots the casts concern.
     */
    private function bootCastsConcern(): void
    {
        $this->cacheOriginalCastsValues();
    }

    /**
     * Caches originla values of the static::$casts.
     */
    private function cacheOriginalCastsValues(): void
    {
        $this->originalCasts = $this->casts;
    }
}
