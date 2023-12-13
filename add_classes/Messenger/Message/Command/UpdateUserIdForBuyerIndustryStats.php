<?php

declare(strict_types=1);

namespace App\Messenger\Message\Command;

/**
 * Base command for updating the id of the user on login
 *
 * @author Bendiucov Tatiana
 */
final class UpdateUserIdForBuyerIndustryStats
{
    /**
     * The id of the user
     */
    private int $idUser;

    /**
     * The id from _ep_client_id cookie
     */
    private string $idNotLogged;

    /**
     * @param int $idUser - the id of the user
     * @param string $idNotLogged - the id from _ep_client_id cookie
     */
    public function __construct(int $idUser, string $idNotLogged)
    {
        $this->idUser = $idUser;
        $this->idNotLogged = $idNotLogged;
    }

    /**
     * Get the user ID value.
     */
    public function getIdUser(): int
    {
        return $this->idUser;
    }

    /**
     * Set the user ID value.
     *
     * @return $this
     */
    public function setIdUser(int $idUser): self
    {
        $this->idUser = $idUser;

        return $this;
    }

    /**
     * Get the user ID not logged value.
     */
    public function getIdNotLogged(): string
    {
        return $this->idNotLogged;
    }

    /**
     * Set the user ID not logged value.
     *
     * @return $this
     */
    public function setIdNotLogged(string $idNotLogged): self
    {
        $this->idNotLogged = $idNotLogged;

        return $this;
    }
}
