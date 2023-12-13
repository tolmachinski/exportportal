<?php

declare(strict_types=1);

use App\Common\Database\BaseModel;
use App\Common\Database\Concerns;

/**
 * Activity logs model class.
 *
 * @author Anton Zencenco
 *
 * @deprecated 2.6.x
 */
class Activity_Logs_Model extends BaseModel
{
    use Concerns\CanTransformValues;

    /**
     * Name of the table which contains the activity logs.
     *
     * @var string
     */
    protected $activity_logs_table = 'activity_logs';

    /**
     * Name of the table which contains the activity log resources.
     *
     * @var string
     */
    protected $activity_log_resources_table = 'activity_log_resource_types';

    /**
     * Name of the table which contains the activity log operations.
     *
     * @var string
     */
    protected $activity_log_operations_table = 'activity_log_operation_type';

    /**
     * Name of the table which contains the activity log operations.
     *
     * @var string
     */
    protected $activity_log_examinators_table = 'users';
    protected $activity_log_companies_table = 'company_base';


    /**
     * List of columns in activity log table which is later used on create or update.
     * Each record must be an array that have filled keys:
     *  - name: is a name of the column
     *  - fillable: indicates that column is fillable in NORMAL mode (columns with this flag set to `false` are fillable in FORCE mode).
     *  - datetime: flag which indicates that field has datetime value.
     *
     * @var array
     */
    protected $activity_logs_columns = array(
        array('name' => 'id_log', 'fillable' => false),
        array('name' => 'id_resource', 'fillable' => true),
        array('name' => 'id_initiator', 'fillable' => true),
        array('name' => 'id_examinator', 'fillable' => true),
        array('name' => 'id_resource_type', 'fillable' => true),
        array('name' => 'id_operation_type', 'fillable' => true),
        array('name' => 'date', 'fillable' => true, 'datetime' => true),
        array('name' => 'level', 'fillable' => true),
        array('name' => 'message', 'fillable' => true),
        array('name' => 'context', 'fillable' => true, 'type' => 'json'),
        array('name' => 'is_viewed', 'fillable' => true),
        array('name' => 'level_name', 'fillable' => true),
        array('name' => 'created_at', 'fillable' => false, 'datetime' => true),
        array('name' => 'updated_at', 'fillable' => false, 'datetime' => true),
        array('name' => 'examined_at', 'fillable' => true, 'datetime' => true),
    );

    /**
     * Writes log record to the database.
     *
     * @param array $log
     *
     * @return bool
     */
    public function write_log(array $log)
    {
        return $this->db->insert(
            $this->activity_logs_table,
            $this->recordAttributesToDatabaseValues(
                $log,
                $this->activity_logs_columns
            )
        );
    }

    public function mark_viewed($log_id, $examinator_id = null)
    {
        $this->db->where('id_log = ?', (int) $log_id);

        return $this->db->update(
            $this->activity_logs_table,
            $this->recordAttributesToDatabaseValues(
                array('id_examinator' => $examinator_id, 'is_viewed' => 1, 'examined_at' => date('Y-m-d H:i:s')),
                $this->activity_logs_columns
            )
        );
    }

    public function get_log($id, array $params = array())
    {
        $columns = null;
        $with = array();
        $conditions = array();

        extract($params);
        extract($conditions, EXTR_PREFIX_ALL, 'condition');
        extract($with, EXTR_PREFIX_ALL, 'with');

        $this->db->select($this->prepareColumns($columns));
        $this->db->from("{$this->activity_logs_table} LOGS");

        //region Joins

        if (!empty($with)) {
            if (isset($with_resources) && $with_resources) {
                $this->db->join("{$this->activity_log_resources_table} RESOURCES", 'LOGS.id_resource_type = RESOURCES.id_type', 'left');
                if (is_callable($with_resources)) {
                    $with_resources($this->db, $this);
                }
            }

            if (isset($with_operations) && $with_operations) {
                $this->db->join("{$this->activity_log_operations_table} OPERATIONS", 'LOGS.id_operation_type = OPERATIONS.id_type', 'left');
                if (is_callable($with_operations)) {
                    $with_operations($this->db, $this);
                }
            }
        }

        //endregion Joins

        //region Conditions

        $this->db->where('LOGS.id_log = ?', (int) $id);

        if (!empty($conditions)) {
            // Here be dragons
        }

        //endregion Conditions

        $data = $this->db->query_one();

        return $data ? $data : null;
    }

