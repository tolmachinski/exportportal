<?php

namespace App\Plugins\EPDocs\Rest\Resources;

use App\Plugins\EPDocs\NotFoundException;
use App\Plugins\EPDocs\Rest\Objects\File as FileObject;
use App\Plugins\EPDocs\Rest\RestResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Psr\Http\Message\StreamInterface;
use Ramsey\Uuid\UuidInterface;

final class File extends RestResource
{
    /**
     * Check if file exists.
     *
     * @param mixed $fileId
     *
     * @return bool
     */
    public function hasFile($fileId)
    {
        try {
            return 200 === (int) $this->sendRequest('HEAD', "/api/files/{$fileId}")->getStatusCode();
        } catch (NotFoundException $exception) {
            return false;
        }
    }

    /**
     * Returns the file information.
     *
     * @param mixed $fileId
     *
     * @return FileObject
     */
    public function getFile($fileId)
    {
        return FileObject::fromArray(
            $this->getParsedResponseBody(
                $this->sendRequest('GET', "/api/files/{$fileId}")
            )
        );
    }

    /**
     * Get files for user.
     *
     * @param string|UuidInterface $userId
     */
    public function getUserFiles($userId): Collection
    {
        return new ArrayCollection(
            array_map(
                function (array $resource) { return FileObject::fromArray($resource); },
                $this->getParsedResponseBody(
                    $this->sendRequest('GET', "/api/users/{$userId}/files")
                ) ?? []
            )
        );
    }

    /**
     * Copyies the file.
     *
     * @param string|UuidInterface $userId
     * @param mixed                $fileId
     */
    public function copyFile($userId, $fileId): FileObject
    {
        return FileObject::fromArray(
            $this->getParsedResponseBody(
                $this->sendRequest('POST', '/api/files/copy', ['json' => [
                    'user' => "/api/users/{$userId}",
                    'file' => "/api/files/{$fileId}",
                ]])
            )
        );
    }

    /**
     * Creates the file.
     *
     * @param mixed $userId
     * @param mixed $temporaryFileId
     *
     * @return \App\Plugins\EPDocs\Rest\Objects\File
     */
    public function createFile($userId, $temporaryFileId)
    {
        return FileObject::fromArray(
            $this->getParsedResponseBody(
                $this->sendRequest('POST', '/api/files', ['json' => [
                    'user'          => "/api/users/{$userId}",
                    'temporaryFile' => "/api/temporary-files/{$temporaryFileId}",
                ]])
            )
        );
    }

    /**
     * Creates the file from resource.
     *
     * @param mixed       $userId
     * @param mixed       $resource
     * @param string      $name
     * @param null|string $type
     *
     * @return \App\Plugins\EPDocs\Rest\Objects\File
     */
    public function createFileFromResource($userId, $resource, $name, $type = null)
    {
        if (!is_resource($resource) && !$resource instanceof StreamInterface) {
            throw new \InvalidArgumentException(sprintf("Expected argument 2 in %s() to be of type 'resource' or stream, got %s", __METHOD__, gettype($resource)));
        }

        return FileObject::fromArray(
            $this->getParsedResponseBody(
                $this->sendRequest('POST', "/api/users/{$userId}/files", ['multipart' => [
                    [
                        'name'     => 'file',
                        'contents' => $resource,
                        'filename' => $name,
                    ],
                    [
                        'name'     => 'type',
                        'contents' => null !== $type ? $type : 'file',
                    ],
                ]])
            )
        );
    }

    /**
     * Deletes file by ID.
     *
     * @param mixed $fileId
     *
     * @return bool
     */
    public function deleteFile($fileId)
    {
        return 204 === (int) $this->sendRequest('DELETE', "/api/files/{$fileId}")->getStatusCode();
    }
}
