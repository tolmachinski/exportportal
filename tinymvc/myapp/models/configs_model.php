<?php

declare(strict_types=1);

use App\Common\Database\Concerns;
use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Configs model.
 */
final class Configs_Model extends Model
{
    use Concerns\CanSearch;

    /**
     * The name of the "created at" column.
     */
    protected const CREATED_AT = null;

    /**
     * The name of the "updated at" column.
     */
    protected const UPDATED_AT = 'update_date';

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'config';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'CONFIG';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_config';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id',
        self::UPDATED_AT,
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_config'   => Types::INTEGER,
        'update_date' => Types::DATETIME_IMMUTABLE,
        'is_deleted'  => Types::BOOLEAN,
    ];

    /**
     * Finds all key-values tuples.
     */
    public function findTuples(): array
    {
        return array_map(fn (array $record) => [$record['key_config'], $record['value']], $this->findAll());
    }

    /**
     * Finds all key-value pairs.
     */
    public function findPairs(): array
    {
        return array_column($this->findAll(), 'value', 'key_config');
    }

    /**
     * Scope query for ID.
     */
    protected function scopeId(QueryBuilder $builder, int $userId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->getPrimaryKey(),
                $builder->createNamedParameter($userId, ParameterType::INTEGER, $this->nameScopeParameter('configId', true))
            )
        );
    }

    /**
     * Scope query for key.
     */
    protected function scopeKey(QueryBuilder $builder, string $key): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('key_config'),
                $builder->createNamedParameter($key, ParameterType::STRING, $this->nameScopeParameter('configKey', true))
            )
        );
    }

    /**
     * Scope a query to filter by text search.
     */
    protected function scopeSearch(QueryBuilder $builder, string $text): void
    {
        $this->appendSearchConditionsToQuery(
            $builder,
            $text,
            [],
            ['key_config', 'value', 'description'],
        );
    }
}

// End of file configs_model.php
// Location: /tinymvc/myapp/models/configs_model.php