    public function get_resource_type($id, array $params = array())
    {
        $columns = null;
        $with = array();
        $conditions = array();

        extract($params);
        extract($conditions, EXTR_PREFIX_ALL, 'condition');
        extract($with, EXTR_PREFIX_ALL, 'with');

        $this->db->select($this->prepareColumns($columns));
        $this->db->from("{$this->activity_log_resources_table} RESOURCES");

        //region Joins

        if (!empty($with)) {
            // Here be Dragons
        }

        //endregion Joins

        //region Conditions

        $this->db->where('RESOURCES.id_type = ?', (int) $id);
        if (!empty($conditions)) {
            // Here be Double Dragons
        }

        //endregion Conditions

        $data = $this->db->query_one();

        return $data ? $data : null;
    }

    public function get_logs(array $params = array())
    {
        $skip = null;
        $limit = null;
        $columns = null;
        $with = array();
        $group = array();
        $order = array();
        $conditions = array();

        extract($params);
        extract($conditions, EXTR_PREFIX_ALL, 'condition');
        extract($with, EXTR_PREFIX_ALL, 'with');

        $this->db->select($this->prepareColumns($columns));
        $this->db->from("{$this->activity_logs_table} LOGS");

        //region Joins

        if (!empty($with)) {
            if (isset($with_resources) && $with_resources) {
                $this->db->join("{$this->activity_log_resources_table} RESOURCES", 'LOGS.id_resource_type = RESOURCES.id_type', 'left');
                if (is_callable($with_resources)) {
                    $with_resources($this->db, $this);
                }
            }

            if (isset($with_operations) && $with_operations) {
                $this->db->join("{$this->activity_log_operations_table} OPERATIONS", 'LOGS.id_operation_type = OPERATIONS.id_type', 'left');
                if (is_callable($with_operations)) {
                    $with_operations($this->db, $this);
                }
            }

            if (isset($with_companies) && $with_companies) {
                $this->db->join("{$this->activity_log_companies_table} COMPANIES", 'LOGS.id_resource = COMPANIES.id_company', 'inner');
                if (is_callable($with_companies)) {
                    $with_companies($this->db, $this);
                }
            }
        }

        //endregion Joins

        //region Conditions

        if (!empty($conditions)) {
            if (isset($condition_level)) {
                $this->db->where('level = ?', $condition_level);
            }
            if (isset($condition_viewed)) {
                $this->db->where('is_viewed = ?', $condition_viewed);
            }
            if (isset($condition_resource)) {
                $this->db->where('id_resource = ?', $condition_resource);
            }
            if (isset($condition_initiator)) {
                $this->db->where('id_initiator = ?', $condition_initiator);
            }
            if (isset($condition_initiator_name)) {
                $this->db->where(
                    "(JSON_CONTAINS_PATH(LOGS.context, 'all', '$.user', '$.user.name') AND LOGS.context->'$.user.name' LIKE ?)",
                    "%{$condition_initiator_name}%"
                );
            }
            if (isset($condition_initiator_email)) {
                $this->db->where(
                    "(JSON_CONTAINS_PATH(LOGS.context, 'all', '$.user', '$.user.email') AND LOGS.context->'$.user.email' LIKE ?)",
                    "%{$condition_initiator_email}%"
                );
            }
            if (isset($condition_resource_type)) {
                $this->db->where('id_resource_type = ?', $condition_resource_type);
                if (isset($condition_resource_name) && isset($condition_resource_name_field)) {
                    $this->db->where(
                        "(JSON_CONTAINS_PATH(LOGS.context, 'all', '$.{$condition_resource_name_field}', '$.{$condition_resource_name_field}.name') AND LOGS.context->'$.{$condition_resource_name_field}.name' LIKE ?)",
                        "%{$condition_resource_name}%"
                    );
                }
            }
            if (isset($condition_operation_type)) {
                $this->db->where('id_operation_type = ?', $condition_operation_type);
            }
            if (isset($condition_date_from)) {
                $this->db->where('date >= ?', $condition_date_from);
            }
            if (isset($condition_date_to)) {
                $this->db->where('date <= ?', $condition_date_to);
            }
        }

        //endregion Conditions

        //region GroupBy

        foreach ($group as $column) {
            $this->db->groupby($column);
        }

        //endregion GroupBy

        //region OrderBy

        foreach ($order as $column => $direction) {
            if (!empty($direction) && is_string($direction)) {
                $this->db->orderby("{$column} {$direction}");
            } else {
                $this->db->orderby($column);
            }
        }

        //endregion OrderBy

        //region Limits

        if (null !== $limit) {
            if (null !== $skip) {
                $this->db->limit($limit, $skip);
            } else {
                $this->db->limit($limit);
            }
        }

        //endregion Limits

        $data = $this->db->query_all();

        return !empty($data) ? $data : array();
    }

