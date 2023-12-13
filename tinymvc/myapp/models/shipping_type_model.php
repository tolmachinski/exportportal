<?php

declare(strict_types=1);

use App\Common\Database\BaseModel;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Shipping_Type_Model.
 *
 * @author Cravciuc Andrei
 * @deprecated in favor of \Shipping_Types_Model
 */
class Shipping_Type_Model extends BaseModel
{
    /**
     * List of columns the table which is later used on create or update.
     * Each record must be an array that have filled keys:
     *  - name: is a name of the column
     *  - type: indicates the type of the column
     *  - fillable: indicates that column is fillable in NORMAL mode (columns with this flag set to `false` are fillable in FORCE mode).
     *
     * @var array
     */
    protected $shipping_type_columns_metadata = array(
        array('name' => 'id_type',          'fillable' => false, 'type' => 'int'),
        array('name' => 'type_alias',       'fillable' => true,  'type' => 'string'),
        array('name' => 'type_name',        'fillable' => true,  'type' => 'string'),
        array('name' => 'type_description', 'fillable' => true,  'type' => 'string'),
    );

    /**
     * List of columns the table which is later used on create or update.
     * Each record must be an array that have filled keys:
     *  - name: is a name of the column
     *  - type: indicates the type of the column
     *  - fillable: indicates that column is fillable in NORMAL mode (columns with this flag set to `false` are fillable in FORCE mode).
     *
     * @var array
     */
    protected $shipper_relation_columns_metadata = array(
        array('name' => 'id_type',    'fillable' => false, 'type' => 'int'),
        array('name' => 'id_shipper', 'fillable' => false, 'type' => 'int'),
    );

    /**
     * The name of the shipping type table.
     *
     * @var string
     */
    private $shipping_type_table = 'shipping_type';

    /**
     * The alias of the shipping type table.
     *
     * @var string
     */
    private $shipping_type_table_alias = 'TYPES';

    /**
     * The name of the table with relation between shipping type and shipper.
     *
     * @var string
     */
    private $shippers_relation_table = 'shipping_type_relation';

    /**
     * The alias of the table with relation between shipping type and shipper.
     *
     * @var string
     */
    private $shippers_relation_table_alias = 'SHIPPERS_RELATIONS';

    public function get($type_id, array $params = array())
    {
        return $this->get_type($type_id, $params);
    }

    public function get_type($type_id, array $params = array())
    {
        return $this->findRecord(
            'type',
            $this->shipping_type_table,
            $this->shipping_type_table_alias,
            'id_type',
            $type_id,
            $params
        );
    }

    public function find_type(array $params = array())
    {
        return $this->findRecord(
            'type',
            $this->shipping_type_table,
            $this->shipping_type_table_alias,
            null,
            null,
            $params
        );
    }

    public function get_all($conditions = array())
    {
        return $this->get_types(array(
            'conditions' => array_filter(array(
                'id'   => isset($conditions['id_type']) && !is_array($conditions['id_type']) ? (int) $conditions['id_type'] : null,
                'list' => isset($conditions['id_type']) && is_array($conditions['id_type']) ? $conditions['id_type'] : null,
            )),
        ));
    }

    public function get_types(array $params = array())
    {
        return $this->findRecords(
            'type',
            $this->shipping_type_table,
            $this->shipping_type_table_alias,
            $params
        );
    }

    public function get_all_by_shipper($shipper_id)
    {
        return $this->find_for_shipper($shipper_id);
    }

    public function find_for_shipper($shipper_id)
    {
        return $this->get_types(array(
            'conditions' => array(
                'shipper' => (int) $shipper_id,
            ),
        ));
    }

    public function insert_shipper_shipping_type_relation($insert = array())
    {
        return empty($insert) ? false : $this->db->insert_batch($this->shippers_relation_table, $insert);
    }

    public function delete_shipper_shipping_type_relation($id_shipper = 0)
    {
        $this->db->where('id_shipper', $id_shipper);

        return $this->db->delete($this->shippers_relation_table);
    }

    /**
     * Scope a query to filter types by ID.
     *
     * @param int $type_id
     */
    protected function scopeTypeId(QueryBuilder $builder, $type_id)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->shipping_type_table_alias}`.`id_type` = ?",
                $builder->createNamedParameter((int) $type_id, ParameterType::INTEGER, $this->nameScopeParameter('typeId'))
            )
        );
    }

    /**
     * Scope a query to filter types by list of IDs.
     *
     * @param mixed $types
     */
    protected function scopeTypeList(QueryBuilder $builder, $types)
    {
        if (empty($types)) {
            return;
        }

        $types = array_map('intval', getArrayFromString($types));

        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->shipping_type_table_alias}`.`id_type`",
                array_map(
                    fn (int $index, $type) => $builder->createNamedParameter(
                        (int) $type,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("typesIds{$index}")
                    ),
                    array_keys($types),
                    $types
                )
            )
        );
    }

    /**
     * Scope a query to filter by shipper.
     *
     * @param int $shipper_id
     */
    protected function scopeTypeShipper(QueryBuilder $builder, $shipper_id)
    {
        $subquery_builder = $this->createQueryBuilder();
        $subquery_builder
            ->select("`{$this->shippers_relation_table_alias}`.`id_type`")
            ->from($this->shippers_relation_table, $this->shippers_relation_table_alias)
            ->where(
                $subquery_builder->expr()->eq(
                    "`{$this->shippers_relation_table_alias}`.`id_shipper`",
                    $builder->createNamedParameter((int) $shipper_id, ParameterType::INTEGER, $this->nameScopeParameter('shipperId'))
                )
            )
        ;

        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->shipping_type_table_alias}`.`id_type`",
                $subquery_builder->getSQL()
            )
        );
    }

    /**
     * Scope a query to bind shippers to the query.
     */
    protected function bindTypeShippersRelations(QueryBuilder $builder)
    {
        $builder->innerJoin(
            $this->shipping_type_table_alias,
            $this->shippers_relation_table,
            $this->shippers_relation_table_alias,
            "`{$this->shipping_type_table_alias}`.`id_type` = `{$this->shippers_relation_table_alias}`.`id_type`"
        );
    }
}
