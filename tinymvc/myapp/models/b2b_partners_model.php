<?php

declare(strict_types=1);

use App\Common\Database\Concerns;
use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\ParameterType;

/**
 * B2b_Partners_Pivot model.
 */
final class B2b_Partners_Model extends Model
{
    use Concerns\CanSearch;

    /**
     * The name of the "created at" column.
     */
    protected const CREATED_AT = 'date_partnership';

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'b2b_partners';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'B2B_PARTNERS';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'b2b_partners_relation_id';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'b2b_partners_relation_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'b2b_partners_relation_id' => Types::INTEGER,
        'id_company'               => Types::INTEGER,
        'id_partner'               => Types::INTEGER,
        'date_partnership'         => Types::DATETIME_IMMUTABLE,
    ];

    /**
     * Scope a query to filter by text search.
     */
    protected function scopeSearch(QueryBuilder $builder, string $text): void
    {
        $this->appendSearchConditionsToQuery(
            $builder,
            $text,
            ['for_search'],
            ['for_search'],
        );
    }

    /**
     * Scope a query to filter by text search.
     */
    protected function scopeCompanyId(QueryBuilder $builder, int $companyId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_company'),
                $builder->createNamedParameter($companyId, ParameterType::INTEGER, $this->nameScopeParameter('companyId'))
            )
        );
    }

    /**
     * Scope a query to filter by text search.
     *
     * @param QueryBuilder $builder
     * @param int[] $companyIds
     */
    protected function scopeCompanyIds(QueryBuilder $builder, array $companyIds): void
    {
        if (empty($companyIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in('id_company', array_map(
                fn ($i, $companyId) => $builder->createNamedParameter((int) $companyId, ParameterType::INTEGER, $this->nameScopeParameter("companyId{$i}")),
                array_keys($companyIds),
                $companyIds
            ))
        );
    }

    /**
     * Relation with the company.
     */
    protected function company(): RelationInterface
    {
        return $this->belongsTo(Seller_Companies_Model::class, 'id_company')->enableNativeCast();
    }

    /**
     * Relation with the partner.
     */
    protected function partner(): RelationInterface
    {
        /** @var Countries_Model $countryModel */
        $countryModel = model(Countries_Model::class);
        $countryTable = $countryModel->getTable();

        /** @var Users_Model $usersModel */
        $usersModel = model(Users_Model::class);
        $usersTable = $usersModel->getTable();

        /** @var User_Groups_Model $usersGroupsModel */
        $usersGroupsModel = model(User_Groups_Model::class);
        $usersGroupsTable = $usersGroupsModel->getTable();

        $relation = $this->belongsTo(Seller_Companies_Model::class, 'id_partner')->enableNativeCast();
        $relation->enableNativeCast();
        $companyTable = $relation->getRelated()->getTable();
        $builder = $relation->getQuery();
        $builder
            ->select(
                "`{$companyTable}`.`id_company`",
                "`{$companyTable}`.`name_company`",
                "`{$companyTable}`.`logo_company`",
                "`{$companyTable}`.`index_name`",
                "`{$companyTable}`.`id_user`",
                "`{$companyTable}`.`id_country`",
                "`{$companyTable}`.`type_company`",
                "`{$usersTable}`.`user_group`",
                "`{$usersTable}`.`status` as user_status",
                "`{$countryTable}`.`country`",
                "`{$usersGroupsTable}`.`gr_type` AS `group_type`",
            )
            ->leftJoin($companyTable, $countryTable, $countryTable, "`{$companyTable}`.`id_country` = `{$countryTable}`.`{$countryModel->getPrimaryKey()}`")
            ->leftJoin($companyTable, $usersTable, $usersTable, "{$usersTable}.{$usersModel->getPrimaryKey()} = {$companyTable}.id_user")
            ->leftJoin($companyTable, $usersGroupsTable, $usersGroupsTable, "`{$usersGroupsTable}`.`{$usersGroupsModel->getPrimaryKey()}` = `{$usersTable}`.`user_group`")
        ;

        return $relation;
    }
}

// End of file b2b_partners_pivot_model.php
// Location: /tinymvc/myapp/models/b2b_partners_pivot_model.php
