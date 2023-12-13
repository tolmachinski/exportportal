<?php

declare(strict_types=1);

namespace App\Bridge\Matrix\User;

/**
 * @author Anton Zencenco
 */
interface UserReferenceProviderInterface
{
    /**
     * Get user reference by ID.
     */
    public function getReferenceById(int $referenceId, bool $extended = true): ?array;

    /**
     * Get user reference by ID.
     */
    public function getReferenceByUserId(int $userId, bool $extended = true): ?array;

    /**
     * Get user reference by MXID (Matrix ID).
     */
    public function getReferenceByUserMxid(string $mixd, bool $extended = true): ?array;

    /**
     * Get user reference by MXID (Matrix ID).
     *
     * @param string[] $mixds
     */
    public function getReferencesByUserMxids(array $mixds, bool $extended = true): ?array;
}
