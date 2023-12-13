<?php

namespace App\Common\Exceptions;

/**
 * Thrown when a file was not found.
 */
class FileNotFoundException extends FileException
{
    /**
     * {@inheritdoc}
     */
    public function __construct(string $path)
    {
        parent::__construct(
            sprintf('The file "%s" does not exist', $path)
        );
    }
}
