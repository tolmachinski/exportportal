<?php

namespace App\Logger\Handlers;

class CallableHandler extends AbstractHandler
{
    /**
     * Stored function that handles the log record.
     *
     * @var callable
     */
    private $callableHandle;

    public function __construct(callable $callableHandle)
    {
        $this->callableHandle = $callableHandle;
    }

    /**
     * {@inheritdoc}
     *
     * Is a wrapper for callbale
     */
    public function handle(array $logRecord)
    {
        if ($this->isClosed()) {
            return true;
        }

        return call_user_func($this->callableHandle, $logRecord);
    }
}
