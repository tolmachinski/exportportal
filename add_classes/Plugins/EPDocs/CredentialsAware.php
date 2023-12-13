<?php

namespace App\Plugins\EPDocs;

interface CredentialsAware
{
    /**
     * Returns the credentials.
     *
     * @return \App\Plugins\EPDocs\Credentials
     */
    public function getCredentials();
}
