<?php

declare(strict_types=1);

namespace App\Common\Database\Concerns;

use App\Common\Database\Scope\BindingNotFoundException;
use App\Common\Database\Scope\ScopeNotFoundException;
use Doctrine\DBAL\Query\QueryBuilder;
use Symfony\Component\String\UnicodeString;

/**
 * Allows for the model to DB connections.
 */
trait HasScopes
{
    /**
     * The list of the scope resolvers.
     *
     * @var Array<string, \Closure>
     */
    private static array $scopesResolvers = [];

    /**
     * Get the scope function by its name.
     *
     * @param string $name the name of the scope
     *
     * @throws ScopeNotFoundException if binding is not found
     */
    public function getScope(string $name/*, ?string $dimension = null*/): \Closure
    {
        $args = \func_get_args();
        if (2 === \func_num_args()) {
            @trigger_error('Passing a second paramter to this method is deprecated.', \E_USER_DEPRECATED);
        }
        $dimension = $args[1] ?? null;
        if (null === $resolver = $this->getScopeResolverFromName($this->createScopeName('scope', $dimension, $name))) {
            throw ScopeNotFoundException::create($this, $name);
        }

        return $resolver($this);
    }

    /**
     * Get the binding function by its name.
     *
     * @param string $name the name of the scope
     *
     * @throws BindingNotFoundException if binding is not found
     */
    public function getBinding(string $name/*, ?string $dimension = null*/): \Closure
    {
        $args = \func_get_args();
        if (2 === \func_num_args()) {
            @trigger_error('Passing a second paramter to this method is deprecated.', \E_USER_DEPRECATED);
        }
        $dimension = $args[1] ?? null;
        if (null === $resolver = $this->getScopeResolverFromName($this->createScopeName('bind', $dimension, $name))) {
            throw BindingNotFoundException::create($this, $name);
        }

        return $resolver($this);
    }

    /**
     * Make the name for scope query named parameter.
     *
     * @param string $originalName the name of the paramter
     * @param bool   $randomize    the flag that enables/disables parameter name randomization
     */
    protected function nameScopeParameter(string $originalName, bool $randomize = true): string
    {
        if ($randomize) {
            return \sprintf(':sp_%s_%s', (new UnicodeString($originalName))->camel()->toString(), \bin2hex(\random_bytes(12)));
        }

        return \sprintf(':sp_%s', (new UnicodeString($originalName))->camel()->toString());
    }

    /**
     * Adds binding constraints.
     */
    protected function addBindingConstraints(QueryBuilder $builder, ?string $cut, array $bindings)
    {
        if (empty($bindings)) {
            return;
        }

        foreach ($bindings as $binding) {
            if (null === $binding || empty($binding)) {
                continue;
            }

            try {
                // Get the binding by its name.
                $bindingCallable = $this->getBinding($this->createScopeName($cut, $binding));
            } catch (ScopeNotFoundException $e) {
                // Silently fail if binding was not found by its name.
                // The exception is silenced as attempt to prevent BC breaks in this place.
                continue;
            }

            $bindingCallable($builder);
        }
    }

    /**
     * Add scopes.
     */
    protected function addScopedConstraints(QueryBuilder $builder, ?string $cut, array $constraintsList)
    {
        if (empty($constraintsList)) {
            return;
        }

        foreach ($constraintsList as $scope => $constraints) {
            if (is_numeric($scope)) {
                // If the scope name is numeric, we must take the constraint as the name.
                if (!is_string($constraints)) {
                    // If scope name is not string, we must skip it.
                    continue;
                }

                $scope = $constraints;
                // Put TRUE to preserve the flow.
                $constraints = true;
            }

            if (null === $constraints) {
                continue;
            }

            // Get the scope by its name.
            $scopeCallable = $this->getScope($this->createScopeName($cut, $scope));
            // If the constrants are not closures (scalara values, objects etc)
            // then we will call scope with the cosntaints as first argument
            if (!$constraints instanceof \Closure) {
                $scopeCallable($builder, $constraints);

                continue;
            }

            // Else, we assume that the constrants returns the values that must be provided to the scope.
            $args = $constraints();
            if (!is_array($args) && !$args instanceof \Traversable) {
                $args = [$args];
            }
            $scopeCallable($builder, ...$args);
        }
    }

    /**
     * Returns scope from the name.
     */
    private function getScopeResolverFromName(string $name): ?\Closure
    {
        $modelName = \get_class($this);
        $scopeKey = "{$modelName}::{$name}";
        if (!isset(static::$scopesResolvers[$scopeKey])) {
            if (!\method_exists($this, $name)) {
                return null;
            }

            static::$scopesResolvers[$scopeKey] = fn ($model) => \Closure::fromCallable([$model, $name]);
        }

        return static::$scopesResolvers[$scopeKey] ?? null;
    }

    /**
     * Creates scope name.
     */
    private function createScopeName(?string ...$parts): string
    {
        return (string) (new UnicodeString(\implode('_', \array_filter($parts, fn ($p) => null !== $p))))->camel();
    }
}
