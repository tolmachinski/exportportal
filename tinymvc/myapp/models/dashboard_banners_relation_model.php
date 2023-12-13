<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * User_Group_Relation model.
 */
final class Dashboard_Banners_Relation_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'dashboard_banners_relation';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'DASHBOARD_BANNERS_RELATION';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'                                       => Types::INTEGER,
        'banner_id'                                => Types::INTEGER,
        'group_id'                                 => Types::INTEGER,
    ];

     /**
     * Scope query for banner_id.
     */
    protected function scopeBannerId(QueryBuilder $builder, int $bannerId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`banner_id`",
                $builder->createNamedParameter($bannerId, ParameterType::INTEGER, $this->nameScopeParameter('banerId'))
            )
        );
    }

     /**
     * Scope query for user group ids.
     *
     * @param int[] $userGroupIds
     */
    protected function scopeUserGroupsIds(QueryBuilder $builder, array $userGroupIds): void
    {

        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->getTable()}`.`group_id`",
                array_map(
                    fn ($index, $userGroupId) => $builder->createNamedParameter((int) $userGroupId, ParameterType::INTEGER, $this->nameScopeParameter("userGroupIds_{$index}")),
                    array_keys($userGroupIds),
                    $userGroupIds
                )
            )
        );
    }
}

// End of file user_group_relation_model.php
// Location: /tinymvc/myapp/models/user_group_relation_model.php
