<?php

namespace App\Plugins\EPDocs;

interface AuthenticationClientAware
{
    /**
     * Returns the autheticator.
     *
     * @return \App\Plugins\EPDocs\AuthenticationClient
     */
    public function getAuthenticationClient();
}
