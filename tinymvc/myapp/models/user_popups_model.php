<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Plugins\Datatable\Output\Button\PopupButton;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Database\Relations\Rule\RelationRule;
use App\Common\Database\Relations\Rule\RuleBuilder;
use Doctrine\DBAL\ParameterType;
use App\Common\Database\PortableModel;
use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Popup_Surveys model
 */
final class User_Popups_Model extends Model
{
    protected const CREATED_AT = 'add_date';

    /**
     * {@inheritdoc}
     */
    protected string $table = "user_popups";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "USER_POPUPS";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;

    /**
     * {@inheritdoc}
     */
    protected array $guarded = [
        'id',
        self::CREATED_AT
    ];


    /**
     * {@inheritdoc}
     */
    protected array $casts = [
        'id'            => Types::INTEGER,
        'id_user'       => Types::INTEGER,
        'id_not_logged' => Types::STRING,
        'id_popup'      => Types::INTEGER,
        'is_viewed'     => Types::INTEGER,
        'add_date'      => Types::DATETIME_IMMUTABLE,
        'show_date'     => Types::DATETIME_IMMUTABLE,
    ];

    /**
     * {@inheritdoc}
     */
    protected array $nullable = [
        'id_user',
        'id_not_logged',
        'show_date',
    ];

    /**
     * Get list of popups for not logged in users
     *
     * Get popups by these conditions:
     * - id of the not logged in user
     * - for_who - not logged or all
     * - is active
     * - type - popup
     * - if popup has to be added to user_popups beforehand (require_user_popups_relation) then show it only if it was added in the user_popups and is_viewed is 0
     *   else if require_user_popups_relation == 0 then
     *        check if in user_popus the popup is not null (was not to user already) then we check if popup is repeatable.
     *              if it is reapeatable then show only the ones which has to be repeated again by checking the date
     *        if it is not repeatable and was already shown we don't show it again
     *        else show the ones that are not in user_popups yet (is null)
     * - the repeatable ones should be the latest (most recent) to be checked for the date
     */
    public function getPopupsForNotLogged($idNotLogged)
    {
        $connection = $this->getHandler()->getConnection();
        $userPopupsTable = $this->getTable();

        /** @var Popup_Model $popup */
        $popup = model(Popup_Model::class);
        $popupTable = $popup->getTable();

        $sql = <<<QUERY
            WITH existing AS (
                SELECT up.*
                FROM (
                SELECT $userPopupsTable.*, ROW_NUMBER() over (PARTITION by $userPopupsTable.id_popup order by $userPopupsTable.show_date DESC) AS recent
                    FROM $userPopupsTable
                    WHERE id_not_logged = :id_user_not_logged
                ) AS up
                JOIN $popupTable AS pop ON up.id_popup=pop.id_popup
                WHERE recent = 1
            )
            SELECT $popupTable.id_popup, $popupTable.popup_hash, $popupTable.view_method, $popupTable.type_popup, $popupTable.repeat_on_cancel, $popupTable.repeat_on_submit, $popupTable.priority, $popupTable.snooze_time, $popupTable.call_on_start, existing.id_not_logged, existing.is_viewed, existing.show_date
            FROM $popupTable LEFT JOIN existing ON existing.id_popup = $popupTable.id_popup
            WHERE
                CASE
                    WHEN popups.require_user_popups_relation = 1 THEN existing.is_viewed=0
                    WHEN popups.require_user_popups_relation = 0 THEN
                        CASE WHEN existing.id_popup IS NOT NULL AND (popups.repeat_on_submit IS NOT NULL OR popups.repeat_on_cancel IS NOT NULL)
                                THEN (IF(existing.viewed_type = 'submit',
                                DATEDIFF(CURDATE(), existing.show_date) >= IFNULL(popups.repeat_on_submit, 0),
                                DATEDIFF(CURDATE(), existing.show_date) >= IFNULL(popups.repeat_on_cancel, 0)))
                            ELSE existing.id_popup IS NULL
                    END
                END
            AND
            ($popupTable.for_who='all' OR $popupTable.for_who='not_logged') AND is_active = 1 AND type = 'popup'
            ORDER BY $popupTable.priority ASC
        QUERY;

        $statement = $connection->prepare($sql);
        $records = $statement->executeQuery(['id_user_not_logged' => $idNotLogged])->fetchAllAssociative();

        /* @todo Resotore in the `v2.42`
        $records = $this->addNestedWithRelations(
            $this->parseNestedRelations(['pages', 'notPages']),
            $records
        );
        */

        // @todo Remove in the `v2.42`
        $records = $this->loadEagerRelations(
            $this->parseRelations(['pages','notPages']),
            $records
        );

        array_walk($records, function(&$value){
            if(!empty($value['pages'])){
                $value['pages'] = array_column($value['pages']->toArray(), 'page_hash');
            }
            if(!empty($value['not_pages'])){
                $value['not_pages'] = array_column($value['not_pages']->toArray(), 'page_hash');
            }
        });

        return $records;
    }

