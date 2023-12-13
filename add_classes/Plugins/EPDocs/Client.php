<?php

namespace App\Plugins\EPDocs;

interface Client
{
    /**
     * Returns the new API resource.
     *
     * @param string $resourceName
     *
     * @return \App\Plugins\EPDocs\Resource
     */
    public function getResource($resourceName);
}
