<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Platforms\MySQL\Types\Types as MySQLTypes;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Model for sample orders statuses.
 */
class Sample_Orders_Statuses_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected const CREATED_AT = 'creation_date';

    /**
     * {@inheritdoc}
     */
    protected const UPDATED_AT = 'update_date';

    /**
     * {@inheritdoc}
     */
    protected string $table = 'sample_orders_statuses';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id';

    /**
     * {@inheritdoc}
     */
    protected bool $timestamps = true;

    /**
     * {@inheritdoc}
     */
    protected int $perPage = 10;

    /**
     * {@inheritdoc}
     */
    protected array $guarded = [
        'id',
        self::CREATED_AT,
        self::UPDATED_AT,
    ];

    /**
     * {@inheritdoc}
     */
    protected array $casts = [
        'id'           => Types::INTEGER,
        'position'     => MySQLTypes::TINYINT,
        'description'  => Types::JSON,
        'notify_users' => MySQLTypes::SET,
    ];

    /**
     * {@inheritdoc}
     */
    protected array $nullable = [
        'position',
        'description',
        'notify_users',
        'creation_date',
        'update_date',
    ];

    /**
     * Get all order statuses.
     *
     * @deprecated
     */
    public function get_statuses(array $params = []): array
    {
        return $this->findRecords(
            'status',
            $this->getTable(),
            null,
            $params
        );
    }

    /**
     * Finds a single record by its alias.
     */
    public function find_by_alias(string $alias): ?array
    {
        return $this->findOneBy(['conditions' => ['alias' => $alias]]);
    }

    /**
     * Finds records with sample count for provided user.
     */
    public function find_with_sample_count_for_user(int $user_id, bool $is_seller = false): array
    {
        return $this->findAllBy([
            'with_count' => ['samples as samples_count' => function (RelationInterface $relation, QueryBuilder $builder) use ($user_id, $is_seller) {
                $builder->andWhere(
                    $builder->expr()->eq(
                        $is_seller ? 'id_seller' : 'id_buyer',
                        $builder->createNamedParameter($user_id, ParameterType::INTEGER, ':userId')
                    )
                );
            }],
        ]);
    }

    /**
     * Updates one record by its alias.
     *
     * @return bool
     */
    public function update_one_by_alias(string $alias, array $record)
    {
        $this->updateMany($record, [
            'conditions' => [
                'alias' => $alias,
            ],
        ]);

        return true;
    }

    /**
     * Scope a query to filter by status alias.
     */
    protected function scopeAlias(QueryBuilder $builder, string $alias): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'alias',
                $builder->createNamedParameter($alias, ParameterType::STRING, $this->nameScopeParameter('type'))
            )
        );
    }

    /**
     * Scope a query to bind statuses to query.
     */
    protected function bindSamples(QueryBuilder $builder): void
    {
        /** @var Sample_Orders_Model $samples */
        $samples = model(Sample_Orders_Model::class);
        $builder->leftJoin(
            $this->getTable(),
            $samples->getTable(),
            null,
            "`{$this->getTable()}`.`{$this->getPrimaryKey()}` = `{$samples->getTable()}`.`id_status`"
        );
    }

    /**
     * Resolves static relationships with samples.
     */
    protected function samples(): RelationInterface
    {
        return $this->hasMany(Sample_Orders_Model::class, 'id_status')->disableNativeCast();
    }
}

// End of file sample_orders_statuses_model.php
// Location: /tinymvc/myapp/models/sample_orders_statuses_model.php
