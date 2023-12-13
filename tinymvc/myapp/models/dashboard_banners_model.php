<?php

declare(strict_types=1);

use App\Common\Database\Concerns;
use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;
use App\Common\Database\Relations\RelationInterface;

/**
 * Dashboard_Banner model.
 */
final class Dashboard_Banners_Model extends Model
{
    use Concerns\CanSearch;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'dashboard_banners';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'DASHBOARD_BANNERS';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'                                       => Types::INTEGER,
        'is_visible'                               => Types::INTEGER,
        'date_created_at'                          => Types::DATETIME_IMMUTABLE,
        'date_updated_at'                          => Types::DATETIME_IMMUTABLE,
    ];

    /**
     * Scope query for ID.
     */
    protected function scopeId(QueryBuilder $builder, int $bannerId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`id`",
                $builder->createNamedParameter($bannerId, ParameterType::INTEGER, $this->nameScopeParameter('banerId'))
            )
        );
    }

    /**
     * Scope query for isVisible.
     */
    protected function scopeIsVisible(QueryBuilder $builder, int $isVisible): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`is_visible`",
                $builder->createNamedParameter($isVisible, ParameterType::INTEGER, $this->nameScopeParameter('isVisible'))
            )
        );
    }

    /**
     * Scope order by keywords.
     */
    protected function scopeKeywords(QueryBuilder $builder, string $keywords): void
    {
        if (empty($keywords)) {
            return;
        }

        $this->appendSearchConditionsToQuery(
            $builder,
            $keywords,
            [],
            ['title', 'subtitle'],
        );
    }

    /**
     * Scope order by order date from.
     */
    protected function scopeCreateDateGte(QueryBuilder $builder, string $createDate): void
    {
        $builder->andWhere(
            $builder->expr()->gte(
                "DATE(`{$this->getTable()}`.`date_created_at`)",
                $builder->createNamedParameter($createDate, ParameterType::STRING, $this->nameScopeParameter('createDateGte'))
            )
        );
    }

    /**
     * Scope order by order date to.
     */
    protected function scopeCreateDateLte(QueryBuilder $builder, string $createDate): void
    {
        $builder->andWhere(
            $builder->expr()->lte(
                "DATE(`{$this->getTable()}`.`date_created_at`)",
                $builder->createNamedParameter($createDate, ParameterType::STRING, $this->nameScopeParameter('createDateLte'))
            )
        );
    }

    /**
     * Scope query for user group ids.
     *
     * @param int[] $userGroupIds
     */
    protected function scopeUserGroupIds(QueryBuilder $builder, array $userGroupIds): void
    {
        /** @var Dashboard_Banners_Relation_Model $dashboardBannersRelationModel */
        $dashboardBannersRelationModel = model(Dashboard_Banners_Relation_Model::class);

        $builder->andWhere(
            $builder->expr()->in(
                "`{$dashboardBannersRelationModel->getTable()}`.`group_id`",
                array_map(
                    fn ($index, $userGroupId) => $builder->createNamedParameter((int) $userGroupId, ParameterType::INTEGER, $this->nameScopeParameter("userGroupIds_{$index}")),
                    array_keys($userGroupIds),
                    $userGroupIds
                )
            )
        );
    }

    /**
     * Left Join with dashboard_banners_relation table.
     */
    protected function bindDashboardBannersRelation(QueryBuilder $builder): void
    {
        /** @var Dashboard_Banners_Relation_Model $dashboardBannersRelationModel */
        $dashboardBannersRelationModel = model(Dashboard_Banners_Relation_Model::class);

        $dashboardBannersRelationTable = $dashboardBannersRelationModel->getTable();
        $builder->leftJoin(
            $this->getTable(),
            $dashboardBannersRelationTable,
            $dashboardBannersRelationTable,
            "`{$dashboardBannersRelationTable}`.`banner_id` = `{$this->getTable()}`.`id`"
        );
    }

    /**
     * Relation with complete dashboard banner relation options.
     */
    protected function userGroups(): RelationInterface
    {
        return $this->hasManyThrough(
            User_Groups_Model::class,
            Dashboard_Banners_Relation_Model::class,
            'banner_id',
            'idgroup',
            $this->getPrimaryKey(),
            'group_id'
        );


    }
}

// End of file dashboard_banner_model.php
// Location: /tinymvc/myapp/models/dashboard_banner_model.php
