<?php

declare(strict_types=1);

namespace App\Services;

use App\Common\Contracts\BuyerIndustries\CollectTypes;
use App\Common\DependencyInjection\ServiceLocator\ModelLocator;
use Buyer_Item_Categories_Stats_Model;

final class BuyerIndustryOfInterestService
{
    /**
     * The model.
     *
     * @var Buyer_Item_Categories_Stats_Model
     */
    private $buyerIndustriesStats;

    /**
     * Creates the instance of the service.
     */
    public function __construct(ModelLocator $modelLocator)
    {
        $this->buyerIndustriesStats = $modelLocator->get(\Buyer_Item_Categories_Stats_Model::class);
    }

    /**
     * Adds new statistic about buyer industry of interest.
     *
     * @param int          $idCategory  - the id of the category
     * @param CollectTypes $collectType - the type of collection (item, item search, seller etc.)
     */
    public function addIndustryOfInterest(int $idCategory, int $idUser, string $idNotLogged, CollectTypes $collectType): void
    {
        $this->buyerIndustriesStats->insertOne([
            'id_not_logged'  => $idNotLogged,
            'id_category'    => $idCategory,
            'type'           => $collectType,
            'idu'            => $idUser ?: null,
        ]);
    }

    /**
     * Update all records that have idNotLogged but do not have id user
     * Sets the id user for these records.
     *
     * @param int    $idUser      - the id to set
     * @param string $idNotLogged - the id of the not logged to search by
     */
    public function correlateIdUser(int $idUser, string $idNotLogged): void
    {
        if (null === $idNotLogged
        || null === $this->buyerIndustriesStats->findOneBy([
            'conditions' => [
                'idNotLogged' => $idNotLogged,
                'iduIsNull',
            ],
        ])) {
            return;
        }

        $this->buyerIndustriesStats->updateMany(['idu' => $idUser], [
            'conditions' => [
                'id_not_logged' => $idNotLogged,
                'iduIsNull',
            ],
        ]);
    }
}
