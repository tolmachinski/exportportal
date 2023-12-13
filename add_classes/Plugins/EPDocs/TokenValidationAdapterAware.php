<?php

namespace App\Plugins\EPDocs;

interface TokenValidationAdapterAware
{
    /**
     * Returns the validator.
     *
     * @return \App\Plugins\EPDocs\TokenValidationAdapter
     */
    public function getValidator();
}
