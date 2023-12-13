<?php

namespace App\Plugins\EPDocs\Rest\Resources;

use App\Plugins\EPDocs\Rest\Objects\User as UserObject;
use App\Plugins\EPDocs\Rest\RestResource;

final class User extends RestResource
{
    /**
     * Returns the user by its ID.
     *
     * @param mixed $userId
     *
     * @return \App\Plugins\EPDocs\Rest\Objects\User
     */
    public function getUser($userId)
    {
        return UserObject::fromArray(
            $this->getParsedResponseBody(
                $this->sendRequest('GET', "/api/users/{$userId}")
            )
        );
    }

    /**
     * Check if user exists using provided context.
     *
     * @return bool
     */
    public function hasUser(array $context)
    {
        return !empty($this->getParsedResponseBody(
            $this->sendRequest('GET', '/api/users', ['query' => [
                'signature' => $context,
            ]])
        ));
    }

    /**
     * Returns the list of users.
     *
     * @return null|\App\Plugins\EPDocs\Rest\Objects\User
     */
    public function getUsers()
    {
        $collection = $this->getParsedResponseBody(
            $this->sendRequest('GET', '/api/users')
        );

        return empty($collection)
            ? []
            : array_map(
                function ($user) {
                    return UserObject::fromArray($user);
                },
                $collection
            );
    }

    /**
     * Finds user by signature context.
     *
     * @return null|\App\Plugins\EPDocs\Rest\Objects\User
     */
    public function findUser(array $context)
    {
        $collection = $this->getParsedResponseBody(
            $this->sendRequest('GET', '/api/users', ['query' => [
                'signature' => $context,
            ]])
        );

        return isset($collection[0]) ? UserObject::fromArray($collection[0]) : null;
    }

    /**
     * Finds user if exists, if not then creates it.
     *
     * @return \App\Plugins\EPDocs\Rest\Objects\User
     */
    public function findUserIfNotCreate(array $context)
    {
        $user = $this->findUser($context);
        if (null === $user) {
            return $this->createUser($context);
        }

        return $user;
    }

    /**
     * Creates the user from provided context.
     *
     * @return \App\Plugins\EPDocs\Rest\Objects\User
     */
    public function createUser(array $context)
    {
        return UserObject::fromArray(
            $this->getParsedResponseBody(
                $this->sendRequest('POST', '/api/users', ['json' => [
                    'signature' => $context,
                ]])
            )
        );
    }

    /**
     * Deletes user by ID.
     *
     * @param mixed $userId
     *
     * @return bool
     */
    public function deleteUser($userId)
    {
        return 204 === (int) $this->sendRequest('DELETE', "/api/users/{$userId}")->getStatusCode();
    }
}
