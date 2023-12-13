<?php

/**
 * Pages model class.
 *
 * @author Anton Zencenco
 */
class Pages_Model extends TinyMVC_Model
{
    /**
     * Name of the table which contains the pages records.
     *
     * @var string
     */
    protected $pages_table = 'pages';

    /**
     * Name of the table which contains page-modules relation records.
     *
     * @var string
     */
    protected $modules_relation_table = 'page_modules_relation';

    /**
     * Name of the table which contains modules records.
     *
     * @var string
     */
    protected $modules_table = 'ep_modules';

    /**
     * Name of the table
     *
     * @var string
     */
    protected $translations_keys_pages_relation_table = 'translations_keys_pages_relation';

    /**
     * Name of the table
     *
     * @var string
     */
    protected $translations_files_table = 'translations_files';

    /**
     * Name of the table which contains search log records.
     *
     * @var string
     */
    protected $search_log_table = 'search_log';

    /**
     * List of columns in pages table which is later used on create or update.
     * Each record must be an array that have filled keys:
     *  - name: is a name of the column
     *  - fillable: indicates that column is fillable in NORMAL mode (columns with this flag set to `false` are fillable in FORCE mode).
     *  - datetime: flag which indicates that field has datetime value.
     *
     * @var array
     */
    protected $pages_columns = array(
        array('name' => 'id_page', 'fillable' => false),
        array('name' => 'page_name', 'fillable' => true),
        array('name' => 'page_hash', 'fillable' => true),
        array('name' => 'page_action', 'fillable' => true),
        array('name' => 'page_controller', 'fillable' => true),
        array('name' => 'is_public', 'fillable' => true),
        array('name' => 'page_view_files', 'fillable' => true),
        array('name' => 'page_description', 'fillable' => true),
        array('name' => 'page_url_template', 'fillable' => true),
        array('name' => 'page_url_pattern', 'fillable' => false),
        array('name' => 'page_created_at', 'fillable' => false, 'datetime' => true),
        array('name' => 'page_updated_at', 'fillable' => false, 'datetime' => true),
    );

    public function getPagesTable(): string
    {
        return $this->pages_table;
    }

    public function getPagesTablePrimaryKey(): string
    {
        return "id_page";
    }