    public function get_resource_types(array $params = array())
    {
        $skip = null;
        $limit = null;
        $columns = null;
        $with = array();
        $group = array();
        $order = array();
        $conditions = array();

        extract($params);
        extract($conditions, EXTR_PREFIX_ALL, 'condition');
        extract($with, EXTR_PREFIX_ALL, 'with');

        $this->db->select($this->prepareColumns($columns));
        $this->db->from("{$this->activity_log_resources_table} RESOURCES");

        //region Joins

        if (!empty($with)) {
            // Here be Dragons
        }

        //endregion Joins

        //region Conditions

        if (!empty($conditions)) {
            // Here be Double Dragons
        }

        //endregion Conditions

        //region GroupBy

        foreach ($group as $column) {
            $this->db->groupby($column);
        }

        //endregion GroupBy

        //region OrderBy

        foreach ($order as $column => $direction) {
            if (!empty($direction) && is_string($direction)) {
                $this->db->orderby("{$column} {$direction}");
            } else {
                $this->db->orderby($column);
            }
        }

        //endregion OrderBy

        //region Limits

        if (null !== $limit) {
            if (null !== $skip) {
                $this->db->limit($limit, $skip);
            } else {
                $this->db->limit($limit);
            }
        }

        //endregion Limits

        $data = $this->db->query_all();

        return !empty($data) ? $data : array();
    }

    public function get_operation_types(array $params = array())
    {
        $skip = null;
        $limit = null;
        $columns = null;
        $with = array();
        $group = array();
        $order = array();
        $conditions = array();

        extract($params);
        extract($conditions, EXTR_PREFIX_ALL, 'condition');
        extract($with, EXTR_PREFIX_ALL, 'with');

        $this->db->select($this->prepareColumns($columns));
        $this->db->from("{$this->activity_log_operations_table} OPERATIONS");

        //region Joins

        if (!empty($with)) {
            // Here be Dragons
        }

        //endregion Joins

        //region Conditions

        if (!empty($conditions)) {
            // Here be Double Dragons
        }

        //endregion Conditions

        //region GroupBy

        foreach ($group as $column) {
            $this->db->groupby($column);
        }

        //endregion GroupBy

        //region OrderBy

        foreach ($order as $column => $direction) {
            if (!empty($direction) && is_string($direction)) {
                $this->db->orderby("{$column} {$direction}");
            } else {
                $this->db->orderby($column);
            }
        }

        //endregion OrderBy

        //region Limits

        if (null !== $limit) {
            if (null !== $skip) {
                $this->db->limit($limit, $skip);
            } else {
                $this->db->limit($limit);
            }
        }

        //endregion Limits

        $data = $this->db->query_all();

        return !empty($data) ? $data : array();
    }

    public function get_visibility(array $params = array())
    {
        $params = array(
            'columns'    => array('COUNT(is_viewed) as `visibility`', "IF(ANY_VALUE(is_viewed) = 1, 'viewed', 'not_viewed') as `name`"),
            'conditions' => !empty($params['conditions']) ? $params['conditions'] : array(),
            'with' => !empty($params['with']) ? $params['with'] : array(),
            'group'      => array('is_viewed'),
        );
        $visibility = array_map(function ($count) { return (int) $count; }, array_merge(
                array('all' => 0, 'viewed' => 0, 'not_viewed' => 0),
                array_column($this->get_logs($params), 'visibility', 'name')
            )
        );
        $visibility['all'] = $visibility['viewed'] + $visibility['not_viewed'];

        return $visibility;
    }

