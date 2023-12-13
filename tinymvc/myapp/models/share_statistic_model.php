<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Share_Statistic model
 */
final class Share_Statistic_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "share_statistic";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "SHARE_STATISTIC";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * Add one statistc.
     *
     * @see Share_Statistic_Model::insertOne()
     */
    public function add(array $statistic): int
    {
        return (int) $this->insertOne($statistic, true);
    }

    /**
     * Get the list of share_statistcs.
     */

    public function get_share_statistcs(array $params = []): ?Collection
    {
        $params['order'] = $params['order'] ?? ["`{$this->getTable()}`.`date_created`" => 'DESC'];
        $shareStatistcs = $this->findRecords(
            null,
            $this->getTable(),
            null,
            $params
        );

        if (empty($shareStatistcs)) {
            return null;
        }

        return new ArrayCollection($shareStatistcs);
    }

    public function get_count_share_statistcs(array $params = []): ?int
    {
        $params['columns'] = 'COUNT(*) AS `count_share_statistcs`';

        $response = $this->findRecord(
            null,
            $this->getTable(),
            null,
            null,
            null,
            $params
        );

        return empty($response['count_share_statistcs']) ? 0 : (int) $response['count_share_statistcs'];
    }

        /**
     * Scope a query to filter records by created date.
     *
     * @param mixed $dateCreated
     */
    protected function scopeCreatedFrom(QueryBuilder $builder, $dateCreated): void
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($dateCreated, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "DATE({$this->getTable()}.date_created)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('createdFrom'))
            )
        );
    }

    /**
     * Scope a query to filter records by created date.
     *
     * @param mixed $dateCreated
     */
    protected function scopeCreatedTo(QueryBuilder $builder, $dateCreated): void
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($dateCreated, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "DATE({$this->getTable()}.date_created)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('createdTo'))
            )
        );
    }

    /**
     * Scope a query to filter records by type sharing.
     *
     * @param mixed $typeSharing
     */
    protected function scopeTypeSharing(QueryBuilder $builder, $typeSharing): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.type_sharing",
                $builder->createNamedParameter($typeSharing, ParameterType::STRING, $this->nameScopeParameter('typeSharing'))
            )
        );
    }


    /**
     * Scope a query to filter records by type.
     *
     * @param mixed $type
     */
    protected function scopeType(QueryBuilder $builder, $type): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.type",
                $builder->createNamedParameter($type, ParameterType::STRING, $this->nameScopeParameter('type'))
            )
        );
    }

    /**
     * Scope for join with resources.
     */
    protected function bindUsers(QueryBuilder $builder): void
    {
        /** @var Users_Model $resources */
        $resources = model(Users_Model::class);
        $builder
            ->leftJoin(
                $this->getTable(),
                $resources->getTable(),
                $resources->getTable(),
                "`{$resources->getTable()}`.`{$resources->getPrimaryKey()}` = `{$this->getTable()}`.`id_user`"
            )
        ;
    }
}

/* End of file share_statistic_model.php */
/* Location: /tinymvc/myapp/models/share_statistic_model.php */