    /**
     * Get popups for logged in users
     *
     * Get popups by these conditions:
     * - id of the logged in user
     * - for_who - logged or all
     * - is active
     * - type - popup
     * - if popup has to be added to user_popups beforehand (require_user_popups_relation) then show it only if it was added in the user_popups and is_viewed is 0
     *   else if require_user_popups_relation == 0 then
     *        check if in user_popus the popup is not null (was not to user already) then we check if popup is repeatable.
     *              if it is reapeatable then show only the ones which has to be repeated again by checking the date
     *        if it is not repeatable and was already shown we don't show it again
     *        else show the ones that are not in user_popups yet (is null)
     * - the repeatable ones should be the latest (most recent) to be checked for the date
     */
    public function getPopupsForLogged($idLogged)
    {
        $connection = $this->getHandler()->getConnection();
        $userPopupsTable = $this->getTable();

        /** @var Popup_Model $popup */
        $popup = model(Popup_Model::class);
        $popupTable = $popup->getTable();

        $sql = <<<QUERY
            WITH existing AS (
                SELECT up.*
                FROM (
                SELECT $userPopupsTable.*, ROW_NUMBER() over (PARTITION by $userPopupsTable.id_popup order by $userPopupsTable.show_date DESC) AS recent
                    FROM $userPopupsTable
                    WHERE id_user = :id_user
                ) AS up
                JOIN $popupTable AS pop ON up.id_popup=pop.id_popup
                WHERE recent = 1
            )
            SELECT $popupTable.id_popup, $popupTable.popup_hash, $popupTable.view_method, $popupTable.type_popup, $popupTable.repeat_on_cancel, $popupTable.repeat_on_submit, $popupTable.priority, $popupTable.snooze_time, $popupTable.call_on_start, existing.id_not_logged, existing.is_viewed, existing.show_date
            FROM $popupTable LEFT JOIN existing ON existing.id_popup = $popupTable.id_popup
            WHERE
                CASE
                    WHEN popups.require_user_popups_relation = 1 THEN existing.is_viewed=0
                    WHEN popups.require_user_popups_relation = 0 THEN
                        CASE WHEN existing.id_popup IS NOT NULL AND (popups.repeat_on_submit IS NOT NULL OR popups.repeat_on_cancel IS NOT NULL)
                                THEN (IF(existing.viewed_type = 'submit',
                                DATEDIFF(CURDATE(), existing.show_date) >= IFNULL(popups.repeat_on_submit, 0),
                                DATEDIFF(CURDATE(), existing.show_date) >= IFNULL(popups.repeat_on_cancel, 0)))
                            ELSE existing.id_popup IS NULL
                    END
                END
            AND
            ($popupTable.for_who='all' OR $popupTable.for_who='logged') AND is_active = 1 AND type = 'popup'
            ORDER BY $popupTable.priority ASC
        QUERY;

        $statement = $connection->prepare($sql);
        $records = $statement->executeQuery(['id_user' => $idLogged])->fetchAllAssociative();

        /* @todo Resotore in the `v2.42`
        $records = $this->addNestedWithRelations(
            $this->parseNestedRelations(['pages', 'notPages']),
            $records
        );
        */

        // @todo Remove in the `v2.42`
        $records = $this->loadEagerRelations(
            $this->parseRelations(['pages','notPages']),
            $records
        );

        array_walk($records, function(&$value){
            if(!empty($value['pages'])){
                $value['pages'] = array_column($value['pages']->toArray(), 'page_hash');
            }
            if(!empty($value['not_pages'])){
                $value['not_pages'] = array_column($value['not_pages']->toArray(), 'page_hash');
            }
        });

        return $records;
    }

