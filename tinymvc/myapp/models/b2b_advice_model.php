<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\ParameterType;

/**
 * B2b_Advice model.
 */
final class B2b_Advice_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'b2b_request_advices';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'B2B_REQUEST_ADVICES';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_advice';

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_advice'                   => Types::INTEGER,
        'id_request'                  => Types::INTEGER,
        'id_user'                     => Types::INTEGER,
        'message_advice'              => Types::TEXT,
        'date_advice'                 => Types::DATETIME_IMMUTABLE,
        'count_plus'                  => Types::INTEGER,
        'count_minus'                 => Types::INTEGER,
        'moderated'                   => Types::BOOLEAN,
    ];

    /**
     * Scope a query to filter by advice ID.
     */
    protected function scopeId(QueryBuilder $builder, int $adviceId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn($this->getPrimaryKey()),
                $builder->createNamedParameter($adviceId, ParameterType::INTEGER, $this->nameScopeParameter('adviceId'))
            )
        );
    }

    /**
     * Scope a query to filter id of the user
     */
    protected function scopeUserId(QueryBuilder $builder, int $userId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_user'),
                $builder->createNamedParameter($userId, ParameterType::INTEGER, $this->nameScopeParameter('userId'))
            )
        );
    }

    /**
     * Scope a query to filter id of the request
     */
    protected function scopeRequestId(QueryBuilder $builder, int $requestId): void
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
    protected function bindUsers(QueryBuilder $builder): void
    {
        /** @var Users_Model $usersModel */
        $usersModel = model(Users_Model::class);
        $usersTable = $usersModel->getTable();

        $builder
            ->leftJoin(
                $this->getTable(),
                $usersTable,
                $usersTable,
                "`{$usersTable}`.`{$usersModel->getPrimaryKey()}` = `{$this->getTable()}`.`id_user`"
            );
    }
}

// End of file b2b_advice_model.php
// Location: /tinymvc/myapp/models/b2b_advice_model.php
