<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\ParameterType;

/**
 * B2b_Followers model.
 */
final class B2b_Followers_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'b2b_followers';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'B2B_FOLLOWERS';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_follower';

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_follower'                  => Types::INTEGER,
        'id_request'                   => Types::INTEGER,
        'id_user'                      => Types::INTEGER,
        'notice_follower'              => Types::TEXT,
        'date_follow'                  => Types::DATETIME_IMMUTABLE,
        'moderated'                    => Types::BOOLEAN,
    ];

    /**
     * Scope a query to filter by user ID.
     */
    protected function scopeUserId(QueryBuilder $builder, int $userId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_user'),
                $builder->createNamedParameter($userId, ParameterType::INTEGER, $this->nameScopeParameter('userId'))
            )
        );
    }

    /**
     * Scope a query to filter by request ID.
     */
    protected function scopeRequestId(QueryBuilder $builder, int $requestId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_request'),
                $builder->createNamedParameter($requestId, ParameterType::INTEGER, $this->nameScopeParameter('requestId'))
            )
        );
    }

    /**
     * Scope for join with users
     */
    protected function bindExtendedUsers(QueryBuilder $builder): void
    {
        /** @var Users_Model $usersModel */
        $usersModel = model(Users_Model::class);
        $usersTable = $usersModel->getTable();

        /** @var User_Groups_Model $userGroupsModel */
        $userGroupsModel = model(User_Groups_Model::class);
        $userGroupsTable = $userGroupsModel->getTable();

        $builder
            ->leftJoin(
                $this->getTable(),
                $usersTable,
                $usersTable,
                "`{$usersTable}`.`{$usersModel->getPrimaryKey()}` = `{$this->getTable()}`.`id_user`"
            )
            ->leftJoin($this->getTable(), $userGroupsTable, $userGroupsTable, "{$usersTable}.user_group = {$userGroupsTable}.{$userGroupsModel->getPrimaryKey()}");
    }

}

// End of file b2b_followers_model.php
// Location: /tinymvc/myapp/models/b2b_followers_model.php