    /**
     * Scope a query to filter by userId.
     *
     * @param QueryBuilder $builder
     * @param int $userId
     *
     * @return void
     */
    protected function scopeUserId(QueryBuilder $builder, int $userId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.id_user",
                $builder->createNamedParameter($userId, ParameterType::INTEGER, $this->nameScopeParameter('userId'))
            )
        );
    }

    /**
     * Scope a query to filter by userId.
     *
     * @param QueryBuilder $builder
     * @param int $userId
     *
     * @return void
     */
    protected function scopeIdUser(QueryBuilder $builder, int $userId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.id_user",
                $builder->createNamedParameter($userId, ParameterType::INTEGER, $this->nameScopeParameter('userId'))
            )
        );
    }


    /**
     * Scope a query to filter by popupId.
     *
     * @param QueryBuilder $builder
     * @param int $popupId
     *
     * @return void
     */
    protected function scopeIdPopup(QueryBuilder $builder, int $popupId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.id_popup",
                $builder->createNamedParameter($popupId, ParameterType::INTEGER, $this->nameScopeParameter('popupId'))
            )
        );
    }

    /**
     * Scope a query to filter by idNotLogged.
     *
     * @param QueryBuilder $builder
     * @param string $idNotLogged
     *
     * @return void
     */
    protected function scopeIdNotLogged(QueryBuilder $builder, string $idNotLogged): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.id_not_logged",
                $builder->createNamedParameter($idNotLogged, ParameterType::STRING, $this->nameScopeParameter('idNotLogged'))
            )
        );
    }

    /**
     * Scope a query to filter by Viewed.
     *
     * @param QueryBuilder $builder
     * @param string $popupHash
     *
     * @return void
     */
    protected function scopePopupHash(QueryBuilder $builder, string $popupHash): void
    {
        /** @var Popups_Model $popupsModel */
        $popupsModel = model(Popups_Model::class);

        $subqueryBuilder = $this->createQueryBuilder();

        $subqueryBuilder->select('id_popup');
        $subqueryBuilder->from($popupsModel->getTable());
        $subqueryBuilder->andWhere(
            $builder->expr()->eq(
                'popup_hash',
                $builder->createNamedParameter($popupHash, ParameterType::STRING, $this->nameScopeParameter('popupHash'))
            )
        );

        $builder->andWhere(
            $builder->expr()->in(
                "{$this->getTable()}.id_popup",
                $subqueryBuilder->getSQL()
            )
        );
    }

    /**
     * Resolves static relationships with event city.
     */
    protected function pages(): RelationInterface
    {
        /** @var Popup_page_Model $pagesRelPopup */
        $pagesRelPopup = model(Popup_page_Model::class);
        $pagesPopupTable = $pagesRelPopup->getTable();

        $pageModel = $this->resolveRelatedModel(Page_Model::class);
        $pageModelTable = $pageModel->getTable();
        $relation = $this->hasMany(
            $pagesPopupTable,
            'id_popup',
            'id_popup'
        );
        $relation->disableNativeCast();
        $builder = $relation->getQuery();
        $related = $relation->getRelated();

        $builder
            ->select(
                "$pagesPopupTable.*, $pageModelTable.page_hash ,$pageModelTable.id_page"
            )
            ->innerJoin(
                $table = $related->getTable(),
                $pageModelTable,
                null,
                "{$pageModelTable}.{$pageModel->getPrimaryKey()} = {$table}.id_page"
            )
        ;

        return $relation;
    }

    /**
     * Resolves static relationships with event city.
     */
    protected function notPages(): RelationInterface
    {
        /** @var Popup_Not_Pages_Model $pagesRelPopup */
        $pagesRelPopup = model(Popup_not_pages_Model::class);
        $pagesPopupTable = $pagesRelPopup->getTable();

        $pageModel = $this->resolveRelatedModel(Page_Model::class);
        $pageModelTable = $pageModel->getTable();
        $relation = $this->hasMany(
            $pagesPopupTable,
            'id_popup',
            'id_popup'
        );
        $relation->disableNativeCast();
        $builder = $relation->getQuery();
        $related = $relation->getRelated();

        $builder
            ->select(
                "$pagesPopupTable.*, $pageModelTable.page_hash, $pageModelTable.id_page"
            )
            ->innerJoin(
                $table = $related->getTable(),
                $pageModelTable,
                null,
                "{$pageModelTable}.{$pageModel->getPrimaryKey()} = {$table}.id_page"
            )
        ;

        return $relation;
    }
    /**
     * Scope a query to bind popups to query.
     */
    protected function bindPopups(QueryBuilder $builder): void
    {
        $builder->leftJoin(
            $this->getTable(),
            'popups',
            null,
            "{$this->getTable()}.id_popup = popups.id_popup"
        );
    }

    /**
     * Scope a query to filter by Viewed.
     */
    protected function scopeViewed(QueryBuilder $builder, int $isViewed): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.is_viewed",
                $builder->createNamedParameter((int) $isViewed, ParameterType::INTEGER, $this->nameScopeParameter('isViewed'))
            )
        );
    }

    /**
     * Check if added already
     *
     * @return bool
     */
    public function checkIfNeedToAddRecord(string $idType = 'logged', string $id, int $idPopup, int $days): bool
    {
        if(empty($id) || empty($idPopup) || empty($days)){
            return false;
        }

        $builder = $this->createQueryBuilder();

        #region subquery
        $subqueryBuilder = $this->createQueryBuilder();

        $subqueryBuilder->select('*');
        $subqueryBuilder->from($this->getTable());
        if($idType == 'logged'){
            $subqueryBuilder->where(
                $subqueryBuilder->expr()->eq(
                    "{$this->getTable()}.id_user",
                    $builder->createNamedParameter((int) $id, ParameterType::INTEGER, $this->nameScopeParameter('id'))
                )
            );
        }else{
            $subqueryBuilder->where(
                $subqueryBuilder->expr()->eq(
                    "{$this->getTable()}.id_not_logged",
                    $builder->createNamedParameter((string) $id, ParameterType::STRING, $this->nameScopeParameter('id'))
                )
            );
        }
        $subqueryBuilder->andWhere(
            $subqueryBuilder->expr()->eq(
                "{$this->getTable()}.id_popup",
                $builder->createNamedParameter((int) $idPopup, ParameterType::INTEGER, $this->nameScopeParameter('idPopup'))
            )
        );
        $subqueryBuilder->orderBy('show_date', 'DESC');
        $subqueryBuilder->setMaxResults(1);
        #endregion subquery

        $builder->select('COUNT(*) AS `AGGREGATE`');
        $builder->from("({$subqueryBuilder->getSQL()}) as up");
        $builder->andWhere('DATEDIFF(CURDATE(), up.show_date) < :days')->setParameter('days', $days);
        $latest = $builder->execute()->fetchAssociative();

        if($latest['AGGREGATE'] > 0){
            return false;
        }

        return true;
    }

}

/* End of file popup_surveys_model.php */
/* Location: /tinymvc/myapp/models/popup_surveys_model.php */
