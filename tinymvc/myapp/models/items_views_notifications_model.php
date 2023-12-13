<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Types\Types;

/**
 * Items_View_Notifications model
 */
final class Items_Views_Notifications_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "items_views_notifications";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "ITEMS_VIEWS_NOTIFICATIONS";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'                => Types::INTEGER,
        'user_id'           => Types::INTEGER,
        'views_count'       => Types::INTEGER,
        'notification_date' => Types::DATETIME_IMMUTABLE,
    ];

    /**
     * Prepare data for cron: notify_sellers_about_items_views
     *
     * @param $usersIds
     * @return array
     */
    public function getLastUsersNotifications(array $usersIds): array
    {
        if (empty($usersIds)) {
            return [];
        }

        $queryBuilder = $this->createQueryBuilder();
        $subQueryBuilder = $this->createQueryBuilder();

        $subQueryBuilder
            ->select(
                "`{$this->table}`.*",
                "ROW_NUMBER() over (PARTITION BY `{$this->table}`.`user_id` ORDER BY `{$this->table}`.`id` DESC) AS rating"
            )
            ->from($this->table)
            ->andWhere(
                $queryBuilder->expr()->in(
                    "`{$this->table}`.`user_id`",
                    array_map(
                        fn ($i, $userId) => $queryBuilder->createNamedParameter((int) $userId, ParameterType::INTEGER, $this->nameScopeParameter("userId_{$i}")),
                        array_keys($usersIds),
                        $usersIds
                    )
                )
            )
        ;

        $queryBuilder
            ->select('`last_notifications`.*')
            ->from("({$subQueryBuilder->getSQL()}) AS last_notifications")
            ->andWhere(
                $queryBuilder->expr()->eq(
                    '`last_notifications`.`rating`',
                    $queryBuilder->createNamedParameter(1, ParameterType::INTEGER, $this->nameScopeParameter('notificationRank')),
                )
            )
        ;

        return $queryBuilder->execute()->fetchAllAssociative();
    }
}

/* End of file items_view_notifications_model.php */
/* Location: /tinymvc/myapp/models/items_view_notifications_model.php */
