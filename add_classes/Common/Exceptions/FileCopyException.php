<?php

namespace App\Common\Exceptions;

class FileCopyException extends FileException
{
    /**
     * The list of the copying errors.
     *
     * @var array
     */
    private $errors = array();

    /**
     * Get the list of the copying errors.
     *
     * @return array
     */
    public function getCopyErrors()
    {
        return $this->errors;
    }

    /**
     * Set the list of the copying errors.
     *
     * @param array $errors The list of the copying errors
     *
     * @return self
     */
    public function setCopyErrors(array $errors)
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * Make the instance of exception.
     *
     * @param array      $errors
     * @param string     $message
     * @param int        $code
     * @param \Throwable $prev
     *
     * @return static
     */
    public static function make(
        $errors = array(),
        $message = 'Failed to copy the files',
        $code = 0,
        $prev = null
    ) {
        return tap(new static($message, $code, $prev), function (FileCopyException $exception) use ($errors) {
            $exception->setCopyErrors($errors);
        });
    }
}
