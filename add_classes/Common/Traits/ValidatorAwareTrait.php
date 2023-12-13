<?php

namespace App\Common\Traits;

/**
 * @deprecated
 */
trait ValidatorAwareTrait
{
    /**
     * Returns the validator.
     *
     * @return \TinyMVC_Library_validator
     */
    protected function getValidator()
    {
        return library('validator');
    }
}
