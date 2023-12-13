<?php

declare(strict_types=1);

namespace App\DataProvider;

use App\Common\Contracts\ConditionsOperator;
use App\Common\Contracts\Droplist\ItemStatus;
use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use DateTimeImmutable;
use Items_Droplist_Model;
use Symfony\Component\HttpFoundation\InputBag;

/**
 * Data provider used for Droplist items
 */
final class DroplistItemsDataProvider
{
    private Items_Droplist_Model $droplistModel;

    /**
     * Construct class.
     */
    public function __construct(Model $droplistModel)
    {
        $this->droplistModel = $droplistModel;
    }

    /**
     * Get list with items for datatable.
     */
    public function getDatatableListItems(InputBag $request, int $userId, bool $sellerCompany = false, bool $product = false): ?array
    {
        return $this->droplistModel->findAllBy([
            'columns'   => [
                "{$this->droplistModel->qualifyColumn('id')} AS `id`",
                "{$this->droplistModel->getTable()}.*"
            ],
            'scopes'    => array_merge(
                $this->getScopesConditions($request),
                [
                    'userId'        => $userId,
                    'statusNotIn'   => [
                        ItemStatus::BLOCKED(),
                    ],
                ]
            ),
            'with'      => $this->getWithConditions($sellerCompany, $product),
            'order'     => array_column(
                \dtOrdering(
                    $request->all(),
                    [
                        'dt_droplist_price'     => $this->droplistModel->qualifyColumn('`droplist_price`'),
                        'dt_current_price'      => $this->droplistModel->qualifyColumn('`item_price`'),
                        'dt_added_date'         => $this->droplistModel->qualifyColumn('`created_at`'),
                        'dt_price_change_date'  => $this->droplistModel->qualifyColumn('`price_changed_at`'),
                    ],
                ),
                'direction',
                'column'
            ),
            'limit'     => $request->getInt('iDisplayLength'),
            'skip'      => $request->getInt('iDisplayStart'),
        ]);
    }

    /**
     * Get count items for datatable.
     */
    public function getDatatableListItemsCount(InputBag $request, int $userId, bool $sellerCompany = false): int
    {
        return $this->droplistModel->countAllBy([
            'scopes'    => array_merge($this->getScopesConditions($request), ['user_id' => $userId]),
            'with'      => $this->getWithConditions($sellerCompany),
            'order'     => array_column(
                \dtOrdering(
                    $request->all(),
                    [
                        'dt_droplist_price'     => $this->droplistModel->qualifyColumn('`droplist_price`'),
                        'dt_current_price'      => $this->droplistModel->qualifyColumn('`item_price`'),
                        'dt_added_date'         => $this->droplistModel->qualifyColumn('`created_at`'),
                        'dt_price_change_date'  => $this->droplistModel->qualifyColumn('`price_changed_at`'),
                    ],
                ),
                'direction',
                'column'
            ),
        ]);
    }

    /**
     * Get list for notifications event.
     */
    public function getItemForNotifications(int $id): ?array
    {
        return $this->droplistModel->findOneBy([
            'columns'   => [
                '`id`',
                '`user_id`',
                '`item_id`',
                '`item_title`',
                '`item_image`',
                '`item_price`',
                '`droplist_price`',
                '`notification_type`',
            ],
            'scopes'    => [
                'id'    => $id,
            ],
            'with'      => [
                'user',
            ],
        ]);
    }

    /**
     * Get items categories tree.
     */
    public function getItemsCategoriesTree(): array
    {
        $itemsList = $this->droplistModel->findAllBy([
            'columns'   => [
                '`item_id`',
            ],
            'scopes'    => [
                'userId'        => (int) session()->id,
                'statusNotIn'   => [
                    ItemStatus::BLOCKED(),
                ],
            ],
        ]);

        if (empty($itemsList)) {
            return [];
        }

        $categoriesTree = [];

        $categories = arrayByKey(
            $this->droplistModel->getItemsCategoriesTree($itemsList),
            'category_id'
        );

        foreach ($categories as $categoryId => &$category) {
            if (!$category['parent']) {
                $categoriesTree[$categoryId] = &$category;
            } else {
                $categories[$category['parent']]['subcats'][$categoryId] = &$category;
            }
        }

        return $categoriesTree;
    }

    /**
     * Get with conditions.
     */
    private function getWithConditions(bool $sellerCompany = false, bool $product = false): array
    {
        $with = [];

        if ($sellerCompany) {
            $with['sellerCompany'] = function (RelationInterface $relation) {
                /** @var \Countries_Model $countriesModel */
                $countriesModel = $relation->getRelated()->getRelation('country')->getRelated();

                $relation->getQuery()->select(
                    "`{$relation->getRelated()->getPrimaryKey()}`",
                    "`{$relation->getRelated()->getTable()}`.`name_company`",
                    "`{$relation->getRelated()->getTable()}`.`id_country`",
                    "`{$relation->getRelated()->getTable()}`.`logo_company`",
                    $countriesModel->qualifyColumn('country'),
                );

                $relation->getQuery()->leftJoin(
                    $relation->getRelated()->getTable(),
                    $countriesModel->getTable(),
                    $countriesModel->getTable(),
                    "{$relation->getRelated()->qualifyColumn('id_country')} = {$countriesModel->qualifyColumn($countriesModel->getPrimaryKey())}"
                );
            };
        }

        if ($product) {
            $with['product'] = function (RelationInterface $relation) {
                $relation->getQuery()->select(
                    "`{$relation->getRelated()->getPrimaryKey()}`",
                    "`{$relation->getRelated()->getTable()}`.`discount`",
                );
            };
        }

        return $with;
    }

    /**
     * Generate scopes from request.
     */
    private function getScopesConditions(InputBag $request): array
    {
        return \dtConditions(
            $request->all(),
            [
                ['as' => 'search', 'key' => 'keywords', 'type' => 'cut_str:200'],
                ['as' => 'category', 'key' => 'category', 'type' => 'int'],
                [
                    'as'   => 'priceChangedFrom',
                    'key'  => 'price_changed_at',
                    'type' => fn ($v) => validateDate($v, 'm/d/Y') ? DateTimeImmutable::createFromFormat('m/d/Y', $v) : null,
                ],
                [
                    'as'   => 'priceChangedTo',
                    'key'  => 'price_changed_to',
                    'type' => fn ($v) => validateDate($v, 'm/d/Y') ? DateTimeImmutable::createFromFormat('m/d/Y', $v) : null,
                ],
                [
                    'as' => 'statusIn',
                    'key' => 'availability',
                    'type' => fn (string $status) => $status === (string) ItemStatus::ACTIVE() ? [ItemStatus::ACTIVE()] : [
                        ItemStatus::BLOCKED(), ItemStatus::OUT_OF_STOCK(), ItemStatus::ON_MODERATION(), ItemStatus::DRAFT(), ItemStatus::INVISIBLE()
                    ]
                ],
                ['as' => 'priceFluctuation', 'key' => 'price_fluctuation', 'type' => fn ($type) => ConditionsOperator::tryFrom($type)],
            ]
        );
    }
}
