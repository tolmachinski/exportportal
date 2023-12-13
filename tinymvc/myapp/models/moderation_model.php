<?php

declare(strict_types=1);

use App\Common\Database\BaseModel;
use App\Common\Database\Concerns;
use App\Common\Traits\InterpolatableTrait;
use Ramsey\Uuid\Exception\UnableToBuildUuidException;
use Ramsey\Uuid\Uuid;

use const App\Moderation\Messages\MESSAGE_BLOCK;
use const App\Moderation\Messages\MESSAGE_IMMODERATE;
use const App\Moderation\Messages\MESSAGE_MODERATE;
use const App\Moderation\Messages\MESSAGE_NOTICE;
use const App\Moderation\Messages\MESSAGE_UNBLOCK;
use const App\Moderation\Types\TYPE_B2B;
use const App\Moderation\Types\TYPE_B2B_NAME;
use const App\Moderation\Types\TYPE_COMPANY;
use const App\Moderation\Types\TYPE_COMPANY_NAME;
use const App\Moderation\Types\TYPE_ITEM;
use const App\Moderation\Types\TYPE_ITEM_NAME;

/**
 * Activity logs model class.
 *
 * @author Anton Zencenco
 */
class Moderation_Model extends BaseModel
{
    use InterpolatableTrait;
    use Concerns\CanTransformValues;

    /**
     * ElasticSearch handler.
     *
     * @var null|\TinyMVC_Library_Elasticsearch
     */
    private $elasticsearch;

    /**
     * Known types mapping.
     *
     * @var string[]
     */
    private $types = array(
        TYPE_B2B     => TYPE_B2B_NAME,
        TYPE_ITEM    => TYPE_ITEM_NAME,
        TYPE_COMPANY => TYPE_COMPANY_NAME,
    );

    /**
     * List of columns in activity log table which is later used on create or update.
     * Each record must be an array that have filled keys:
     *  - name: is a name of the column
     *  - fillable: indicates that column is fillable in NORMAL mode (columns with this flag set to `false` are fillable in FORCE mode).
     *  - type: indicates the type of the column.
     *
     * @var array
     */
    private $metadata = array(
        array('name' => 'moderation_is_approved',  'fillable' => true, 'type' => 'int'),
        array('name' => 'moderation_is_blocked',   'fillable' => true, 'type' => 'int'),
        array('name' => 'moderation_approved_at',  'fillable' => true, 'type' => 'datetime'),
        array('name' => 'moderation_blocked_at',   'fillable' => true, 'type' => 'datetime'),
        array('name' => 'moderation_unblocked_at', 'fillable' => true, 'type' => 'datetime'),
        array('name' => 'moderation_noticed_at',   'fillable' => true, 'type' => 'datetime'),
        array('name' => 'moderation_notices',      'fillable' => true, 'type' => 'object', 'nullable' => true),
        array('name' => 'moderation_blocking',     'fillable' => true, 'type' => 'object', 'nullable' => true),
        array('name' => 'moderation_activity',     'fillable' => true, 'type' => 'object', 'nullable' => true),
    );

    /**
     * B2B request definition.
     *
     * @var array
     */
    private $b2b_definition = array(
        'table'      => 'b2b_request',
        'primary'    => 'id_request',
        'author'     => 'id_user',
        'projection' => array(
            '`id_request` as `id`',
            '`b2b_title` as `title`',
            '`b2b_date_register` as `created_at`',
            '`b2b_date_update` as `updated_at`',
            '`id_user`'
        ),
        'mask' => array(
            'id'                    => 'id_request',
            'title'                 => 'b2b_title',
            'created_at'            => 'b2b_date_register',
            'updated_at'            => 'b2b_date_update',
            'moderation_is_blocked' => 'blocked',
        ),
    );

    /**
     * Items definition.
     *
     * @var array
     */
    private $items_definition = array(
        'table'      => 'items',
        'primary'    => 'id',
        'author'     => 'id_seller',
        'projection' => array(
            '`id`',
            '`title`',
            '`create_date` as `created_at`',
            '`update_date` as `updated_at`',
            '`id_seller` as `id_user`',
            '`is_distributor`',
            '`final_price`',
            '`is_handmade`',
        ),
        'mask' => array(
            'title'                 => 'title',
            'created_at'            => 'create_date',
            'updated_at'            => 'update_date',
            'moderation_is_blocked' => 'blocked',
            'draft'                 => 'draft',
            'is_handmade'           => 'is_handmade',
        ),
    );

