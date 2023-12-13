<?php

namespace App\Common\Exceptions;

use Throwable;

/**
 * Thrown when failed to write file.
 */
class FileWriteException extends FileException
{
    /**
     * {@inheritdoc}
     */
    public function __construct(string $path, $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Failed to write the file "%s"', $path),
            $code,
            $previous
        );
    }
}
