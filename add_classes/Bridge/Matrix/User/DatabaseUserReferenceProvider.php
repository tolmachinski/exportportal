<?php

declare(strict_types=1);

namespace App\Bridge\Matrix\User;

use App\Bridge\Matrix\Database\RepositoryAwareInterface as DatabaseRepositoryAwareInterface;
use App\Common\Database\Model;

/**
 * @author Anton Zencenco
 */
final class DatabaseUserReferenceProvider implements UserReferenceProviderInterface, DatabaseRepositoryAwareInterface
{
    /**
     * The user repository.
     */
    private Model $repository;

    /**
     * The internal sync cycle.
     */
    private ?string $cycle;

    /**
     * @param Model $repository The user repository
     */
    public function __construct(Model $repository, ?string $cycle = null)
    {
        $this->cycle = $cycle;
        $this->repository = $repository;
    }

    /**
     * Get the database repository.
     */
    public function getRepository(): Model
    {
        return $this->repository;
    }

    /**
     * Get user reference by ID.
     */
    public function getReferenceById(int $referenceId, bool $extended = true): ?array
    {
        return $this->repository->findOne($referenceId, [
            'with' => $extended ? ['user'] : [],
        ]);
    }

    /**
     * Get user reference by ID.
     */
    public function getReferenceByUserId(int $userId, bool $extended = true): ?array
    {
        return $this->repository->findOneBy([
            'with'       => $extended ? ['user'] : [],
            'conditions' => [
                'user'    => $userId,
                'version' => $this->cycle,
            ],
        ]);
    }

    /**
     * Get user reference by MXID (Matrix ID).
     */
    public function getReferenceByUserMxid(string $mixd, bool $extended = true): ?array
    {
        return $this->repository->findOneBy([
            'with'       => $extended ? ['user'] : [],
            'conditions' => [
                'mxid'    => $mixd,
                'version' => $this->cycle,
            ],
        ]);
    }

    /**
     * Get user reference by MXID (Matrix ID).
     *
     * @param string[] $mixds
     */
    public function getReferencesByUserMxids(array $mixds, bool $extended = true): ?array
    {
        if (empty($mixds)) {
            return [];
        }

        return $this->repository->findAllBy([
            'with'       => $extended ? ['user'] : [],
            'conditions' => [
                'mxids'   => $mixds,
                'version' => $this->cycle,
            ],
        ]);
    }
}
