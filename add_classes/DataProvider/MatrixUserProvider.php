<?php

declare(strict_types=1);

namespace App\DataProvider;

use App\Bridge\Matrix\MatrixConnector;
use Symfony\Contracts\Cache\CacheInterface;
use TinyMVC_Library_Session as LegacySessionHandler;

final class MatrixUserProvider
{
    /**
     * The instance of the cache adapter.
     */
    private CacheInterface $cacheAdapter;

    /**
     * The legacy session handler.
     */
    private LegacySessionHandler $sessionHandler;

    /**
     * The instance of matrix connector.
     */
    private MatrixConnector $matrixConnector;

    /**
     * @param MatrixConnector      $matrixConnector the instance of matrix connector
     * @param LegacySessionHandler $sessionHandler  the legacy session handler
     * @param CacheInterface       $cacheAdapter    the instance of the cache adapter
     */
    public function __construct(
        MatrixConnector $matrixConnector,
        LegacySessionHandler $sessionHandler,
        CacheInterface $cacheAdapter
    ) {
        $this->cacheAdapter = $cacheAdapter;
        $this->sessionHandler = $sessionHandler;
        $this->matrixConnector = $matrixConnector;
    }

    public function userCredentials(int $userId): ?array
    {
        $hasKeys = $this->sessionHandler->get('matrixKeys', false);
        $creadentials = $this->sessionHandler->get('matrix', null);
        if (null === $creadentials || !$hasKeys) {
            if (null === $reference = $this->readUserReference($userId)) {
                return null;
            }

            $this->sessionHandler->set('matrixKeys', $reference['has_initialized_keys']);
            $this->sessionHandler->set('matrix', $creadentials = [
                'profileId' => $reference['profile_room_id'],
                'matrixId'  => $reference['mxid'],
                'username'  => $reference['username'],
                'password'  => $reference['password'],
                'hasKeys'   => $reference['has_initialized_keys'],
            ]);
        }

        return $creadentials;
    }

    /**
     * Reads the user data from storage.
     */
    private function readUserReference(int $userId): ?array
    {
        return $this->cacheAdapter->get(
            "matrix_user.{$userId}",
            fn () => $this->matrixConnector->getUserReferenceProvider()->getReferenceByUserId($userId, false)
        );
    }
}
