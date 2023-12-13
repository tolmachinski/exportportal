<?php

namespace App\Plugins\EPDocs\Rest\Resources;

use App\Plugins\EPDocs\Rest\RestResource;

final class FilePermissions extends RestResource
{
    const PERMISSION_NONE = 0;

    const PERMISSION_READ = 1;

    const PERMISSION_WRITE = 2;

    const PERMISSION_EXECUTE = 4;

    /**
     * Creates the permissions to the file for given user.
     *
     * @param mixed $userId
     * @param mixed $fileId
     * @param int   $permissions
     */
    public function createPermissions($fileId, $userId, $permissions)
    {
        return $this->getParsedResponseBody(
            $this->sendRequest('POST', '/api/files/permissions', ['json' => [
                'user'        => "/api/users/{$userId}",
                'file'        => "/api/files/{$fileId}",
                'permissions' => (int) $permissions,
            ]])
        );
    }

    /**
     * Checks if user has indicated permissions to the file.
     *
     * @param midex $userId
     * @param midex $fileId
     * @param int   $permissions
     *
     * @return bool
     */
    public function hasPermissions($fileId, $userId, $permissions)
    {
        return !empty($this->getParsedResponseBody(
            $this->sendRequest('GET', '/api/files/permissions', ['query' => [
                'user'        => "/api/users/{$userId}",
                'file'        => "/api/files/{$fileId}",
                'permissions' => (int) $permissions,
            ]])
        ));
    }

    /**
     * Creates the permissions to the file for given user if they doesn't exists.
     *
     * @param midex $userId
     * @param midex $fileId
     * @param int   $permissions
     *
     * @return bool
     */
    public function createPermissionsIfNotExists($fileId, $userId, $permissions)
    {
        if (!$this->hasPermissions($fileId, $userId, $permissions)) {
            $this->createPermissions($fileId, $userId, $permissions);
        }
    }
}
