<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Model Sitemap.
 */
class Sitemap_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected const CREATED_AT = 'generation_date';

    /**
     * {@inheritdoc}
     */
    protected string $table = 'sitemap';

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
    protected array $guarded = array(
        'id',
        self::CREATED_AT,
    );

    /**
     * Counts records in the table filtered by given params.
     *
     * @return int
     */
    public function count_records(array $params = array())
    {
        $params['columns'] = 'COUNT(*) as `counter`';
        $response = $this->findRecord(
            null,
            $this->getTable(),
            null,
            null,
            null,
            $params
        );

        return empty($response) ? 0 : (int) $response['counter'];
    }

    /**
     * Removes the records filtered by given params.
     *
     * @return bool
     */
    public function delete_records(array $params = array())
    {
        return $this->removeRecords(
            null,
            $this->getTable(),
            null,
            $params
        );
    }

    /**
     * Add several records into the table. Deprecated. Use {@see self::insertMany()}.
     *
     * @return string
     *
     * @deprecated
     */
    public function add_records(array $sitemap_data)
    {
        $this->insertMany($sitemap_data);

        return $this->getConnection()->lastInsertId();
    }

    /**
     * Returns sseveral records by given params. Deprecated. Use {@see self::findAllBy()}.
     *
     * @return array
     *
     * @deprecated
     */
    public function get_records(array $params = array())
    {
        return $this->findAllBy($params);
    }

    /**
     * Scope a query to filter by sitemap entity.
     */
    protected function scopeEntity(QueryBuilder $builder, string $entity): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`entity`",
                $builder->createNamedParameter($entity, ParameterType::STRING, $this->nameScopeParameter('entity'))
            )
        );
    }
}

// End of file sitemap_model.php
// Location: /tinymvc/myapp/models/sitemap_model.php
