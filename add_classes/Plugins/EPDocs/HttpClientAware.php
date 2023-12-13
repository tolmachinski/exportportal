<?php

namespace App\Plugins\EPDocs;

interface HttpClientAware
{
    /**
     * Returns the HTTP client.
     *
     * @return \GuzzleHttp\ClientInterface
     */
    public function getHttpClient();
}
