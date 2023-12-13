<?php

declare(strict_types=1);

namespace App\Messenger\Message\Command;

/**
 * Command that deactivates the user's account on the matrix server by matrix ID.
 *
 * @author Anton Zencenco
 */
final class DeactivateUnknownMatrixUser
{
    /**
     * The matrix user ID value.
     */
    private string $matrixUserId;

    /**
     * @param string $matrixUserId the matrix user ID value
     */
    public function __construct(string $matrixUserId)
    {
        $this->matrixUserId = $matrixUserId;
    }

    /**
     * Get the matrix user ID value.
     */
    public function getMatrixUserId(): string
    {
        return $this->matrixUserId;
    }

    /**
     * Set the matrix user ID value.
     */
    public function setMatrixUserId(string $matrixUserId): self
    {
        $this->matrixUserId = $matrixUserId;

        return $this;
    }
}
