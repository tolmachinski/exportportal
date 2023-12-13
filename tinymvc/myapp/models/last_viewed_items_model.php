<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Last_Viewed_Items model.
 */
final class Last_Viewed_Items_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'last_viewed_items';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'LAST_VIEWED_ITEMS';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'date_updated'  => Types::DATETIME_IMMUTABLE,
        'times_viewed'  => Types::INTEGER,
        'date_added'    => Types::DATETIME_IMMUTABLE,
        'id_item'       => Types::INTEGER,
        'id'            => Types::INTEGER,
    ];

    /**
     * Method for increment times_viewed value for one last viewed item.
     *
     * @param string $notLoggedId
     * @param integer $itemId
     *
     * @return void
     */
    public function incrementLastViewedItemCounter(string $idNotLogged, int $itemId): bool
    {
        if (!empty($lastViewedItem = $this->findOneBy([
            'conditions' => [
                'notLoggedId'   => $idNotLogged,
                'itemId'        => $itemId,
            ],
        ]))) {
            $this->updateOne(
                $lastViewedItem['id'],
                [
                    'times_viewed' => $lastViewedItem['times_viewed'] + 1,
                    'date_updated' => new \DateTimeImmutable(),
                ]
            );
        } else {
            $this->insertOne([
                'id_user'          => id_session() ?: null,
                'id_not_logged'    => $idNotLogged,
                'id_item'          => $itemId,
                'analytics_cookie' => cookies()->getCookieParam('ANALITICS_CT_SUID'),
            ]);
        }

        return true;
    }

    /**
     * Return true or false if record exists for today
     *
     * @param integer $idItem
     * @param string $notLoggedId
     *
     * @return void
     */
    public function existsViewedToday(int $idItem, string $notLoggedId)
    {
        return (bool) $this->countBy([
            'conditions' => [
                'item_id'      => $idItem,
                'date_updated' => new DateTimeImmutable(),
                'notLoggedId'  => $notLoggedId,
            ],
        ]);
    }

    /**
     * Scope a query by the item id.
     *
     * @param QueryBuilder $builder
     * @param integer $itemId
     *
     * @return void
     */
    protected function scopeItemId(QueryBuilder $builder, int $itemId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.`id_item`",
                $builder->createNamedParameter($itemId, ParameterType::INTEGER, $this->nameScopeParameter('itemId'))
            )
        );
    }

    /**
     * Scope query for specific date updated.
     *
     * @param QueryBuilder $builder
     * @param DateTimeImmutable $updatedDate
     *
     * @return void
     */
    protected function scopeDateUpdated(QueryBuilder $builder, DateTimeImmutable $updatedDate): void
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updatedDate, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "DATE({$this->getTable()}.date_updated)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('updatedDate'))
            )
        );
    }

    /**
     * Scope a query by the not logged id.
     *
     * @param QueryBuilder $builder
     * @param DateTimeImmutable $notLoggedId
     *
     * @return void
     */
    protected function scopeNotLoggedId(QueryBuilder $builder, string $notLoggedId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.`id_not_logged`",
                $builder->createNamedParameter($notLoggedId, ParameterType::STRING, $this->nameScopeParameter('notLoggedId'))
            )
        );
    }
}

// End of file last_viewed_items_model.php
// Location: /tinymvc/myapp/models/last_viewed_items_model.php
