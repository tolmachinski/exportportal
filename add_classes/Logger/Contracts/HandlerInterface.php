<?php

namespace App\Logger\Contracts;

interface HandlerInterface
{
    /**
     * Handles a record.
     *
     * All records may be passed to this method, and the handler should discard
     * those that it does not want to handle.
     *
     * The return value of this function controls the bubbling process of the handler stack.
     * Unless the bubbling is interrupted (by returning true), the Logger class will keep on
     * calling further handlers in the stack with a given log record.
     *
     * @param array $logRecord The log record to handle
     *
     * @return bool
     */
    public function handle(array $logRecord);

    /**
     * Handles a set of records at once.
     *
     * @param array[] $records The log records to handle (an array of record arrays)
     */
    public function handleBatch(array $logRecords);

    /**
     * Open the handler.
     */
    public function open();

    /**
     * Closes the handler.
     */
    public function close();

    /**
     * Returns TRUE if handler is closed.
     *
     * @return bool
     */
    public function isClosed();
}
