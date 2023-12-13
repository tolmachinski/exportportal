<?php

declare(strict_types=1);

namespace App\Messenger\Message\Command;

use App\Common\Contracts\BuyerIndustries\CollectTypes;

/**
 * Base command for saving the new industry of interest.
 *
 * @author Bendiucov Tatiana
 */
final class SaveBuyerIndustryOfInterest
{
    /**
     * The category ID.
     */
    private int $idCategory;

    /**
     * The user ID.
     */
    private int $idUser;

    /**
     * The user not logged id
     */
    private string $idNotLogged;

    /**
     * The leave reason.
     */
    private CollectTypes $type;

    /**
     * @param int          $idCategory  - the id of the category of interest
     * @param CollectTypes $collectType - the type of page to collect category from
     */
    public function __construct(int $idCategory, int $idUser, string $idNotLogged, CollectTypes $collectType)
    {
        $this->idCategory = $idCategory;
        $this->idUser = $idUser;
        $this->idNotLogged = $idNotLogged;
        $this->type = $collectType;
    }

    /**
     * Get the user ID value.
     */
    public function getIdCategory(): int
    {
        return $this->idCategory;
    }

    /**
     * Set the user ID value.
     *
     * @return $this
     */
    public function setIdCategory(int $idCategory): self
    {
        $this->idCategory = $idCategory;

        return $this;
    }

    /**
     * Get the user ID
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
     * Get the not logged ID
     */
    public function getIdNotLogged(): string
    {
        return $this->idNotLogged;
    }

    /**
     * Set the not logged ID value.
     *
     * @return $this
     */
    public function setIdNotLogged(string $idNotLogged): self
    {
        $this->idNotLogged = $idNotLogged;

        return $this;
    }

    /**
     * Get the CollectType.
     */
    public function getCollectType(): CollectTypes
    {
        return $this->type;
    }

    /**
     * Set the CollectType.
     *
     * @return $this
     */
    public function setCollectType(?string $collectType): self
    {
        $this->type = $collectType;

        return $this;
    }
}
