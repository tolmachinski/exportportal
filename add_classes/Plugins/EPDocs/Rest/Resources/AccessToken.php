<?php

namespace App\Plugins\EPDocs\Rest\Resources;

use App\Plugins\EPDocs\Rest\Objects\AccessToken as AccessTokenObject;
use App\Plugins\EPDocs\Rest\RestResource;

final class AccessToken extends RestResource
{
    /**
     * Creates the access token for the file.
     *
     * @param mixed $fileId
     * @param int   $ttl
     *
     * @return \App\Plugins\EPDocs\Rest\Objects\AccessToken
     */
    public function createToken($fileId, $ttl = -1)
    {
        return AccessTokenObject::fromArray(
            $this->getParsedResponseBody(
                $this->sendRequest('POST', '/api/access-tokens', ['json' => [
                    'file' => "/api/files/{$fileId}",
                    'ttl'  => $ttl,
                ]])
            )
        );
    }
}