    /**
     * Checks if page record with provided ID exists in table.
     *
     * @param int|string $id record ID
     *
     * @return bool
     */
    public function is_page_exists($id)
    {
        $this->db->select('COUNT(*) as is_exsits');
        $this->db->from($this->pages_table);
        $this->db->where('id_page = ?', $id);
        if (!$this->db->query()) {
            return false;
        }

        return filter_var(((object) $this->db->getQueryResult()->fetchAssociative())->is_exsits, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Returns one page record that match provided ID
     * Using {@link $columns} one can return a projection of the record.
     *
     * @param int|string        $id      record ID
     * @param null|array|string $columns comma-separated string or array of column names
     *
     * @return null|array
     */
    public function find_page($id, $columns = null)
    {
        $this->db->select($this->resolve_columns($columns));
        $this->db->from($this->pages_table);
        $this->db->where("id_page = ?", $id);
        if (!$this->db->query()) {
            return null;
        }
        $data = $this->db->getQueryResult()->fetchAssociative();

        return $data ? $data : null;
    }

    public function find_page_by_hash($hash, $columns = null)
    {
        $this->db->select($this->resolve_columns($columns));
        $this->db->from($this->pages_table);
        $this->db->where("page_hash = ?", $hash);
        if (!$this->db->query()) {
            return null;
        }

        return $this->db->getQueryResult()->fetchAssociative() ?: null;
    }

    /**
     * Get the first page record that match the condition
     * Using {@link $columns} one can return a projection of the record.
     *
     * @param array             $conditions an array of conditions that filters the records
     * @param null|array|string $columns    comma-separated string or array of column names
     *
     * @return null|array
     */
    public function get_first_page(array $conditions = array(), $columns = null, array $with = array())
    {
        $this->resolve_column_ambiguity($columns, 'id_page', $this->pages_table);

        extract($with, EXTR_PREFIX_ALL, 'with');
        extract($conditions, EXTR_PREFIX_ALL, 'condition');

        $this->db->select($this->resolve_columns($columns));
        $this->db->from($this->pages_table);

        // Resolve joins
        if (!empty($with)) {
            // Here be dragons
        }

        // Resolve conditions
        if (!empty($conditions)) {
            if (isset($condition_page)) {
                $this->db->where("{$this->pages_table}.id_page = ?", $condition_page);
            }

            if (isset($condition_controller)) {
                $this->db->where("{$this->pages_table}.page_controller = ?", $condition_controller);
            }

            if (isset($condition_action)) {
                $this->db->where("{$this->pages_table}.page_action = ?", $condition_action);
            }

            if (isset($condition_is_public)) {
                $this->db->where("{$this->pages_table}.is_public = ?", $condition_is_public);
            }
        }

        $this->db->limit(1);
        if (!$this->db->query()) {
            return null;
        }

        return $this->db->getQueryResult()->fetchAllAssociative() ?: null;
    }

    /**
     * Returns a list of pages which correspond a provided set of parameters.
     *
     * @param array $params
     */
    public function get_pages(array $params = array())
    {
        $columns = null;
        $conditions = array();
        $with = array();
        $order = array();
        $group = array();
        $limit = null;
        $skip = null;

        extract($params);
        extract($with, EXTR_PREFIX_ALL, 'with');
        extract($conditions, EXTR_PREFIX_ALL, 'condition');

        $this->resolve_column_ambiguity($columns, 'id_page', $this->pages_table);

        $this->db->select($this->resolve_columns($columns));
        $this->db->from($this->pages_table);

        // Resolve joins
        if (!empty($with)) {
            if (isset($with_modules) && $with_modules) {
                $this->db->join($this->modules_relation_table, "{$this->modules_relation_table}.id_page = {$this->pages_table}.id_page", 'left');
                $this->db->join($this->modules_table, "{$this->modules_table}.id_module = {$this->modules_relation_table}.id_module", 'left');
            }

            if (isset($with_search_log) && $with_search_log) {
                $this->db->join($this->search_log_table, "{$this->search_log_table}.page = {$this->pages_table}.id_page");
                $this->db->groupby("{$this->pages_table}.id_page");
            }
        }

        // Resolve conditions
        if (!empty($conditions)) {
            if (isset($condition_page)) {
                $this->db->where("{$this->pages_table}.id_page = ?", $condition_page);
            }
            if (isset($condition_page_in) && !empty($condition_page_in)) {
                $this->db->in("{$this->pages_table}.id_page", $condition_page_in);
            }
            if (isset($condition_module) && !empty($condition_module)) {
                $this->db->where_raw("{$this->pages_table}.id_page IN (SELECT id_page FROM page_modules_relation WHERE id_module = ?) ", [$condition_module]);
            }
            if (isset($condition_url)) {
                $this->db->where("? REGEXP {$this->pages_table}.page_url_pattern = 1", $condition_url);
            }
            if (isset($condition_created_from)) {
                $this->db->where('page_created_at >= ?', $condition_created_from);
            }
            if (isset($condition_created_to)) {
                $this->db->where('page_created_at <= ?', $condition_created_to);
            }
            if (isset($condition_updated_from)) {
                $this->db->where('page_updated_at >= ?', $condition_updated_from);
            }
            if (isset($condition_updated_to)) {
                $this->db->where('page_updated_at <= ?', $condition_updated_to);
            }
            if (isset($condition_ready_for_translation)) {
                $this->db->where('is_ready_for_translation = ?', $condition_ready_for_translation);
            }
            if (isset($condition_title)) {
                $this->db->where_raw('page_name LIKE ?', '%' . $condition_title . '%');
            }
            if (isset($condition_controller)) {
                $this->db->where("{$this->pages_table}.page_controller = ?", $condition_controller);
            }
            if (isset($condition_action)) {
                $this->db->where("{$this->pages_table}.page_action = ?", $condition_action);
            }
            if (isset($condition_is_public)) {
                $this->db->where("{$this->pages_table}.is_public = ?", $condition_is_public);
            }
        }

        // Resolve group by
        foreach ($group as $column) {
            $this->db->groupby($column);
        }

        // Resolve order by
        foreach ($order as $column => $direction) {
            if (!empty($direction) && is_string($direction)) {
                $this->db->orderby("{$column} {$direction}");
            } else {
                $this->db->orderby($column);
            }
        }

        // Resolve limit
        if (null !== $limit) {
            if (null !== $skip) {
                $this->db->limit($limit, $skip);
            } else {
                $this->db->limit($limit);
            }
        }

        // Fetch data
        if (!$this->db->query()) {
            return array();
        }
        return $this->db->getQueryResult()->fetchAllAssociative() ?: [];
    }

    public function get_updated_pages($updated_from = ''){
        if (empty($updated_from)) {
            return false;
        }

        $this->db->select($this->pages_table . '.*');
        $this->db->from($this->pages_table);
        $this->db->join($this->translations_keys_pages_relation_table, "{$this->pages_table}.id_page = {$this->translations_keys_pages_relation_table}.id_page", 'left');
        $this->db->join($this->translations_files_table, "{$this->translations_keys_pages_relation_table}.id_key = {$this->translations_files_table}.id_key", 'left');
        $this->db->where_raw("{$this->pages_table}.is_ready_for_translation = 1 AND ({$this->translations_files_table}.translation_text_updated_at > ? OR {$this->translations_keys_pages_relation_table}.create_date > ?)", array($updated_from, $updated_from));

        if (!$this->db->query()) {
            return array();
        }

        return $this->db->getQueryResult()->fetchAllAssociative() ?: [];
    }

    public function get_page_modules($page, $columns = null, array $with = array())
    {
        $this->resolve_column_ambiguity($columns, 'id_module', $this->modules_relation_table);
        $this->resolve_column_ambiguity($columns, 'id_page', $this->modules_relation_table);

        extract($with, EXTR_PREFIX_ALL, 'with');

        $this->db->select($this->resolve_columns($columns));
        $this->db->from($this->modules_relation_table);
        $this->db->where("{$this->modules_relation_table}.id_page = ?", (int) $page);

        // Resolve joins
        if (!empty($with)) {
            if (isset($with_pages) && $with_pages) {
                $this->db->join($this->pages_table, "{$this->pages_table}.id_page = {$this->modules_relation_table}.id_page", 'left');
            }
            if (isset($with_modules) && $with_modules) {
                $this->db->join($this->modules_table, "{$this->modules_table}.id_module = {$this->modules_relation_table}.id_module", 'left');
            }
        }

        // Fetch data
        if (!$this->db->query()) {
            return [];
        }

        return $this->db->getQueryResult()->fetchAllAssociative() ?: [];
    }

    public function get_pages_modules(array $pages, $columns = null, array $with = array())
    {
        if (empty($pages)) {
            return [];
        }

        $this->resolve_column_ambiguity($columns, 'id_module', $this->modules_relation_table);
        $this->resolve_column_ambiguity($columns, 'id_page', $this->modules_relation_table);

        extract($with, EXTR_PREFIX_ALL, 'with');

        $pages_list = array_filter(
            array_map(
                function ($page) { return (int) $page; },
                $pages
            )
        );
        $this->db->select($this->resolve_columns($columns));
        $this->db->from($this->modules_relation_table);
        $this->db->in("{$this->modules_relation_table}.id_page", $pages_list);

        // Resolve joins
        if (!empty($with)) {
            if (isset($with_pages) && $with_pages) {
                $this->db->join($this->pages_table, "{$this->pages_table}.id_page = {$this->modules_relation_table}.id_page", 'left');
            }
            if (isset($with_modules) && $with_modules) {
                $this->db->join($this->modules_table, "{$this->modules_table}.id_module = {$this->modules_relation_table}.id_module", 'left');
            }
        }

        // Fetch data
        if (!$this->db->query()) {
            return array();
        }

        return $this->db->getQueryResult()->fetchAllAssociative() ?: [];
    }

    /**
     * Counts pages which correspond a provided set of parameters.
     *
     * @param array $params
     */
    public function count_pages(array $params = array())
    {
        $conditions = array();
        $group = array();
        $with = array();

        extract($params);
        extract($with, EXTR_PREFIX_ALL, 'with');
        extract($conditions, EXTR_PREFIX_ALL, 'condition');

        $this->db->select('COUNT(*) as counter');
        $this->db->from($this->pages_table);

        // Resolve joins
        if (!empty($with)) {
            if (isset($with_modules) && $with_modules) {
                $this->db->join($this->modules_relation_table, "{$this->modules_relation_table}.id_page = {$this->pages_table}.id_page", 'left');
                $this->db->join($this->modules_table, "{$this->modules_table}.id_module = {$this->modules_relation_table}.id_module", 'left');
            }
        }

        // Resolve conditions
        if (!empty($conditions)) {
            if (isset($condition_page)) {
                $this->db->where("{$this->pages_table}.id_page = ?", $condition_page);
            }
            if (isset($condition_page_in)) {
                $this->db->in("{$this->pages_table}.id_page", $condition_page_in);
            }
            if (isset($condition_module) && !empty($condition_module)) {
                $this->db->where_raw("{$this->pages_table}.id_page IN (SELECT id_page FROM page_modules_relation WHERE id_module = ?) ", [$condition_module]);
            }
            if (isset($condition_url)) {
                $this->db->where("? REGEXP {$this->pages_table}.page_url_pattern = 1", $condition_url);
            }
            if (isset($condition_created_from)) {
                $this->db->where('page_created_at >= ?', $condition_created_from);
            }
            if (isset($condition_created_to)) {
                $this->db->where('page_created_at <= ?', $condition_created_to);
            }
            if (isset($condition_updated_from)) {
                $this->db->where('page_updated_at >= ?', $condition_updated_from);
            }
            if (isset($condition_updated_to)) {
                $this->db->where('page_updated_at <= ?', $condition_updated_to);
            }
            if (isset($condition_ready_for_translation)) {
                $this->db->where('is_ready_for_translation = ?', $condition_ready_for_translation);
            }
            if (isset($condition_title)) {
                $this->db->where_raw('page_name LIKE ?', '%' . $condition_title . '%');
            }
            if (isset($condition_controller)) {
                $this->db->where("{$this->pages_table}.page_controller = ?", $condition_controller);
            }
            if (isset($condition_action)) {
                $this->db->where("{$this->pages_table}.page_action = ?", $condition_action);
            }
            if (isset($condition_is_public)) {
                $this->db->where("{$this->pages_table}.is_public = ?", $condition_is_public);
            }
        }

        // Resolve group by
        foreach ($group as $column) {
            $this->db->groupby($column);
        }

        // Fetch data
        if (!$this->db->query()) {
            return 0;
        }

        return (int) ((object) $this->db->getQueryResult()->fetchAssociative())->counter;
    }

    /**
     * Create one page record with provided data.
     * By deafult only columns activated (value is set to `true`) in {@link self::$pages_columns} will be used.
     * When force mode is activated all columns specified in {@link self::$pages_columns} will be used.
     *
     * @param array|\ArrayObject|\IteratorAggregate|\stdClass $data  a set of page data
     * @param bool                                            $force a flag that indicates if force mode is enabled/disabled
     *
     * @return bool|int
     */
    public function create_page($data, $force = false)
    {
        return $this->db->insert(
            $this->pages_table,
            $this->prepare_data(
                $data,
                $this->pages_columns,
                $force
            )
        );
    }

    /**
     * Creates relationship MANY-TO-MANY between page and modules.
     *
     * @param int|string                $id        is a page record ID
     * @param int|int[]|string|string[] $relations represents module(s) ID(s) which page is belongs to
     *
     * @return bool
     */
    public function create_relations($id, $relations)
    {
        if (empty($relations)) {
            return false;
        }

        $insert_data = array();
        $relations = arrayable_to_array($relations);
        foreach ($relations as $relation) {
            $insert_data[] = array(
                'id_page'   => $id,
                'id_module' => (int) $relation,
            );
        }
        $insert_data = array_filter($insert_data);

        return $this->db->insert_batch($this->modules_relation_table, $insert_data);
    }

    /**
     * Removes relationship between specified page and modules.
     *
     * @param int|string                     $id        is a page record ID
     * @param null|int|int[]|string|string[] $relations represent module(s) which has relationship which page
     *
     * @return bool
     */
    public function remove_relations($id, $relations = null)
    {
        $this->db->where('id_page = ?', (int) $id);
        $relations = null !== $relations ? arrayable_to_array($relations) : $relations;
        if (!empty($relations)) {
            $this->db->in('id_module', $relations);
        }

        return $this->db->delete($this->modules_relation_table);
    }

    /**
     * Replaces pages relationship with a provided one.
     *
     * @param int|string                $id        is a page record ID
     * @param int|int[]|string|string[] $relations represents new module(s) ID(s) which page is belongs to
     */
    public function replace_relations($id, $relations)
    {
        $removed = $this->remove_relations($id);
        if (empty($relations)) {
            return $removed;
        }

        return $removed && $this->create_relations($id, $relations);
    }

    /**
     * Update one specific page record with provided data.
     * By deafult only columns activated (value is set to `true`) in {@link self::$pages_columns} will be used.
     * When force mode is activated all columns specified in {@link self::$pages_columns} will be used.
     *
     * @param int                                             $id    is a record ID
     * @param array|\ArrayObject|\IteratorAggregate|\stdClass $data  a set of page data
     * @param bool                                            $force a flag that indicates if force mode is enabled/disabled
     *
     * @return bool
     */
    public function update_page($id, $data, $force = false)
    {
        $this->db->where('id_page = ?', $id);

        return $this->db->update(
            $this->pages_table,
            $this->prepare_data(
                $data,
                $this->pages_columns,
                $force
            )
        );
    }

    public function simple_update($id, $data)
    {
        $this->db->where('id_page = ?', $id);

        return $this->db->update($this->pages_table, $data);
    }

    /**
     * Remove one page by provided ID.
     *
     * @param int $id is a page record ID
     *
     * @return bool
     */
    public function remove_page($id)
    {
        $this->db->where('id_page', (int) $id);

        return $this->db->delete($this->pages_table);
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
            case 'page':
                return "{$this->pages_table}.{$column}";
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

            throw new \InvalidArgumentException(
                "Invalid argument for column projection provided - string or array expected, got {$current_type}"
            );
        }

        return is_string($columns) ? $columns : implode(', ', $columns);
    }

    /**
     * Prepends column in the FROM caluse with preferred table name to escape columns name ambiguity in the queries.
     *
     * @param null|array|string $from_clause     is a non-escaped FROM clause
     * @param string            $column          a column that must be prefixed
     * @param string            $preferred_table table name which will be used as prefix
     */
    private function resolve_column_ambiguity(&$from_clause, $column, $preferred_table)
    {
        if (empty($from_clause)) {
            return;
        }

        if (
            is_string($from_clause) &&
            false !== strpos($from_clause, $column)
        ) {
            $from_clause = preg_replace("/([^\.]({$column})\b)/", "{$preferred_table}.$2", $from_clause);
        }

        if (is_array($from_clause) && false !== array_search($column, $from_clause)) {
            $keys = array_keys($from_clause, $column);
            foreach ($keys as $key) {
                if (!isset($from_clause[$key])) {
                    continue;
                }

                if ($column === $from_clause[$key]) {
                    $from_clause[$key] = "{$preferred_table}.{$column}";

                    continue;
                }

                if (is_string($from_clause[$key]) && false !== strpos($from_clause[$key], $column)) {
                    $from_clause[$key] = preg_replace("/([^\.]({$column})\b)/", "{$preferred_table}.$2", $from_clause[$key]);
                }
            }
        }
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

        if(isset($data['page_url_template']) && !empty($data['page_url_template'])) {
            $replacements = array(
                "__ANY__"      => "(.+)",
                "__ANY?__"     => "(.+)?",
                "__NUMBER__"   => "([0-9]+)",
                "__NUMBER?__"  => "([0-9]+)?",
                "__SEGMENT__"  => "([^\/]+)",
                "__SEGMENT?__" => "([^\/]+)?",
            );

            $pattern = trim(str_replace(array_keys($replacements), $replacements, $data['page_url_template']));
            $pattern_length = strlen($pattern);
            if('/' === $pattern[$pattern_length - 1]) {
                $pattern = "{$pattern}?";
            }
            $processed_data['page_url_pattern'] = "^{$pattern}$";
        } else {
            $processed_data['page_url_pattern'] = "";
        }

        if(isset($data['page_view_files']) && !is_string($data['page_view_files'])) {
            $processed_data['page_view_files'] = json_encode($data['page_view_files'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
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
