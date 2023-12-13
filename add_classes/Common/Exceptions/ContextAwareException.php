<?php

declare(strict_types=1);

namespace App\Common\Exceptions;

class ContextAwareException extends \RuntimeException
{
    /**
     * The context.
     */
    private array $context;

    /**
     * {@inheritdoc}
     */
    public function __construct(string $message, array $context = [], int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->context = $context;
    }

    /**
     * Get the context.
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Set the context.
     */
    public function setContext(array $context): self
    {
        $this->context = $context;

        return $this;
    }
}
