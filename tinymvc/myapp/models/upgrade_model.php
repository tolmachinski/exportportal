<?php

declare(strict_types=1);

use App\Common\Database\BaseModel;
use App\Common\Database\Concerns;
use App\Common\Database\Model;
use App\Common\Database\PortableModel;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * upgrade_model.php.
 *
 * Upgrade
 *
 * @author Cravciuc Andrei
 *
 * @deprecated v2.30.6 at 2021-12-23 in favor of {@see \Upgrade_Requests_Model}, {@see \Upgrade_Benefits_Model} and {@see \Upgrade_Packages_Model}
 */
class Upgrade_Model extends BaseModel
{
    use Concerns\CanTransformValues;
    use Concerns\ConvertsAttributes;

    /**
     * List of columns the table which is later used on create or update.
     * Each record must be an array that have filled keys:
     *  - name: is a name of the column
     *  - type: indicates the type of the column
     *  - fillable: indicates that column is fillable in NORMAL mode (columns with this flag set to `false` are fillable in FORCE mode).
     *
     * @var array
     */
    protected $request_columns_metadata = array(
        array('name' => 'id_request',       'fillable' => false, 'type' => 'int'),
        array('name' => 'id_user',          'fillable' => true,  'type' => 'int'),
        array('name' => 'id_package',       'fillable' => true,  'type' => 'int'),
        array('name' => 'id_bill',          'fillable' => true,  'type' => 'int'),
        array('name' => 'status',           'fillable' => true,  'type' => 'string'),
        array('name' => 'type',             'fillable' => true,  'type' => 'string'),
        array('name' => 'date_created',     'fillable' => false, 'type' => 'datetime'),
        array('name' => 'date_updated',     'fillable' => false, 'type' => 'datetime'),
        array('name' => 'date_expire',      'fillable' => true, 'type' => 'date'),
    );

    /**
     * Name of the user personal documents table.
     *
     * @var string
     */
    private $upgrade_request_table = 'upgrade_request';

    /**
     * Alias of the user personal documents table.
     *
     * @var string
     */
    private $upgrade_request_table_alias = 'UPGRADE_REQUESTS';

    /**
     * Name of the user personal document types table.
     *
     * @var string
     */
    private $upgrade_benefits_table = 'upgrade_benefits';

    /**
     * Alias of the user personal document types table.
     *
     * @var string
     */
    private $upgrade_benefits_table_alias = 'UPGRADE_BENEFITS';

    /**
     * Name of the user personal document types table.
     *
     * @var string
     */
    private $upgrade_packages_table = 'ugroup_packages';

    /**
     * Alias of the user personal document types table.
     *
     * @var string
     */
    private $upgrade_packages_table_alias = 'UPGRADE_PACKAGES';

    public function create_request(array $request, $force = false)
    {
        return $this->db->insert(
            $this->upgrade_request_table,
            $this->recordAttributesToDatabaseValues(
                $request,
                $this->request_columns_metadata,
                $force
            )
        );
    }

    public function update_request($id_request, array $request, $force = false)
    {
        $this->db->where("`{$this->upgrade_request_table}`.`id_request` = ?", (int) $id_request);

        return $this->db->update(
            $this->upgrade_request_table,
            $this->recordAttributesToDatabaseValues(
                $request,
                $this->request_columns_metadata,
                $force
            )
        );
    }

    public function get_request(array $params = array())
    {
        return $this->findRecord(
            'request',
            $this->upgrade_request_table,
            $this->upgrade_request_table_alias,
            null,
            null,
            $params
        );
    }

    public function get_latest_request(array $params = array())
    {
        $params['limit'] = 1;
        $params['order'] = array_merge(array('id_request' => 'DESC'), arrayGet($params, 'order', array()));

        return $this->findRecord(
            'request',
            $this->upgrade_request_table,
            $this->upgrade_request_table_alias,
            null,
            null,
            $params
        );
    }

    public function delete_request($id_request)
    {
        $this->db->where("`{$this->upgrade_request_table}`.`id_request` = ?", (int) $id_request);

        return $this->db->delete($this->upgrade_request_table);
    }

    public function get_requests(array $params = array())
    {
        return $this->findRecords(
            'request',
            $this->upgrade_request_table,
            $this->upgrade_request_table_alias,
            $params
        );
    }

