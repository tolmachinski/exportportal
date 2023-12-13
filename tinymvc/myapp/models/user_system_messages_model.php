<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * User_System_Messages model
 */
final class User_System_Messages_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "user_systmessages";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "USER_SYSTMESSAGES";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id_um";

    /**
     * @param int $userId
     * @return array
     */
    public function counterUserNotifications(int $userId): array
    {
        /** @var System_Messages_Model $systemMessagesModel */
        $systemMessagesModel = model(System_Messages_Model::class);

        $systemMessagesTable = $systemMessagesModel->getTable();
        $userSystemMessagesTable = $this->getTable();

        $queryBuilder = $this->createQueryBuilder();

        $queryBuilder
            ->select([
                "COUNT(IF(`{$userSystemMessagesTable}`.`status` = 'new', `{$userSystemMessagesTable}`.`status`, null)) AS count_new",
                "COUNT(IF(`{$userSystemMessagesTable}`.`mess_type` = 'warning' AND `{$userSystemMessagesTable}`.`status` = 'new', `{$userSystemMessagesTable}`.`mess_type`, null)) AS count_warning",
                "COUNT(*) AS count_all"
            ])
            ->from($userSystemMessagesTable)
            ->leftJoin(
                $userSystemMessagesTable,
                $systemMessagesTable,
                $systemMessagesTable,
                "`{$userSystemMessagesTable}`.`idmess` = `{$systemMessagesTable}`.`idmess`"
            )
            ->andWhere(
                $queryBuilder->expr()->eq(
                    "`{$userSystemMessagesTable}`.`idu`",
                    $queryBuilder->createNamedParameter($userId, ParameterType::INTEGER, $this->nameScopeParameter('userId'))
                )
            )
            ->andWhere(
                $queryBuilder->expr()->eq(
                    "`{$userSystemMessagesTable}`.`calendar_only`",
                    $queryBuilder->createNamedParameter(0, ParameterType::INTEGER, $this->nameScopeParameter('calendarOnly'))
                )
            )
            ->andWhere(
                $queryBuilder->expr()->in(
                    "`{$this->getTable()}`.`status`",
                    [
                        $queryBuilder->createNamedParameter('new', ParameterType::STRING, $this->nameScopeParameter("systemMessagesStatus_0")),
                        $queryBuilder->createNamedParameter('seen', ParameterType::STRING, $this->nameScopeParameter("systemMessagesStatus_1")),
                    ]
                )
            );

        return array_shift($queryBuilder->execute()->fetchAllAssociative());
    }

    /**
     * Scope query by user ID
     *
     * @param QueryBuilder $builder
     * @param int $userId
     *
     * @return void
     */
    protected function scopeUserId(QueryBuilder $builder, int $userId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`idu`",
                $builder->createNamedParameter($userId, ParameterType::INTEGER, $this->nameScopeParameter('userId'))
            )
        );
    }

    /**
     * Scope query by calendar only status
     *
     * @param QueryBuilder $builder
     * @param int $calendarOnly
     *
     * @return void
     */
    protected function scopeCalendarOnly(QueryBuilder $builder, int $calendarOnly): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`calendar_only`",
                $builder->createNamedParameter($calendarOnly, ParameterType::INTEGER, $this->nameScopeParameter('calendarOnly'))
            )
        );
    }

    /**
     * Scope query by user system messages statuses
     *
     * @param QueryBuilder $builder
     * @param array $statuses
     *
     * @return void
     */
    protected function scopeSystemMessagesStatuses(QueryBuilder $builder, array $statuses): void
    {
        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->getTable()}`.`status`",
                array_map(
                    fn ($i, $status) => $builder->createNamedParameter((string) $status, ParameterType::STRING, $this->nameScopeParameter("systemMessagesStatus_{$i}")),
                    array_keys($statuses),
                    $statuses
                )
            )
        );
    }

    /**
     * Scope for join with usersStatitcs
     */
    protected function bindSystemMessages(QueryBuilder $builder): void
    {
        /** @var System_Messages_Model $systemMessagesModel */
        $systemMessagesModel = model(System_Messages_Model::class);

        $systemMessagesTable = $systemMessagesModel->getTable();
        $userSystemMessagesTable = $this->getTable();

        $builder
            ->leftJoin(
                $userSystemMessagesTable,
                $systemMessagesTable,
                null,
                "`{$userSystemMessagesTable}`.`idmess` = `{$systemMessagesTable}`.`idmess`"
            );
    }
}

/* End of file user_system_messages_model.php */
/* Location: /tinymvc/myapp/models/user_system_messages_model.php */
