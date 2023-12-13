<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

class Image_optimization_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected const CREATED_AT = 'date_of_creation';

    /**
     * {@inheritdoc}
     */
    protected string $table = 'images_for_optimization';

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
     * {@inheritdoc}
     */
    protected array $casts = array(
        'context'    => Types::JSON,
        'error'      => Types::JSON,
        'in_process' => Types::BOOLEAN,
    );

    /**
     * Returns one record found by provided parameters. Deprecated. Use {@see self::findOneBy()}.
     *
     * @return array
     *
     * @deprecated
     */
    public function get_record(array $params = array())
    {
        return $this->findOneBy($params);
    }

    /**
     * Returns the list of records found by provided parameters. Deprecated. Use {@see self::findAllBy()}.
     *
     * @return mixed[]
     *
     * @deprecated
     */
    public function get_records(array $params = array())
    {
        return $this->findAllBy($params);
    }

    /**
     * Inserts one record int table. Deprecated. Use {@see self::insertOne()}.
     *
     * @return string
     *
     * @deprecated
     */
    public function add_record(array $image)
    {
        return $this->insertOne($image);
    }

    /**
     * Add several records into the table. Deprecated. Use {@see self::insertMany()}.
     *
     * @return string
     *
     * @deprecated
     */
    public function add_records(array $records)
    {
        $this->insertMany($records);

        return $this->getConnection()->lastInsertId();
    }

    /**
     * Updates one record by ID. Deprecated. Use {@see self::updateOne()}.
     *
     * @return bool
     *
     * @deprecated
     */
    public function update_record(int $id, array $record)
    {
        return $this->updateOne($id, $record);
    }

    /**
     * Updates many records with one set of changes.
     */
    public function update_records_by_ids(array $ids, array $record): bool
    {
        $this->updateMany($record, array(
            'conditions' => array(
                'ids' => $ids,
            ),
        ));

        return true;
    }

    /**
     * Removes the records filtered by given params.
     *
     * @return bool
     */
    public function delete_record(array $params = array())
    {
        return $this->removeRecords(
            null,
            $this->getTable(),
            null,
            $params
        );
    }

    /**
     * Scope a query to filter by id.
     */
    protected function scopeId(QueryBuilder $builder, int $id): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id',
                $builder->createNamedParameter($id, ParameterType::INTEGER, $this->nameScopeParameter('id'))
            )
        );
    }

    /**
     * Scope a query to filter by ids.
     */
    protected function scopeIds(QueryBuilder $builder, array $ids): void
    {
        $builder->andWhere(
            $builder->expr()->in(
                'id',
                array_map(
                    fn (int $index, $id) => $builder->createNamedParameter($id, null, $this->nameScopeParameter("ids{$index}")),
                    array_keys($ids),
                    $ids
                )
            )
        );
    }

    /**
     * Scope a query to filter by type of image.
     */
    protected function scopeType(QueryBuilder $builder, string $type): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'type',
                $builder->createNamedParameter($type, ParameterType::STRING, $this->nameScopeParameter('type'))
            )
        );
    }

    /**
     * Scope a query to filter by the presence of an error.
     */
    protected function scopeError(QueryBuilder $builder, bool $error_presence): void
    {
        $builder->andWhere(
            $error_presence
                ? $builder->expr()->isNotNull('error')
                : $builder->expr()->isNull('error')
        );
    }

    /**
     * Scope a query to filter by status in_process.
     */
    protected function scopeInProcess(QueryBuilder $builder, int $in_process): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'in_process',
                $builder->createNamedParameter($in_process, ParameterType::INTEGER, $this->nameScopeParameter('inPorcess'))
            )
        );
    }
}

// End of file image_optimization_model.php
// Location: /tinymvc/myapp/models/image_optimization_model.php
