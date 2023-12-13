<?php

namespace App\Logger\Handlers;

use App\Logger\Contracts\HandlerInterface;

abstract class AbstractHandler implements HandlerInterface
{
    /**
     * Flag which indicates that handler is opened.
     *
     * @var bool
     */
    private $isOpened = true;

    /**
     * Handles a record.
     *
     * All records may be passed to this method, and the handler should discard those that it does not want to handle.
     * The return value of this function controls the bubbling process of the handler stack.
     *
     * @param array $logRecord The log record to handle
     *
     * @return bool
     */
    abstract public function handle(array $logRecord);

    /**
     * Handles a set of records at once.
     *
     * @param array[] $records The log records to handle (an array of record arrays)
     */
    public function handleBatch(array $logRecords)
    {
        if (empty($logRecords) || $this->isClosed()) {
            return;
        }

        foreach ($logRecords as $logRecord) {
            $this->handle($logRecord);
        }
    }

    /**
     * Open the handler.
     */
    public function open()
    {
        $this->isOpened = true;
    }

    /**
     * Closes the handler.
     */
    public function close()
    {
        $this->isOpened = false;
    }

    /**
     * Returns TRUE if handler is closed.
     *
     * @return bool
     */
    public function isClosed()
    {
        return false === $this->isOpened;
    }
}
