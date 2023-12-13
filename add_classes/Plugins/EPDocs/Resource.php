<?php

namespace App\Plugins\EPDocs;

interface Resource
{
    /**
     * Returns the API client.
     *
     * @return \App\Plugins\EPDocs\Client
     */
    public function getApiClient();
}