    public function count_requests(array $params = array())
    {
        unset($params['order'], $params['with'], $params['limit'], $params['skip']);
        if (!isset($params['columns'])) {
            $params['columns'] = array('COUNT(*) AS AGGREGATE');
        }

        $is_multiple = isset($params['multiple']) ? (bool) $params['multiple'] : false;
        $counters = $this->get_requests($params);
        if ($is_multiple) {
            return $counters;
        }

        return (int) arrayGet($counters, '0.AGGREGATE', 0);
    }

    public function get_upgrade_benefits($conditions = array())
    {
        extract($conditions);

        $this->db->select('*');
        $this->db->from($this->upgrade_benefits_table);

        if (isset($id_group)) {
            if (!is_array($id_group)) {
                $id_group = explode(',', $id_group);
            }

            $_where_group = array();
            $groupParams = [];
            foreach ($id_group as $_group) {
                $groupParams[] = $_group = (int) $_group;
                $_where_group[] = " FIND_IN_SET(?, benefit_groups) ";
            }

            if (!empty($_where_group)) {
                $this->db->where_raw(' ( ' . implode(' OR ', $_where_group) . ' ) ', $groupParams);
            }
        }

        if (isset($order_by)) {
            $this->db->orderby($order_by);
        }

        $results = $this->db->query_all();

        return !empty($results) ? $results : array();
    }

    protected function bindRequestPackages(QueryBuilder $builder): void
    {
        $builder->leftJoin(
            $this->upgrade_request_table_alias,
            $this->upgrade_packages_table,
            $this->upgrade_packages_table_alias,
            "{$this->upgrade_request_table_alias}.`id_package` = {$this->upgrade_packages_table_alias}.`idpack`"
        );
    }

