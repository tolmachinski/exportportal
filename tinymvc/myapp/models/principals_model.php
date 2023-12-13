<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Principals model.
 */
class Principals_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'principals';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id';

    /**
     * {@inheritdoc}
     */
    protected array $guarded = array(
        'id',
    );

    /**
     * {@inheritdoc}
     */
    protected array $casts = array(
        'id'      => Types::INTEGER,
        'context' => Types::JSON,
    );

    /**
     * {@inheritdoc}
     */
    protected array $nullable = array(
        'context',
    );

    /**
     * Scope query for ID.
     *
     * @param QueryBuilder $builder
     * @param int $principalId
     *
     * @return void
     */
    protected function scopeId(QueryBuilder $builder, int $principalId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->getPrimaryKey(),
                $builder->createNamedParameter($principalId, ParameterType::INTEGER, $this->nameScopeParameter('principalId'))
            )
        );
    }

    /**
     * Insert one record.
     *
     * @deprecated
     * @see self::insertOne()
     */
    public function insert_last_id()
    {
        return $this->insertOne(array('context' => '{}'));
    }
}

// End of file principals_model.php
// Location: /tinymvc/myapp/models/principals_model.php
