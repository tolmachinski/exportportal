<?php

declare(strict_types=1);

namespace App\Services;

use App\Common\Exceptions\NotFoundException;
use Items_Model;
use User_Model;
use Elasticsearch_Items_Model;

final class SearchProductsFastService implements SampleServiceInterface
{
    /**
    * The items repository.
    *
    * @var User_Model
    */
    private $usersRepository;

    /**
     * The items repository.
     *
     * @var Items_Model
     */
    private $itemsRepository;

    /**
     * Creates instance of service.
     *
     * @param Sample_Orders_Model $sampleOrders
     * @param User_Model          $users
     * @param Items_Model         $items
     */
    public function __construct(
        int $productsPerPage = null
    ) {
        $this->productsPerPage = $productsPerPage;
        $this->usersRepository = $users ?? model(User_Model::class);
        $this->itemsRepository = $items ?? model(Items_Model::class);
        $this->elasticItemsRepository = $items ?? model(Elasticsearch_Items_Model::class);
    }

    /**
     * Ensures that the user exists.
     *
     * @param int $itemId
     *
     * @deprecated
     */
    private function ensureUserExists(?int $userId): void
    {
        if (null === $userId) {
            return;
        }

        if (!$this->usersRepository->exist_user($userId)) {
            throw new NotFoundException("The user with ID '{$userId}' is not found.", static::USER_NOT_FOUND_ERROR);
        }
    }

    /**
     * Finds seller's active products by provided search text.
     */
    public function findElasticProducts(int $userId, ?array $params): array
    {
        //region Security
        $this->ensureUserExists($userId);
        //endregion Security

        $itemsRez = [];
        $condition = array_merge(
            [
                'per_p' 	=> $this->productsPerPage,
                'page' 		=> 1,
                'seller'    => $userId,
            ],
            $params
        );
        $this->elasticItemsRepository->get_items($condition);
        $items = $this->elasticItemsRepository->items;
        $total = $this->elasticItemsRepository->itemsCount;

        if ($total > 0) {
            foreach ($items as $item) {
                $item['price'] = priceToUsdMoney($item['price'] ?? 0);
                $item['final_price'] = priceToUsdMoney($item['final_price'] ?? 0);

                $itemsRez[] = $item;
            }
        }

        return [
            'data'  => $itemsRez,
            'total' => $total,
        ];
    }

}