    /**
     * Items definition.
     *
     * @var array
     */
    private $companies_definition = array(
        'table'      => 'company_base',
        'primary'    => 'id_company',
        'author'     => 'id_user',
        'projection' => array(
            '`id_company` as `id`',
            '`parent_company` as `parent`',
            '`type_company` as `type`',
            '`name_company` as `title`',
            '`index_name` as `index_title`',
            '`registered_company` as `created_at`',
            '`updated_company` as `updated_at`',
            '`id_user`'
        ),
        'mask' => array(
            'id'                    => 'id_company',
            'title'                 => 'name_company',
            'created_at'            => 'register_company',
            'moderation_is_blocked' => 'blocked',
        ),
    );

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->elasticsearch = library(TinyMVC_Library_Elasticsearch::class);
    }

    public function get_resource($resource_id, $type, array $params = array())
    {
        if (!property_exists($this, "{$type}_definition")) {
            throw new \InvalidArgumentException('The type argument has invalid or unknown value');
        }

        $with = array();
        $columns = array();

        extract($params);
        extract($with, EXTR_PREFIX_ALL, 'with');

        $definition = $this->{"{$type}_definition"};
        $primary = $definition['primary'];
        $table = $definition['table'];
        $mask = $definition['mask'];
        $base_columns = array_map(function ($column) use ($table) { return "{$table}.{$column}"; }, $definition['projection']);

        $this->db->select($this->prepareColumns(array_merge(
            $base_columns,
            array(
                "{$table}.{$this->unmask_column('moderation_is_approved', $mask)} as `moderation_is_approved`",
                "{$table}.{$this->unmask_column('moderation_is_blocked', $mask)} as `moderation_is_blocked`",
                "{$table}.{$this->unmask_column('moderation_unblocked_at', $mask)} as `moderation_unblocked_at`",
                "{$table}.{$this->unmask_column('moderation_approved_at', $mask)} as `moderation_approved_at`",
                "{$table}.{$this->unmask_column('moderation_blocked_at', $mask)} as `moderation_blocked_at`",
                "{$table}.{$this->unmask_column('moderation_noticed_at', $mask)} as `moderation_noticed_at`",
                "{$table}.{$this->unmask_column('moderation_notices', $mask)} as `moderation_notices`",
                "{$table}.{$this->unmask_column('moderation_activity', $mask)} as `moderation_activity`",
                "{$table}.{$this->unmask_column('moderation_blocking', $mask)} as `moderation_blocking`",
            ),
            $columns,
            ['users.activation_account_date']
        )));
        $this->db->from($table);

        //region Joins
        if (!empty($with)) {
            if (isset($with_group) && $with_group) {
                if (!isset($with_author)) {
                    $with_author = true;
                }

                $this->with_author($type, $table, $definition, $with_author);
                $this->with_group($with_group);
                unset($with_author);
            }
            if (isset($with_author) && $with_author) {
                $this->with_author($type, $table, $definition, $with_author);
            }
        }
        //endregion Joins

        //region Conditions
        $this->db->where("{$table}.{$this->unmask_column('id', $mask)} = ?", $resource_id);
        //endregion Conditions

        $data = $this->db->query_one();
        if (!$data) {
            return null;
        }

        return $this->transform_resource($data, $type);
    }

    public function find_not_moderated($type, array $params = array())
    {
        if (!property_exists($this, "{$type}_definition")) {
            throw new \InvalidArgumentException('The type argument has invalid or unknown value');
        }

        $with = array();
        $order = array();
        $group = array();
        $columns = array();
        $conditions = array();

        extract($params);
        extract($with, EXTR_PREFIX_ALL, 'with');
        extract($conditions, EXTR_PREFIX_ALL, 'condition');

        $definition = $this->{"{$type}_definition"};
        $primary = $definition['primary'];
        $table = $definition['table'];
        $mask = $definition['mask'];
        $base_columns = array_map(function ($column) use ($table) { return "{$table}.{$column}"; }, $definition['projection']);
        if ($with_author ?? false) {
            $base_columns[] = "users.activation_account_date";
        }

        $this->db->select($this->prepareColumns(array_merge(
            $base_columns,
            array(
                "{$table}.{$this->unmask_column('moderation_is_approved', $mask)} as `moderation_is_approved`",
                "{$table}.{$this->unmask_column('moderation_is_blocked', $mask)} as `moderation_is_blocked`",
                "{$table}.{$this->unmask_column('moderation_unblocked_at', $mask)} as `moderation_unblocked_at`",
                "{$table}.{$this->unmask_column('moderation_approved_at', $mask)} as `moderation_approved_at`",
                "{$table}.{$this->unmask_column('moderation_blocked_at', $mask)} as `moderation_blocked_at`",
                "{$table}.{$this->unmask_column('moderation_noticed_at', $mask)} as `moderation_noticed_at`",
                "{$table}.{$this->unmask_column('moderation_notices', $mask)} as `moderation_notices`",
                "{$table}.{$this->unmask_column('moderation_activity', $mask)} as `moderation_activity`",
                "{$table}.{$this->unmask_column('moderation_blocking', $mask)} as `moderation_blocking`",
            ),
            $columns
        )));
        $this->db->from($table);

        //region Joins
        if (!empty($with)) {
            if (isset($with_author) && $with_author) {
                $this->with_author($type, $table, $definition, $with_author);
            }
        }
        //endregion Joins

        //region Conditions
        $this->db->where("{$table}.{$this->unmask_column('moderation_is_approved', $mask)} = ?", 0);
        if (!empty($conditions)) {
            if (isset($condition_keywords)) {
                $this->db->where_raw("{$table}.{$this->unmask_column('title', $mask)} LIKE ?", "%{$condition_keywords}%");
            }

            if (isset($condition_is_draft)) {
                $this->db->where("{$table}.{$this->unmask_column('draft', $mask)} = ?", (int) $condition_is_draft);
            }

            if (isset($condition_blocked)) {
                $this->db->where("{$table}.{$this->unmask_column('moderation_is_blocked', $mask)} = ?", (int) $condition_blocked);
            }

            if (isset($condition_created_from)) {
                $this->db->where("{$table}.{$this->unmask_column('created_at', $mask)} >= ?", $condition_created_from);
            }

            if (isset($condition_created_to)) {

                $this->db->where("{$table}.{$this->unmask_column('created_at', $mask)} <= ?", $condition_created_to);
            }
            if (isset($condition_updated_from)) {
                $this->db->where("{$table}.{$this->unmask_column('updated_at', $mask)} >= ?", $condition_updated_from);
            }

            if (isset($condition_updated_to)) {
                $this->db->where("{$table}.{$this->unmask_column('updated_at', $mask)} <= ?", $condition_updated_to);
            }

            if (isset($condition_updated_company_from)) {
                $this->db->where("{$table}.{$this->unmask_column('updated_company', $mask)} >= ?", $condition_updated_company_from);
            }

            if (isset($condition_updated_company_to)) {
                $this->db->where("{$table}.{$this->unmask_column('updated_company', $mask)} <= ?", $condition_updated_company_to);
            }

            if (isset($condition_registered_company_from)) {
                $this->db->where("{$table}.{$this->unmask_column('registered_company', $mask)} >= ?", $condition_registered_company_from);
            }

            if (isset($condition_registered_company_to)) {
                $this->db->where("{$table}.{$this->unmask_column('registered_company', $mask)} <= ?", $condition_registered_company_to);
            }

            if (!empty($condition_activated_from)) {
                $this->db->where("DATE(users.activation_account_date) >= ?", $condition_activated_from);
            }

            if (!empty($condition_activated_to)) {
                $this->db->where("DATE(users.activation_account_date) <= ?", $condition_activated_to);
            }

            if (!empty($condition_status)) {
                $this->db->where("users.status = ?", $condition_status);
            }
        }
        //endregion Conditions

        //region GroupBy
        foreach ($group as $column) {
            $this->db->groupby($this->unmask_column($column, $mask));
        }
        //endregion GroupBy

        //region OrderBy
        foreach ($order as $column => $direction) {
            $column = $this->unmask_column($column, $mask);
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

        return !empty($data)
            ? array_map(function ($item) use ($type) { return $this->transform_resource($item, $type); }, $data)
            : array();
    }

    public function count_not_moderated($type, array $params = array())
    {
        if (!property_exists($this, "{$type}_definition")) {
            throw new \InvalidArgumentException('The type argument has invalid or unknown value');
        }

        $definition = $this->{"{$type}_definition"};
        $primary = $definition['primary'];
        $table = $definition['table'];
        $mask = $definition['mask'];
        $columns = $definition['projection'];
        $with = array();
        $group = array();
        $conditions = array();

        extract($params);
        extract($conditions, EXTR_PREFIX_ALL, 'condition');
        extract($with, EXTR_PREFIX_ALL, 'with');

        $this->db->select('COUNT(*) as AGGREGATE');
        $this->db->from($table);

        //region Joins
        if (!empty($with)) {
            if (isset($with_author) && $with_author) {
                $this->with_author($type, $table, $definition, $with_author);
            }
        }
        //endregion Joins

        //region Conditions
        $this->db->where("{$table}.{$this->unmask_column('moderation_is_approved', $mask)} = ?", 0);
        if (!empty($conditions)) {
            if (isset($condition_keywords)) {
                $this->db->where_raw("{$table}.{$this->unmask_column('title', $mask)} LIKE ?", "%{$condition_keywords}%");
            }

            if (isset($condition_is_draft)) {
                $this->db->where("{$table}.{$this->unmask_column('draft', $mask)} = ?", (int) $condition_is_draft);
            }

            if (isset($condition_blocked)) {
                $this->db->where("{$table}.{$this->unmask_column('moderation_is_blocked', $mask)} = ?", (int) $condition_blocked);
            }

            if (isset($condition_created_from)) {
                $this->db->where("{$table}.{$this->unmask_column('created_at', $mask)} >= ?", $condition_created_from);
            }

            if (isset($condition_created_to)) {
                $this->db->where("{$table}.{$this->unmask_column('created_at', $mask)} <= ?", $condition_created_to);
            }

            if (isset($condition_updated_from)) {
                $this->db->where("{$table}.{$this->unmask_column('updated_at', $mask)} >= ?", $condition_updated_from);
            }

            if (isset($condition_updated_to)) {
                $this->db->where("{$table}.{$this->unmask_column('updated_at', $mask)} <= ?", $condition_updated_to);
            }

            if (isset($condition_updated_company_from)) {
                $this->db->where("{$table}.{$this->unmask_column('updated_company', $mask)} >= ?", $condition_updated_company_from);
            }

            if (isset($condition_updated_company_to)) {
                $this->db->where("{$table}.{$this->unmask_column('updated_company', $mask)} <= ?", $condition_updated_company_to);
            }

            if (isset($condition_registered_company_from)) {
                $this->db->where("{$table}.{$this->unmask_column('registered_company', $mask)} >= ?", $condition_registered_company_from);
            }

            if (isset($condition_registered_company_to)) {
                $this->db->where("{$table}.{$this->unmask_column('registered_company', $mask)} <= ?", $condition_registered_company_to);
            }

            if (!empty($condition_activated_from)) {
                $this->db->where("DATE(users.activation_account_date) >= ?", $condition_activated_from);
            }

            if (!empty($condition_activated_to)) {
                $this->db->where("DATE(users.activation_account_date) <= ?", $condition_activated_to);
            }

            if (!empty($condition_status)) {
                $this->db->where("users.status = ?", $condition_status);
            }
        }
        //endregion Conditions

        //region GroupBy
        foreach ($group as $column) {
            $this->db->groupby($this->unmask_column($column, $mask));
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

    public function get_accessibility($type, array $params = array())
    {
        if (!property_exists($this, "{$type}_definition")) {
            throw new \InvalidArgumentException('The type argument has invalid or unknown value');
        }

        $definition = $this->{"{$type}_definition"};
        $primary = $definition['primary'];
        $table = $definition['table'];
        $mask = $definition['mask'];

        $params = array(
            'columns'    => array(
                "COUNT({$table}.{$this->unmask_column('moderation_is_blocked', $mask)}) as `accessibility`",
                "IF(
                    ANY_VALUE({$table}.{$this->unmask_column('moderation_is_blocked', $mask)}) = 1,
                    'blocked',
                    IF(
                        ANY_VALUE({$table}.{$this->unmask_column('moderation_is_blocked', $mask)}) = 2,
                        'locked',
                        'accessible'
                    )
                ) as `name`",
            ),
            'conditions' => !empty($params['conditions']) ? array_merge($params['conditions'], array('blocked' => null)) : array(),
            'with'       => !empty($params['with']) ? $params['with'] : array(),
            'group'      => array("{$table}.{$this->unmask_column('moderation_is_blocked', $mask)}"),
        );
        $accessibility = array_map(
            function ($count) { return (int) $count; },
            array_merge(
                array('all' => 0, 'blocked' => 0, 'locked' => 0, 'accessible' => 0),
                array_column($this->find_not_moderated($type, $params), 'accessibility', 'name')
            )
        );
        $accessibility['all'] = $accessibility['blocked'] + $accessibility['locked'] + $accessibility['accessible'];

        return $accessibility;
    }

    public function notice($resource_id, $type, $reason, $subject, $message, array $context = array())
    {
        if (!property_exists($this, "{$type}_definition")) {
            throw new \InvalidArgumentException('The type argument has invalid or unknown value');
        }

        $now = new Datetime();
        $definition = $this->{"{$type}_definition"};
        $primary = $definition['primary'];
        $table = $definition['table'];
        $mask = $definition['mask'];
        $notices_mask = $this->unmask_column('moderation_notices', $mask);
        $activity_mask = $this->unmask_column('moderation_activity', $mask);

        $ref = $this->get_log_reference($type);
        $moderator = $this->get_user_information();
        $context = array_merge(
            array(
                'id'        => $resource_id,
                'type'      => $this->get_type_name($type),
                'reason'    => $reason,
                'moderator' => $moderator,
            ),
            $context
        );
        $data = $this->recordAttributesToDatabaseValues(
            array(
                'moderation_noticed_at' => $now,
                'moderation_notices'    => array(
                    'ref'       => $ref,
                    'date'      => $now->format(DateTime::ATOM),
                    'reason'    => $reason,
                    'subject'   => $subject,
                    'message'   => $message,
                    'moderator' => $moderator,
                ),
                'moderation_activity'   => array(
                    'ref'     => $ref,
                    'step'    => 'notice',
                    'date'    => $now->format(DateTime::ATOM),
                    'message' => $this->interpolate(MESSAGE_NOTICE, $context),
                    'context' => $context,
                ),
            ),
            $this->metadata
        );

        return $this->db->query(
            "UPDATE {$table}
            SET
                {$this->unmask_column('moderation_noticed_at', $mask)} = ?,
                {$notices_mask} = IF(
                    {$notices_mask} IS NULL OR JSON_TYPE({$notices_mask}) != 'ARRAY',
                    JSON_ARRAY(CAST(? AS JSON)),
                    JSON_ARRAY_APPEND({$notices_mask}, '$', CAST(? AS JSON))
                ),
                {$activity_mask} = IF(
                    {$activity_mask} IS NULL OR JSON_TYPE({$activity_mask}) != 'ARRAY',
                    JSON_ARRAY(CAST(? AS JSON)),
                    JSON_ARRAY_APPEND({$activity_mask}, '$', CAST(? AS JSON))
                )
            WHERE {$primary} = ?",
            array(
                $data['moderation_noticed_at'],
                $data['moderation_notices'],
                $data['moderation_notices'],
                $data['moderation_activity'],
                $data['moderation_activity'],
                (int) $resource_id,
            )
        );
    }

    public function moderate($resource_id, $type, array $context = array())
    {
        if (!property_exists($this, "{$type}_definition")) {
            throw new \InvalidArgumentException('The type argument has invalid or unknown value');
        }

        $now = new Datetime();
        $definition = $this->{"{$type}_definition"};
        $primary = $definition['primary'];
        $table = $definition['table'];
        $mask = $definition['mask'];
        $block_mask = $this->unmask_column('moderation_is_blocked', $mask);
        $activity_mask = $this->unmask_column('moderation_activity', $mask);

        $context = array_merge(
            array(
                'id'        => $resource_id,
                'type'      => $this->get_type_name($type),
                'moderator' => $this->get_user_information(),
            ),
            $context
        );
        $data = $this->recordAttributesToDatabaseValues(
            array(
                'moderation_is_approved'  => 1,
                'moderation_approved_at'  => $now,
                'moderation_activity'     => array(
                    'message' => $this->interpolate(MESSAGE_MODERATE, $context),
                    'date'    => $now->format(DateTime::ATOM),
                    'step'    => 'moderate',
                    'context' => $context,
                ),
            ),
            $this->metadata
        );

        return $this->db->query(
            "UPDATE {$table}
            SET
                {$this->unmask_column('moderation_is_approved', $mask)} = ?,
                {$this->unmask_column('moderation_approved_at', $mask)} = ?,
                {$activity_mask} = IF(
                    {$activity_mask} IS NULL OR JSON_TYPE({$activity_mask}) != 'ARRAY',
                    JSON_ARRAY(CAST(? AS JSON)),
                    JSON_ARRAY_APPEND({$activity_mask}, '$', CAST(? AS JSON))
                )
            WHERE {$primary} = ?",
            array(
                $data['moderation_is_approved'],
                $data['moderation_approved_at'],
                $data['moderation_activity'],
                $data['moderation_activity'],
                (int) $resource_id,
            )
        );
    }

    public function immoderate($resource_id, $type, array $context = array())
    {
        if (!property_exists($this, "{$type}_definition")) {
            throw new \InvalidArgumentException('The type argument has invalid or unknown value');
        }

        $now = new Datetime();
        $definition = $this->{"{$type}_definition"};
        $primary = $definition['primary'];
        $table = $definition['table'];
        $mask = $definition['mask'];
        $block_mask = $this->unmask_column('moderation_is_blocked', $mask);
        $activity_mask = $this->unmask_column('moderation_activity', $mask);

        $context = array_merge(
            array(
                'id'        => $resource_id,
                'type'      => $this->get_type_name($type),
                'editor'    => $this->get_user_information(),
            ),
            $context
        );
        $data = $this->recordAttributesToDatabaseValues(
            array(
                'moderation_is_approved'  => 0,
                'moderation_activity'     => array(
                    'step'    => 'edit',
                    'context' => $context,
                    'message' => $this->interpolate(MESSAGE_IMMODERATE, $context),
                    'date'    => $now->format(DateTime::ATOM),
                ),
            ),
            $this->metadata
        );

        return $this->db->query(
            "UPDATE {$table}
            SET
                {$this->unmask_column('moderation_is_approved', $mask)} = ?,
                {$this->unmask_column('moderation_approved_at', $mask)} = ?,
                {$activity_mask} = IF(
                    {$activity_mask} IS NULL OR JSON_TYPE({$activity_mask}) != 'ARRAY',
                    JSON_ARRAY(CAST(? AS JSON)),
                    JSON_ARRAY_APPEND({$activity_mask}, '$', CAST(? AS JSON))
                )
            WHERE {$primary} = ?",
            array(
                $data['moderation_is_approved'],
                null,
                $data['moderation_activity'],
                $data['moderation_activity'],
                (int) $resource_id,
            )
        );
    }

    public function block($resource_id, $type, $reason, $message = null, array $context = array())
    {
        if (!property_exists($this, "{$type}_definition")) {
            throw new \InvalidArgumentException('The type argument has invalid or unknown value');
        }

        $app = tmvc::instance()->controller;
        $now = new Datetime();
        $definition = $this->{"{$type}_definition"};
        $primary = $definition['primary'];
        $table = $definition['table'];
        $mask = $definition['mask'];
        $blocking_mask = $this->unmask_column('moderation_blocking', $mask);
        $activity_mask = $this->unmask_column('moderation_activity', $mask);

        $ref = $this->get_log_reference($type);
        $moderator = $this->get_user_information();
        $context = array_merge(
            array(
                'id'        => $resource_id,
                'type'      => $this->get_type_name($type),
                'reason'    => $reason,
                'moderator' => $moderator,
            ),
            $context
        );
        $data = $this->recordAttributesToDatabaseValues(
            array(
                'moderation_is_blocked'   => 1,
                'moderation_blocked_at'   => $now,
                'moderation_blocking'     => array(
                    'ref'       => $ref,
                    'date'      => $now->format(DateTime::ATOM),
                    'reason'    => $reason,
                    'message'   => $message,
                    'moderator' => $moderator,
                ),
                'moderation_activity'     => array(
                    'ref'     => $ref,
                    'step'    => 'block',
                    'message' => $this->interpolate(MESSAGE_BLOCK, $context),
                    'date'    => $now->format(DateTime::ATOM),
                    'context' => $context,
                ),
            ),
            $this->metadata
        );

        $isUpdated = $this->db->query(
            "UPDATE {$table}
            SET
                {$this->unmask_column('moderation_is_approved', $mask)} = ?,
                {$this->unmask_column('moderation_is_blocked', $mask)} = ?,
                {$this->unmask_column('moderation_approved_at', $mask)} = ?,
                {$this->unmask_column('moderation_blocked_at', $mask)} = ?,
                {$this->unmask_column('moderation_unblocked_at', $mask)} = ?,
                {$blocking_mask} = IF(
                    {$blocking_mask} IS NULL OR JSON_TYPE({$blocking_mask}) != 'ARRAY',
                    JSON_ARRAY(CAST(? AS JSON)),
                    JSON_ARRAY_APPEND({$blocking_mask}, '$', CAST(? AS JSON))
                ),
                {$activity_mask} = IF(
                    {$activity_mask} IS NULL OR JSON_TYPE({$activity_mask}) != 'ARRAY',
                    JSON_ARRAY(CAST(? AS JSON)),
                    JSON_ARRAY_APPEND({$activity_mask}, '$', CAST(? AS JSON))
                )
            WHERE {$primary} = ?",
            array(
                0,
                $data['moderation_is_blocked'],
                null,
                $data['moderation_blocked_at'],
                null,
                $data['moderation_blocking'],
                $data['moderation_blocking'],
                $data['moderation_activity'],
                $data['moderation_activity'],
                (int) $resource_id,
            )
        );

        if ($isUpdated) {
            switch ($type) {
                case TYPE_COMPANY:
                    $this->db->query("UPDATE {$table} SET index_name_temp = index_name, index_name = '' WHERE {$primary} = ?", array($resource_id));
                    $this->elasticsearch->delete('company', $resource_id);

                    break;
                case TYPE_ITEM:
                    $this->elasticsearch->delete('items', $resource_id);

                    break;
            }
        }

        return $isUpdated;
    }

    public function block_list($resource_ids, $type, $reason, $message = null, array $context = array())
    {
        if (!property_exists($this, "{$type}_definition")) {
            throw new \InvalidArgumentException('The type argument has invalid or unknown value');
        }

        if (empty($resource_ids)) {
            return false;
        }

        $now = new Datetime();
        $app = tmvc::instance()->controller;
        $definition = $this->{"{$type}_definition"};
        $primary = $definition['primary'];
        $table = $definition['table'];
        $mask = $definition['mask'];
        $blocking_mask = $this->unmask_column('moderation_blocking', $mask);
        $activity_mask = $this->unmask_column('moderation_activity', $mask);

        $ref = $this->get_log_reference($type);
        $moderator = $this->get_user_information();
        $context = array_merge(
            array(
                'id'        => $resource_id,
                'type'      => $this->get_type_name($type),
                'reason'    => $reason,
                'moderator' => $moderator,
            ),
            $context
        );
        $data = $this->recordAttributesToDatabaseValues(
            array(
                'moderation_is_blocked'   => 1,
                'moderation_blocked_at'   => $now,
                'moderation_blocking'     => array(
                    'ref'       => $ref,
                    'date'      => $now->format(DateTime::ATOM),
                    'reason'    => $reason,
                    'message'   => $message,
                    'moderator' => $moderator,
                ),
                'moderation_activity'     => array(
                    'ref'     => $ref,
                    'message' => $this->interpolate(MESSAGE_BLOCK, $context),
                    'date'    => $now->format(DateTime::ATOM),
                    'step'    => 'block',
                    'context' => $context,
                ),
            ),
            $this->metadata
        );

        $keys = array();
        $keys_placeholder = array();
        foreach ($resource_ids as $key) {
            $keys[] = (int) $key;
            $keys_placeholder[] = '?';
        }
        $keys_placeholder = implode(',', $keys_placeholder);

        $isUpdated = $this->db->query(
            "UPDATE {$table}
            SET
                {$this->unmask_column('moderation_is_approved', $mask)} = ?,
                {$this->unmask_column('moderation_is_blocked', $mask)} = ?,
                {$this->unmask_column('moderation_approved_at', $mask)} = ?,
                {$this->unmask_column('moderation_blocked_at', $mask)} = ?,
                {$this->unmask_column('moderation_unblocked_at', $mask)} = ?,
                {$blocking_mask} = IF(
                    {$blocking_mask} IS NULL OR JSON_TYPE({$blocking_mask}) != 'ARRAY',
                    JSON_ARRAY(CAST(? AS JSON)),
                    JSON_ARRAY_APPEND({$blocking_mask}, '$', CAST(? AS JSON))
                ),
                {$activity_mask} = IF(
                    {$activity_mask} IS NULL OR JSON_TYPE({$activity_mask}) != 'ARRAY',
                    JSON_ARRAY(CAST(? AS JSON)),
                    JSON_ARRAY_APPEND({$activity_mask}, '$', CAST(? AS JSON))
                )
            WHERE {$primary} IN ({$keys_placeholder})",
            array_merge(
                array(
                    0,
                    $data['moderation_is_blocked'],
                    null,
                    $data['moderation_blocked_at'],
                    null,
                    $data['moderation_blocking'],
                    $data['moderation_blocking'],
                    $data['moderation_activity'],
                    $data['moderation_activity'],
                ),
                $keys
            )
        );

        if ($isUpdated) {
            switch ($type) {
                case TYPE_COMPANY:
                    $this->db->query("UPDATE {$table} SET index_name_temp = index_name, index_name = '' WHERE {$primary} IN ({$keys_placeholder})", $keys);
                    $this->elasticsearch->delete('company', implode(',', $keys));

                    break;
                case TYPE_ITEM:
                    $this->elasticsearch->delete('items', implode(',', $keys));

                    break;
            }
        }

        return $isUpdated;
    }

    public function unblock($resource_id, $type, array $context = array())
    {
        if (!property_exists($this, "{$type}_definition")) {
            throw new \InvalidArgumentException('The type argument has invalid or unknown value');
        }

        $app = tmvc::instance()->controller;
        $now = new Datetime();
        $definition = $this->{"{$type}_definition"};
        $primary = $definition['primary'];
        $table = $definition['table'];
        $mask = $definition['mask'];
        $activity_mask = $this->unmask_column('moderation_activity', $mask);

        $context = array_merge(
            array(
                'id'        => $resource_id,
                'type'      => isset($this->types[$type]) ? $this->types[$type] : $type,
                'reason'    => $reason,
                'moderator' => array(
                    'id'       => $app->session->id,
                    'email'    => $app->session->email,
                    'fullname' => trim("{$app->session->fname} {$app->session->lname}"),
                ),
            ),
            $context
        );
        $data = $this->recordAttributesToDatabaseValues(
            array(
                'moderation_is_blocked'   => 0,
                'moderation_blocked_at'   => null,
                'moderation_unblocked_at' => $now,
                'moderation_activity'     => array(
                    'message' => $this->interpolate(MESSAGE_UNBLOCK, $context),
                    'date'    => $now->format(DateTime::ATOM),
                    'step'    => 'unblock',
                    'context' => $context,
                ),
            ),
            $this->metadata
        );

        $isUpdated = $this->db->query(
            "UPDATE {$table}
            SET
                {$this->unmask_column('moderation_is_blocked', $mask)} = ?,
                {$this->unmask_column('moderation_blocked_at', $mask)} = ?,
                {$this->unmask_column('moderation_unblocked_at', $mask)} = ?,
                {$activity_mask} = IF(
                    {$activity_mask} IS NULL OR JSON_TYPE({$activity_mask}) != 'ARRAY',
                    JSON_ARRAY(CAST(? AS JSON)),
                    JSON_ARRAY_APPEND({$activity_mask}, '$', CAST(? AS JSON))
                )
            WHERE {$primary} = ?",
            array(
                $data['moderation_is_blocked'],
                $data['moderation_blocked_at'],
                $data['moderation_unblocked_at'],
                $data['moderation_activity'],
                $data['moderation_activity'],
                (int) $resource_id,
            )
        );

        if ($isUpdated) {
            switch ($type) {
                case TYPE_COMPANY:
                    $this->db->query("UPDATE {$table} SET index_name = index_name_temp, index_name_temp = '' WHERE {$primary} = ?", array($resource_id));
                    $app->load->model('Elasticsearch_Company_Model', 'elasticsearchCompany');
                    $app->elasticsearchCompany->index_company($resource_id);

                    break;
                case TYPE_ITEM:
                    $app->load->model('Elasticsearch_Items_Model', 'elasticsearchItems');
                    $app->elasticsearchItems->index($resource_id);

                    break;
            }
        }

        return $isUpdated;
    }

    public function unblock_list($resource_ids, $type, array $context = array())
    {
        if (!property_exists($this, "{$type}_definition")) {
            throw new \InvalidArgumentException('The type argument has invalid or unknown value');
        }

        $app = tmvc::instance()->controller;
        $now = new Datetime();
        $definition = $this->{"{$type}_definition"};
        $primary = $definition['primary'];
        $table = $definition['table'];
        $mask = $definition['mask'];
        $activity_mask = $this->unmask_column('moderation_activity', $mask);

        $context = array_merge(
            array(
                'id'        => $resource_id,
                'type'      => isset($this->types[$type]) ? $this->types[$type] : $type,
                'reason'    => $reason,
                'moderator' => array(
                    'id'       => $app->session->id,
                    'email'    => $app->session->email,
                    'fullname' => trim("{$app->session->fname} {$app->session->lname}"),
                ),
            ),
            $context
        );
        $data = $this->recordAttributesToDatabaseValues(
            array(
                'moderation_is_blocked'   => 0,
                'moderation_blocked_at'   => null,
                'moderation_unblocked_at' => $now,
                'moderation_activity'     => array(
                    'message' => $this->interpolate(MESSAGE_UNBLOCK, $context),
                    'date'    => $now->format(DateTime::ATOM),
                    'step'    => 'unblock',
                    'context' => $context,
                ),
            ),
            $this->metadata
        );

        $keys = array();
        $keys_placeholder = array();
        foreach ($resource_ids as $key) {
            $keys[] = (int) $key;
            $keys_placeholder[] = '?';
        }
        $keys_placeholder = implode(',', $keys_placeholder);

        $isUpdated = $this->db->query(
            "UPDATE {$table}
            SET
                {$this->unmask_column('moderation_is_blocked', $mask)} = ?,
                {$this->unmask_column('moderation_blocked_at', $mask)} = ?,
                {$this->unmask_column('moderation_unblocked_at', $mask)} = ?,
                {$activity_mask} = IF(
                    {$activity_mask} IS NULL OR JSON_TYPE({$activity_mask}) != 'ARRAY',
                    JSON_ARRAY(CAST(? AS JSON)),
                    JSON_ARRAY_APPEND({$activity_mask}, '$', CAST(? AS JSON))
                )
            WHERE {$primary} IN ({$keys_placeholder})",
            array_merge(
                array(
                    $data['moderation_is_blocked'],
                    $data['moderation_blocked_at'],
                    $data['moderation_unblocked_at'],
                    $data['moderation_activity'],
                    $data['moderation_activity'],
                ),
                $keys
            )
        );

        if ($isUpdated) {
            switch ($type) {
                case TYPE_COMPANY:
                    $this->db->query("UPDATE {$table} SET index_name = index_name_temp, index_name_temp = '' WHERE {$primary} IN ({$keys_placeholder})", $keys);
                    $app->load->model('Elasticsearch_Company_Model', 'elasticsearchCompany');
                    $app->elasticsearchCompany->index_companies($keys);

                    break;
                case TYPE_ITEM:
                    $app->load->model('Elasticsearch_Items_Model', 'elasticsearchItems');
                    $app->elasticsearchItems->index($keys);

                    break;
            }
        }

        return $isUpdated;
    }

    private function unmask_column($column, array $mask = array())
    {
        if (empty($mask) || !isset($mask[$column])) {
            return $column;
        }

        return $mask[$column];
    }

    private function transform_resource(array $raw, $type)
    {
        $activity = null !== $raw['moderation_activity'] ? json_decode($raw['moderation_activity'], true) : array();
        if (json_last_error()) {
            $activity = array();
        }

        $blocking = null !== $raw['moderation_blocking'] ? json_decode($raw['moderation_blocking'], true) : array();
        if (json_last_error()) {
            $blocking = array();
        }
        $notices = null !== $raw['moderation_notices'] ? json_decode($raw['moderation_notices'], true) : array();
        if (json_last_error()) {
            $notices = array();
        }

        $projection = array();
        switch ($type) {
            case TYPE_COMPANY:
                $projection = array('parent' => (int) $raw['parent']);

                break;
            default:
                break;
        }

        $author = array();
        if (isset($raw['author'])) {
            $author = array('author' => (int) $raw['author']);
        }

        return array_replace(
            $raw,
            array(
                'id'                      => (int) $raw['id'],
                'title'                   => $raw['title'],
                'created_at'              => !empty($raw['created_at']) ? new \DateTime($raw['created_at']) : null,
                'updated_at'              => !empty($raw['updated_at']) ? new \DateTime($raw['updated_at']) : null,
                'moderation_is_approved'  => (bool) $raw['moderation_is_approved'],
                'moderation_is_blocked'   => (int) $raw['moderation_is_blocked'],
                'moderation_approved_at'  => !empty($raw['moderation_approved_at']) ? new \DateTime($raw['moderation_approved_at']) : null,
                'moderation_blocked_at'   => !empty($raw['moderation_blocked_at']) ? new \DateTime($raw['moderation_blocked_at']) : null,
                'moderation_unblocked_at' => !empty($raw['moderation_unblocked_at']) ? new \DateTime($raw['moderation_unblocked_at']) : null,
                'moderation_noticed_at'   => !empty($raw['moderation_noticed_at']) ? new \DateTime($raw['moderation_noticed_at']) : null,
                'moderation_activity'     => $activity,
                'moderation_blocking'     => $blocking,
                'moderation_notices'      => $notices,
            ),
            $projection,
            $author
        );
    }

    private function with_author($type, $table, $meta, $relation)
    {
        if (
            !$this->has_author($type) ||
            null === ($author_binding = $this->get_author_binding($type, $table, $meta))
        ) {
            return;
        }

        $this->db->join(...$author_binding);
        if (is_callable($relation)) {
            $relation($this->db, $this);
        }
    }

    private function with_group($relation)
    {
        $this->db->join('user_groups', 'users.user_group = user_groups.idgroup', 'left');
        if (is_callable($relation)) {
            $relation($this->db, $this);
        }
    }

    private function get_author_binding($type, $table, $meta)
    {
        if (empty($meta['author'])) {
            return null;
        }

        switch ($type) {
            case TYPE_B2B:
            case TYPE_ITEM:
            case TYPE_COMPANY:
                return array('users', "{$table}.{$meta['author']} = users.idu", 'left');
            default:
                return null;
        }
    }

    private function has_author($type)
    {
        return in_array($type, array(
            TYPE_B2B,
            TYPE_COMPANY,
            TYPE_ITEM,
        ));
    }

    private function get_user_information()
    {
        $app = tmvc::instance()->controller;

        return array(
            'id'       => $app->session->id,
            'email'    => $app->session->email,
            'fullname' => trim("{$app->session->fname} {$app->session->lname}"),
        );
    }

    private function get_type_name($type)
    {
        return isset($this->types[$type]) ? $this->types[$type] : $type;
    }

    private function get_log_reference($type)
    {
        try {
            return  Uuid::uuid4()->toString();
        } catch (UnableToBuildUuidException | RuntimeException $e) {
            // The lame way
            return uniqid(sprintf('%s-%s-', md5("moderate-{$type}"), dechex(microtime(1))), true);
        }
    }
}
