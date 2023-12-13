<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use App\Common\Database\PortableModel;
use App\Common\Database\Relations\RelationInterface;
use Symfony\Component\String\UnicodeString;

/**
 * Draft_Extend_Requests_Model model.
 */
final class Draft_Extend_Requests_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'draft_extend_requests';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'DRAFT_EXTEND_REQUESTS';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id';


    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'extend_date',
        'items',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'is_requested'    => Types::INTEGER,
        'text'            => Types::TEXT,
        'status'          => Types::STRING,
        'extend_date'     => Types::DATE_IMMUTABLE,
        'expiration_date' => Types::DATE_IMMUTABLE,
        'requested_date'  => Types::DATETIME_IMMUTABLE,
        'id_user'         => Types::INTEGER,
        'id'              => Types::INTEGER,
    ];

    public function getRequestsBy($conditions)
    {
        return $this->findAllBy([
            // 'columns'    => [
            //     $this->qualifyColumn('`id`'),
            //     $this->qualifyColumn('`id_user`'),
            //     $this->qualifyColumn('`id_envelope`'),
            //     $this->qualifyColumn('`due_date`')
            // ],
            'with'       => ['user'],
            'conditions' => [
                'expirationDate' => $conditions['expirationDate'],
            ],
        ]);
    }

    public function getRequestsWithItemsCount($conditions)
    {
        return $this->findAllBy([
            'columns'    => [
                "{$this->getTable()}.*",
                "(SELECT COUNT(id)
                  FROM items
                  WHERE items.draft = 1
                  AND items.id_seller = {$this->getTable()}.id_user
                  AND DATE(items.draft_expire_date) = \"{$conditions['expirationDate']->format('Y-m-d')}\") AS count_new"
            ],
            'with'       => ['user'],
            'conditions' => [
                'expirationDate' => $conditions['expirationDate'],
            ],
        ]);
    }

    /**
     * Returns the paginator prepared for datagrid.
     */
    public function paginateForAdminGrid(?array $commonFilters = [], ?array $filters = [], ?array $ordering = [], ?int $perPage = null, ?int $page = 1): array
    {
        $paginator = $this->getPaginator(['conditions' => $allFilters = array_merge($commonFilters ?? [], $filters ?? [])], $perPage, $page);
        $paginator['all'] = $this->countBy(['conditions' => $allFilters]);

        $paginator['data'] = $this->findAllBy([
                'with'       => [
                    'user',
                ],
                'conditions' => $allFilters,
                'order'      => $ordering ?? [],
                'limit'      => $perPage,
                'skip'       => (($page ?? 1) - 1) * $perPage,
        ]);

        return $paginator;
    }

    /**
     * Relation with the user.
     */
    protected function user(): RelationInterface
    {
        /** @var User_Model $users */
        $users = model(User_Model::class);

        $relation = $this->belongsTo(
            new PortableModel($this->getHandler(), $usersTable = $users->get_users_table(), $primaryKey = $users->get_users_table_primary_key()),
            'id_user'
        );
        //$relation = $this->belongsTo(new PortableModel($this->getHandler(), 'users', 'idu'), 'id_user');
        $relation->disableNativeCast();
        $builder = $relation->getQuery();

        $builder->select("$primaryKey, `{$usersTable}`.`fname`, `{$usersTable}`.`lname`, `{$usersTable}`.`email`");

        return $relation;
    }

    /**
     * Scope event by ID
     *
     * @var int $requestId
     *
     * @return void
     */
    protected function scopeId(QueryBuilder $builder, int $requestId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->getPrimaryKey(),
                $builder->createNamedParameter($requestId, ParameterType::INTEGER, $this->nameScopeParameter('requestId'))
            )
        );
    }

    /**
     * Scope event by bool vallue
     *
     * @var bool $requestId
     *
     * @return void
     */
    protected function scopeIsRequested(QueryBuilder $builder, bool $isRequested): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.`is_requested`",
                $builder->createNamedParameter($isRequested, ParameterType::BOOLEAN, $this->nameScopeParameter('isRequested'))
            )
        );
    }

    /**
     * Scope query for specific sender.
     *
     * @param \DateTimeInterface|int|string $dueDate
     */
    protected function scopeExpirationDate(QueryBuilder $builder, DateTimeImmutable $expirationDate): void
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($expirationDate, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "DATE({$this->getTable()}.expiration_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('expirationDate'))
            )
        );
    }

    /**
     * Scope comment query by type.
     */
    protected function scopeIdUser(QueryBuilder $builder, int $idUser): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.`id_user`",
                $builder->createNamedParameter($idUser, ParameterType::INTEGER, $this->nameScopeParameter('id_user'))
            )
        );
    }

    /**
     * Scope comment query by type.
     */
    protected function scopeStatus(QueryBuilder $builder, string $status): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.`status`",
                $builder->createNamedParameter($status, ParameterType::STRING, $this->nameScopeParameter('status'))
            )
        );
    }

    /**
     * Scope a query to filter by creation date from.
     *
     * @param \DateTimeInterface|int|string $createdAt
     */
    protected function scopeRequestedFrom(QueryBuilder $builder, $createdAt)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($createdAt, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "DATE({$this->getTable()}.requested_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('requestedFrom'))
            )
        );
    }


    /**
     * Scope a query to filter by creation date to.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeRequestedTo(QueryBuilder $builder, $createdAt)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($createdAt, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "DATE({$this->getTable()}.requested_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('requestedTo'))
            )
        );
    }

    /**
     * Scope a query to filter by creation date from.
     *
     * @param \DateTimeInterface|int|string $createdAt
     */
    protected function scopeExpirationFrom(QueryBuilder $builder, $expiresAt)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($expiresAt, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "DATE({$this->getTable()}.expiration_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('expirationFrom'))
            )
        );
    }


    /**
     * Scope a query to filter by creation date to.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeExpirationTo(QueryBuilder $builder, $expiresAt)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($expiresAt, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "DATE({$this->getTable()}.expiration_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('expirationTo'))
            )
        );
    }

     /**
     * Scope a query to filter by text search.
     */
    protected function scopeSearch(QueryBuilder $builder, string $text): void
    {
        $searchText = (new UnicodeString($text))->trim();

        /** @var User_Model $users */
        $users = model(User_Model::class);

        $expressions = $builder->expr();
        $builder->andWhere(
            $expressions->or(
                $expressions->like(
                    "{$this->getTable()}.extend_reason",
                    $builder->createNamedParameter(
                        (string) $searchText->prepend('%')->append('%'),
                        ParameterType::STRING,
                        $this->nameScopeParameter('searchText')
                    )
                )
            )
        );
    }
}

// End of file draft_extend_requests_model_model.php
// Location: /tinymvc/myapp/models/draft_extend_requestsmodel.php