    public function get_examinators(array $params = array())
    {
        $skip = null;
        $limit = null;
        $columns = null;
        $with = array();
        $group = array();
        $order = array();
        $conditions = array();

        extract($params);
        extract($conditions, EXTR_PREFIX_ALL, 'condition');
        extract($with, EXTR_PREFIX_ALL, 'with');

        $this->db->select($this->prepareColumns($columns));
        $this->db->from("{$this->activity_log_examinators_table} EXAMINATORS");

        //region Joins

        //endregion Joins

        //region Conditions

        $this->db->where('user_type = ?', 'ep_staff');
        if (!empty($conditions)) {

            if(isset($condition_list) && !empty($condition_list) && is_array($condition_list)) {
                $this->db->in("idu", array_map(function($id){ return (int) $id; }, $condition_list));
            }
        }

        //endregion Conditions

        //region GroupBy

        foreach ($group as $column) {
            $this->db->groupby($column);
        }

        //endregion GroupBy

        //region OrderBy

        foreach ($order as $column => $direction) {
            if (!empty($direction) && is_string($direction)) {
                $this->db->orderby("{$column} {$direction}");
            } else {
                $this->db->orderby($column);
            }
        }

        //endregion OrderBy

        //region Limits

        if (null !== $limit) {
            if (null !== $skip) {
                $this->db->limit($limit, $skip);
            } else {
                $this->db->limit($limit);
            }
        }

        //endregion Limits

        $data = $this->db->query_all();

        return !empty($data) ? $data : array();
    }

    public function count_logs(array $params = array())
    {
        $with = array();
        $group = array();
        $conditions = array();

        extract($params);
        extract($conditions, EXTR_PREFIX_ALL, 'condition');
        extract($with, EXTR_PREFIX_ALL, 'with');

        $this->db->select('COUNT(*) as AGGREGATE');
        $this->db->from("{$this->activity_logs_table} LOGS");

        //region Joins

        if (!empty($with)) {
            if (isset($with_resources) && $with_resources) {
                $this->db->join("{$this->activity_log_resources_table} RESOURCES", 'LOGS.id_resource_type = RESOURCES.id_type', 'left');
                if (is_callable($with_resources)) {
                    $with_resources($this->db, $this);
                }
            }

            if (isset($with_operations) && $with_operations) {
                $this->db->join("{$this->activity_log_operations_table} OPERATIONS", 'LOGS.id_operation_type = OPERATIONS.id_type', 'left');
                if (is_callable($with_operations)) {
                    $with_operations($this->db, $this);
                }
            }

            if (isset($with_companies) && $with_companies) {
                $this->db->join("{$this->activity_log_companies_table} COMPANIES", 'LOGS.id_resource = COMPANIES.id_company', 'inner');
                if (is_callable($with_companies)) {
                    $with_companies($this->db, $this);
                }
            }
        }

        //endregion Joins

        //region Conditions

        if (!empty($conditions)) {
            if (isset($condition_level)) {
                $this->db->where('level = ?', $condition_level);
            }
            if (isset($condition_viewed)) {
                $this->db->where('is_viewed = ?', $condition_viewed);
            }
            if (isset($condition_resource)) {
                $this->db->where('id_resource = ?', $condition_resource);
            }
            if (isset($condition_initiator)) {
                $this->db->where('id_initiator = ?', $condition_initiator);
            }
            if (isset($condition_resource_type)) {
                $this->db->where('id_resource_type = ?', $condition_resource_type);
            }
            if (isset($condition_operation_type)) {
                $this->db->where('id_operation_type = ?', $condition_operation_type);
            }
            if (isset($condition_date_from)) {
                $this->db->where('date >= ?', $condition_date_from);
            }
            if (isset($condition_date_to)) {
                $this->db->where('date <= ?', $condition_date_to);
            }
        }

        //endregion Conditions

        //region GroupBy

        foreach ($group as $column) {
            $this->db->groupby($column);
        }

        //endregion GroupBy

        $data = $this->db->query_one();
        if (!$data) {
            return 0;
        }
        if (count($data) !== count($data, COUNT_RECURSIVE)) {
            return $data;
        }

        return isset($data['AGGREGATE']) ? (int) $data['AGGREGATE'] : 0;
    }

    public function deleteActivityLogByUserId($userId)
    {
        $this->db->where('id_initiator', $userId);
        return $this->db->delete($this->activity_logs_table);
    }
}
