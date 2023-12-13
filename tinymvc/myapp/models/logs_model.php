<?php

/**
 * Logs model class.
 *
 * @author Anton Zencenco
 */
class Logs_Model extends TinyMVC_Model
{
    /**
     * PDO instance.
     *
     * @var null|\TinyMVC_PDO
     */
    public $db;

    /**
     * Pointer to application.
     *
     * @var \TinyMVC_Controller
     */
    protected $app;

    /**
     * Name of the table which contains the pages records.
     *
     * @var string
     */
    protected $logs_table = 'logs';

    /**
     * List of columns in logs table which is later used on create or update.
     * Each record must be an array that have filled keys:
     *  - name: is a name of the column
     *  - fillable: indicates that column is fillable in NORMAL mode (columns with this flag set to `false` are fillable in FORCE mode).
     *  - datetime: flag which indicates that field has datetime value.
     *
     * @var array
     */
    protected $logs_columns = array(
        array('name' => 'id_log', 'fillable' => false),
        array('name' => 'log_date', 'fillable' => true, 'datetime' => true),
        array('name' => 'log_level', 'fillable' => true),
        array('name' => 'log_message', 'fillable' => true),
        array('name' => 'log_context', 'fillable' => true),
    );

    /**
     * Pages model constructor.
     */
    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        // Saving pointer to the controller instance
        $this->app = tmvc::instance()->controller;
    }

    public function create(array $log)
    {
        return $this->db->insert(
            $this->logs_table,
            $this->prepare_data(
                $log,
                $this->logs_columns
            )
        );
    }

    /**
     * Add a prefix to column that match provided relation  name.
     *
     * @param string $relation is a name of the relation
     * @param string $column   is a name of the column
     *
     * @return string
     */
    public function prefix_column($relation, $column)
    {
        switch ($relation) {
            case 'log':
                return "{$this->logs_table}.{$column}";
            default:
                $property_name = "{$relation}_table";
                if (isset($this->{$property_name}) && is_string($this->{$property_name})) {
                    $relation = $this->{$property_name};
                }

                return "{$relation}.{$column}";
        }
    }

    /**
     * Transforms provided {@link $columns} variable into a valid string.
     *
     * @param null|array|string $columns comma-separated string or array of column names
     *
     * @return string
     */
    private function resolve_columns($columns)
    {
        if (null === $columns || empty($columns)) {
            return '*';
        }

        if (!(is_string($columns) || is_array($columns))) {
            $current_type = gettype($columns);

            throw new \InvalidArgumentException("Invalid argument for column projection provided - string or array expected, got {$current_type}");
        }

        return is_string($columns) ? $columns : implode(', ', $columns);
    }

    /**
     * Prepares raw page data be used on create or update.
     *
     * @param array|\ArrayObject|\IteratorAggregate|\stdClass $data    a set of page data
     * @param bool                                            $force   a flag that indicates if force mode is enabled/disabled
     * @param mixed                                           $columns
     *
     * @return array
     */
    private function prepare_data($data, $columns, $force = false)
    {
        $allowed_columns = $columns;
        if (!$force) {
            $allowed_columns = array_filter($allowed_columns, function ($column) {
                return isset($column['fillable']) && $column['fillable'];
            });
        }
        $allowed_columns_list = array_column($allowed_columns, 'name');
        $processed_data = array_intersect_key($this->morph_to_array($data), array_flip($allowed_columns_list));

        // Ensure that all datetime fields
        $date_checks = array_filter($allowed_columns, function ($column) {
            return isset($column['datetime']) && $column['datetime'];
        });
        if (!empty($date_checks)) {
            foreach ($date_checks as $column) {
                $name = $column['name'];
                if (isset($processed_data[$name]) && !is_string($processed_data[$name])) {
                    $processed_data[$name] = $this->morph_to_datetime($processed_data[$name]);
                }
            }
        }

        if(isset($processed_data['log_context'])) {
            $processed_data['log_context'] = json_encode($processed_data['log_context'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return $processed_data;
    }

    /**
     * Morphing a value to array.
     *
     * @param array|\ArrayObject|\IteratorAggregate|mixed|\stdClass $raw_array a set or arrayable data
     *
     * @return array
     */
    private function morph_to_array($raw_array)
    {
        if (is_array($raw_array)) {
            return $raw_array;
        }
        if (is_object($raw_array) && method_exists($raw_array, 'toArray')) {
            return $raw_array->toArray();
        }
        if ($raw_array instanceof \ArrayObject) {
            return $raw_array->getArrayCopy();
        }

        return  $raw_array instanceof \IteratorAggregate ? iterator_to_array($raw_array) : (array) $raw_array;
    }

    /**
     * Morphs datetime value into DB compatible form
     * Returns null if cannot transform value or on failure.
     *
     * @param \DateTime|int $datetime
     *
     * @return null|string
     */
    private function morph_to_datetime($datetime)
    {
        if ($datetime instanceof \DateTime) {
            return $datetime->format('Y-m-d H:i:s');
        }

        if ($datetime instanceof \DateTimeImmutable) {
            return $datetime->format('Y-m-d H:i:s');
        }

        if (is_int($datetime)) {
            try {
                $morphed_date = new \DateTime($datetime);
            } catch (\Exception $exception) {
                return null;
            }

            return $morphed_date->format('Y-m-d H:i:s');
        }

        return null;
    }
}
