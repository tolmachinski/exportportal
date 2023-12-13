<?php

namespace App\Plugins\EPDocs\Rest\Resources;

use App\Plugins\EPDocs\NotFoundException;
use App\Plugins\EPDocs\Rest\Objects\TemporaryFile as TemporaryFileObject;
use App\Plugins\EPDocs\Rest\RestResource;

final class TemporaryFile extends RestResource
{
    /**
     * Check if file exists.
     *
     * @param mixed $fileId
     */
    public function hasFile($fileId): bool
    {
        try {
            return 200 === (int) $this->sendRequest('HEAD', "/api/temporary-files/{$fileId}")->getStatusCode();
        } catch (NotFoundException $exception) {
            return false;
        }
    }

    /**
     * Returns the temporary file by its ID.
     *
     * @param string $temporaryFileId
     *
     * @return \App\Plugins\EPDocs\Rest\Objects\TemporaryFile
     */
    public function getFile($temporaryFileId)
    {
        return TemporaryFileObject::fromArray(
            $this->getParsedResponseBody(
                $this->sendRequest('GET', "/api/temporary-files/{$temporaryFileId}")
            )
        );
    }

    /**
     * Returns the list of temporary files their id.
     *
     * @param mixed[] $temporaryFileIds
     *
     * @return \App\Plugins\EPDocs\Rest\Objects\TemporaryFile[]
     */
    public function findFiles($temporaryFileIds)
    {
        return \array_map(
            fn (array $file) => TemporaryFileObject::fromArray($file),
            $this->getParsedResponseBody(
                $this->sendRequest('GET', '/api/temporary-files', [
                    'query' => [
                        'id' => \array_map(fn ($id) => (string) $id, $temporaryFileIds),
                    ],
                ])
            )
        );
    }
}
