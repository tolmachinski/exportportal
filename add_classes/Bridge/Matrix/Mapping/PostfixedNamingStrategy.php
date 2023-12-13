<?php

declare(strict_types=1);

namespace App\Bridge\Matrix\Mapping;

/**
 * @deprecated Use UserPostfixedNamingStrategy instead
 */
class PostfixedNamingStrategy extends UserPostfixedNamingStrategy implements NamingStrategyInterface
{
    /**
     * The decorated naming strategy.
     *
     * @var NamingStrategyInterface
     */
    protected UserNamingStrategyInterface $namingStrategy;

    /**
     * {@inheritDoc}
     */
    public function __construct(NamingStrategyInterface $namingStrategy, string $postfix)
    {
        parent::__construct($namingStrategy, $postfix);

        $this->namingStrategy = $namingStrategy;
    }

    /**
     * {@inheritDoc}
     */
    public function spaceName(string $name): string
    {
        return "{$this->namingStrategy->spaceName($name)}.{$this->postfix}";
    }

    /**
     * {@inheritDoc}
     */
    public function spaceAlias(string $name): string
    {
        return $this->addPrefixToTheName($this->namingStrategy->spaceAlias($name));
    }
}