    /**
     * Scope a query to filter by request by user.
     *
     * @param int $id_user
     */
    protected function scopeRequestUser(QueryBuilder $builder, $id_user)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->upgrade_request_table_alias}.id_user",
                $builder->createNamedParameter((int) $id_user, ParameterType::INTEGER, $this->nameScopeParameter('requestUserId'))
            )
        );
    }

    /**
     * Scope a query to filter by request by id_package.
     */
    protected function scopeRequestIdPackage(QueryBuilder $builder, int $id_package)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->upgrade_request_table_alias}.id_package",
                $builder->createNamedParameter((int) $id_package, ParameterType::INTEGER, $this->nameScopeParameter('requestPackageId'))
            )
        );
    }

    /**
     * Scope a query to filter by request by package_price.
     */
    protected function scopeRequestPackagePrice(QueryBuilder $builder, string $package_price)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->upgrade_packages_table_alias}.price",
                $builder->createNamedParameter($package_price, ParameterType::STRING, $this->nameScopeParameter('requestPackagePrice'))
            )
        );
    }

    /**
     * Scope a query to filter by request by package_period.
     */
    protected function scopeRequestPackagePeriod(QueryBuilder $builder, int $package_period)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->upgrade_packages_table_alias}.period",
                $builder->createNamedParameter((int) $package_period, ParameterType::INTEGER, $this->nameScopeParameter('requestPackagePeriod'))
            )
        );
    }

    /**
     * Scope a query to filter by request by user.
     *
     * @param int   $id_user
     * @param mixed $date_created
     */
    protected function scopeRequestDateCreated(QueryBuilder $builder, $date_created)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($date_created))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->upgrade_request_table_alias}.date_created",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('requestCreatedAt'))
            )
        );
    }

    /**
     * Scope a query to filter by request by user.
     *
     * @param int $id_user
     */
    protected function scopeRequestIsExpired(QueryBuilder $builder)
    {
        $builder->andWhere(
            $builder->expr()->and(
                $builder->expr()->isNotNull("{$this->upgrade_request_table_alias}.date_expire"),
                $builder->expr()->lte("{$this->upgrade_request_table_alias}.date_expire", 'NOW()')
            )
        );
    }

    /**
     * Scope a query to filter by request by user.
     *
     * @param int $id_user
     */
    protected function scopeRequestIsNotExpired(QueryBuilder $builder)
    {
        $builder->andWhere(
            $builder->expr()->or(
                $builder->expr()->isNull("{$this->upgrade_request_table_alias}.date_expire"),
                $builder->expr()->gt("{$this->upgrade_request_table_alias}.date_expire", 'NOW()')
            )
        );
    }

    /**
     * Scope a query to filter by request by status.
     *
     * @param string $status 'new','confirmed','canceled'
     */
    protected function scopeRequestStatus(QueryBuilder $builder, $status)
    {
        if (empty($status)) {
            return;
        }

        if (is_array($status)) {
            $list = array_map('cleanInput', $status);
        } elseif (is_string($status) && false !== strpos($status, ',')) {
            $list = array_map('cleanInput', explode(',', $status));
        } else {
            $list = array(cleanInput($status));
        }

        $builder->andWhere(
            $builder->expr()->in(
                "{$this->upgrade_request_table_alias}.status",
                array_map(
                    fn (int $index, $status) => $builder->createNamedParameter(
                        $status,
                        ParameterType::STRING,
                        $this->nameScopeParameter("requestStatus{$index}")
                    ),
                    array_keys($list),
                    $list
                )
            )
        );
    }

    /**
     * Scope a query to filter by request by status.
     *
     * @param string $type 'upgrade','extend','downgrade'
     */
    protected function scopeRequestType(QueryBuilder $builder, $type)
    {
        if (empty($type)) {
            return;
        }

        if (is_array($type)) {
            $list = array_map('cleanInput', $type);
        } elseif (is_string($type) && false !== strpos($type, ',')) {
            $list = array_map('cleanInput', explode(',', $type));
        } else {
            $list = array(cleanInput($type));
        }

        $builder->andWhere(
            $builder->expr()->in(
                "{$this->upgrade_request_table_alias}.type",
                array_map(
                    fn (int $index, $type) => $builder->createNamedParameter(
                        $type,
                        ParameterType::STRING,
                        $this->nameScopeParameter("requestType{$index}")
                    ),
                    array_keys($list),
                    $list
                )
            )
        );
    }

    /**
     * Scope a query to filter by creation datetime from.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeRequestCreatedFrom(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "{$this->upgrade_request_table_alias}.date_created",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('requestCreatedFrom'))
            )
        );
    }

    /**
     * Scope a query to filter by creation datetime to.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeRequestCreatedTo(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "{$this->upgrade_request_table_alias}.date_created",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('requestCreatedTo'))
            )
        );
    }

    /**
     * Scope a query to filter by last updated datetime from.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeRequestUpdatedFrom(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "{$this->upgrade_request_table_alias}.date_updated",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('requestUpdatedFrom'))
            )
        );
    }

    /**
     * Scope a query to filter by last updated datetime to.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeRequestUpdatedTo(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "{$this->upgrade_request_table_alias}.date_updated",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('requestUpdatedTo'))
            )
        );
    }

    /**
     * Scope a query to filter by last updated datetime from.
     *
     * @param \DateTimeInterface|int|string $expire_at
     */
    protected function scopeRequestExpireFrom(QueryBuilder $builder, $expire_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($expire_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "{$this->upgrade_request_table_alias}.date_expire",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('requestExpiredFrom'))
            )
        );
    }

    /**
     * Scope a query to filter by last updated datetime to.
     *
     * @param \DateTimeInterface|int|string $expire_at
     */
    protected function scopeRequestExpireTo(QueryBuilder $builder, $expire_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($expire_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "{$this->upgrade_request_table_alias}.date_expire",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('requestExpiredTo'))
            )
        );
    }

    /**
     * Resolves static relationships with packages.
     */
    protected function requestPackage(): RelationInterface
    {
        return $this->belongsTo(
            new PortableModel($this->getHandler(), $this->upgrade_packages_table, 'idpack'),
            'id_package'
        )->disableNativeCast();
    }

    /**
     * Resolves static relationships with packages.
     */
    protected function requestBill(): RelationInterface
    {
        return $this->hasOne(
            new PortableModel($this->getHandler(), 'users_bills', 'id_bill'),
            'id_bill',
            'id_bill'
        )->disableNativeCast();
    }

    /**
     * Resolves static relationships with packages.
     */
    protected function requestUser(): RelationInterface
    {
        return $this->belongsTo(
            new PortableModel($this->getHandler(), 'users', 'idu'),
            'id_user'
        )->disableNativeCast();
    }

    /**
     * Creates new related instance of the model for relation.
     *
     * @param BaseModel|Model|string $source
     */
    protected function resolveRelatedModel($source): Model
    {
        if ($source === $this) {
            return new PortableModel($this->getHandler(), $this->upgrade_request_table_alias, 'id_request');
        }

        return parent::resolveRelatedModel($source);
    }
}
