<?php

declare(strict_types=1);

use App\Common\Database\BaseModel;

/**
 * Seller library Model.
 *
 * @author Cravciuc Andrei
 */
class Seller_Library_Model extends BaseModel
{
    /**
     * Name of the library table.
     *
     * @var string
     */
    private $library_table = 'seller_library';

    /**
     * Name of the library categories table.
     *
     * @var string
     */
    private $library_categories_table = 'seller_library_categories';

    /**
     * Name of the companies table.
     *
     * @var string
     */
    private $companies_table = 'company_base';

    public function create_document($data)
    {
        return $this->db->insert($this->library_table, $data);
    }

    public function get_document($document_id)
    {
        $this->db->select('sd.*, slc.category_title');
        $this->db->from("{$this->library_table} sd");
        $this->db->join("{$this->library_categories_table} slc", 'sd.id_category = slc.id_category', 'left');
        $this->db->where('id_file = ?', (int) $document_id);

        return $this->db->query_one();
    }

    public function is_my_document($document_id, $company_id)
    {
        $this->db->select('COUNT(*) as AGGREGATE');
        $this->db->from($this->library_table);
        $this->db->where('id_file = ?', (int) $document_id);
        $this->db->where('id_company = ?', (int) $company_id);

        return (bool) (int) ($this->db->query_one()['AGGREGATE'] ?? false);
    }

    public function update_document($document_id, $data)
    {
        $this->db->where('id_file', $document_id);

        return $this->db->update($this->library_table, $data);
    }

    public function delete_document($document_id)
    {
        $document_id = getArrayFromString($document_id);

        $this->db->in('id_file', array_map('intval', $document_id));
        return $this->db->delete($this->library_table);
    }

    public function get_seller_documents(array $conditions = array())
    {
        $page = 1;
        $per_p = 20;
        $order_by = array();
        $use_limit = true;
        $columns = array('sd.*', 'slc.category_title');

        extract($conditions);

        $columns = $this->prepareColumns($columns);
        switch ($sort_by) {
            case 'title_asc': $order_by[] = 'sd.title_file ASC';

            break;
            case 'title_desc': $order_by[] = 'sd.title_file DESC';

            break;
            case 'date_asc': $order_by[] = 'sd.add_date_file ASC';

            break;
            case 'date_desc': $order_by[] = 'sd.add_date_file DESC';

            break;
            case 'ext_asc': $order_by[] = 'sd.extension_file ASC';

            break;
            case 'ext_desc': $order_by[] = 'sd.extension_file DESC';

            break;
            case 'rand': $order_by[] = 'RAND()';

            break;
            default: $order_by[] = 'sd.id_file DESC';

            break;
        }

        $this->db->select($columns);
        $this->db->from("{$this->library_table} sd");
        $this->db->join("{$this->library_categories_table} slc", 'sd.id_category = slc.id_category', 'left');

        if (isset($id_seller)) {
            $this->db->where('sd.id_seller = ?', (int) $id_seller);
        }

        if (isset($id_company)) {
            $this->db->where('sd.id_company = ?', (int) $id_company);
        }

        if (!empty($id_document)) {
            $id_document = getArrayFromString($id_document);
            $this->db->in('sd.id_file', array_map('intval', $id_document));
        }

        if (!empty($not_document)) {
            $not_document = getArrayFromString($not_document);
            $this->db->where_raw("sd.id_file NOT IN (" . implode(',', array_fill(0, count($not_document), '?')) . ")", $not_document);
        }

        if (isset($type)) {
            $this->db->where('sd.type_file = ?', $type);
        }

        if (isset($keywords)) {
            if (str_word_count_utf8($keywords) > 1) {
                $escaped_search_string = $this->db->getConnection()->quote(trim($keywords));
                $search_parts = preg_split("/\b/", trim($escaped_search_string, "'"));
                $search_parts = array_map('trim', $search_parts);
                $search_parts = array_filter($search_parts);
                if (!empty($search_parts)) {
                    // Drop array keys
                    $search_parts = array_values($search_parts);
                    // Unite words - each consecutive word have lesser contribution
                    $search_condition = implode('* <', $search_parts);
                    $search_condition = "{$search_condition}*";
                    $this->db->where_raw('MATCH (sd.title_file, sd.description_file) AGAINST (? IN BOOLEAN MODE)', $search_condition);
                    $this->db->select($columns = "{$columns}, MATCH (sd.title_file, sd.description_file) AGAINST ('{$search_condition}' IN BOOLEAN MODE) AS REL");
                    $order_by[] = 'REL DESC';
                }
            } else {
                $this->db->where_raw('(sd.title_file LIKE ? || sd.description_file LIKE ?)', ["%{$keywords}%", "%{$keywords}%"]);
            }
        }

        if (!empty($order_by)) {
            $this->db->orderby(implode(', ', $order_by));
        }

        if ($use_limit) {
            $per_p = (int) $per_p !== 0 ? $per_p : 20;

            $page = $page > 0 ? $page : 1;
            $limit = $per_p;
            $skip = ($page - 1) * $per_p;
            if ($skip < 0) {
                $skip = 0;
            }

            $this->db->limit($limit, $skip);
        }

        return $this->db->query_all();
    }

    public function count_seller_documents(array $conditions = array())
    {
        extract($conditions);

        $columns = $this->prepareColumns($columns);

        $this->db->select('COUNT(*) as AGGREGATE');
        $this->db->from("{$this->library_table} sd");
        $this->db->join("{$this->library_categories_table} slc", 'sd.id_category = slc.id_category', 'left');

        if (isset($id_seller)) {
            $this->db->where('sd.id_seller = ?', (int) $id_seller);
        }

        if (isset($id_company)) {
            $this->db->where('sd.id_company = ?', (int) $id_company);
        }

        if (!empty($id_document)) {
            $id_document = getArrayFromString($id_document);
            $this->db->in('sd.id_file', array_map('intval', $id_document));
        }

        if (!empty($not_document)) {
            $not_document = getArrayFromString($not_document);
            $this->db->where_raw("sd.id_file NOT IN (" . implode(',', array_fill(0, count($not_document), '?')) . ")", $not_document);
        }

        if (isset($type)) {
            $this->db->where('sd.type_file = ?', $type);
        }

        if (isset($keywords)) {
            if (str_word_count_utf8($keywords) > 1) {
                $escaped_search_string = $this->db->getConnection()->quote(trim($keywords));
                $search_parts = preg_split("/\b/", trim($escaped_search_string, "'"));
                $search_parts = array_map('trim', $search_parts);
                $search_parts = array_filter($search_parts);
                if (!empty($search_parts)) {
                    // Drop array keys
                    $search_parts = array_values($search_parts);
                    // Unite words - each consecutive word have lesser contribution
                    $search_condition = implode('* <', $search_parts);
                    $search_condition = "{$search_condition}*";
                    $this->db->where_raw('MATCH (sd.title_file, sd.description_file) AGAINST (? IN BOOLEAN MODE)', $search_condition);
                    $this->db->select($columns = "{$columns}, MATCH (sd.title_file, sd.description_file) AGAINST ('{$search_condition}' IN BOOLEAN MODE) AS REL");
                    $order_by[] = 'REL DESC';
                }
            } else {
                $this->db->where_raw('(sd.title_file LIKE ? || sd.description_file LIKE ?)', ["%{$keywords}%", "%{$keywords}%"]);
            }
        }

        return (int) ($this->db->query_one()['AGGREGATE'] ?? 0);
    }

    public function get_library_documents(array $params = array())
    {
        $skip = null;
        $limit = null;
        $alias = 'FILES';
        $with = array('companies' => true, 'categories' => true);
        $order = array("{$alias}.edit_date_file" => 'DESC');
        $group = array();
        $columns = !empty($params['columns']) ? '*' : array(
            "{$alias}.*",
            'COMPANIES.logo_company',
            'COMPANIES.index_name',
            'COMPANIES.name_company',
            'COMPANIES.type_company',
            'COMPANIES.visible_company',
            'CATEGORIES.category_title',
        );
        $conditions = array();

        extract($params);
        extract($with, EXTR_PREFIX_ALL, 'with');
        extract($conditions, EXTR_PREFIX_ALL, 'condition');

        $this->db->select($this->prepareColumns($columns));
        $this->db->from("{$this->library_table} AS {$alias}");

        //region Joins
        if (!empty($with)) {
            if (isset($with_companies) && $with_companies) {
                $this->with_seller($alias, 'id_seller', $with_companies);
            }
            if (isset($with_categories) && $with_categories) {
                $this->with_categories($alias, 'id_category', $with_categories);
            }
        }
        //endregion Joins

        //region Conditions
        if (!empty($conditions)) {
            if (isset($condition_created_from)) {
                $this->db->where("{$alias}.add_date_file >= ?", $condition_created_from);
            }

            if (isset($condition_created_to)) {
                $this->db->where("{$alias}.add_date_file <= ?", $condition_created_to);
            }

            if (isset($condition_updated_from)) {
                $this->db->where("{$alias}.edit_date_file >= ?", $condition_updated_from);
            }

            if (isset($condition_updated_to)) {
                $this->db->where("{$alias}.edit_date_file <= ?", $condition_updated_to);
            }

            if (isset($condition_search)) {
                if (str_word_count_utf8($condition_search) > 1) {
                    $escaped_search_string = $this->db->getConnection()->quote(trim($condition_search));
                    $search_parts = preg_split("/\b/", trim($escaped_search_string, "'"));
                    $search_parts = array_map('trim', $search_parts);
                    $search_parts = array_filter($search_parts);
                    if (!empty($search_parts)) {
                        // Drop array keys
                        $search_parts = array_values($search_parts);
                        // Unite words - each consecutive word have lesser contribution
                        $search_condition = implode('* <', $search_parts);
                        $search_condition = "{$search_condition}*";
                        $this->db->where_raw("MATCH ({$alias}.title_file, {$alias}.description_file) AGAINST (? IN BOOLEAN MODE)", $search_condition);
                    }
                } else {
                    $this->db->where_raw("({$alias}.title_file LIKE ? OR {$alias}.description_file LIKE ?)", ["%{$condition_search}%", "%{$condition_search}%"]);
                }
            }

            if (isset($condition_document)) {
                $this->db->where("{$alias}.id_file = ?", (int) $condition_document);
            }

            if (isset($condition_documents) && !empty($condition_documents)) {
                $this->db->in("{$alias}.id_file", array_map('intval', $condition_documents));
            }

            if (isset($condition_access)) {
                $this->db->where("{$alias}.type_file = ?", $condition_access);
            }

            if (isset($condition_category)) {
                $this->db->where("{$alias}.id_category = ?", (int) $condition_category);
            }

            if (isset($condition_categories)) {
                $condition_categories = getArrayFromString($condition_categories);
                $this->db->in("{$alias}.id_category", array_map('intval', $condition_categories));
            }

            if (isset($condition_seller)) {
                $this->db->where("{$alias}.id_seller = ?", (int) $condition_seller);
            }

            if (isset($condition_company) && $with_companies) {
                $this->db->where('COMPANIES.id_company = ?', (int) $condition_company);
            }

            if (isset($condition_visibility) && $with_companies) {
                $this->db->where('COMPANIES.visible_company = ?', (int) $condition_visibility);
            }

            if (isset($condition_company_type) && $with_companies) {
                $this->db->where('COMPANIES.type_company = ?', $condition_company_type);
            }
        }
        //endregion Conditions

        //region GroupBy
        foreach ($group as $column) {
            $this->db->groupby($column);
        }
        //endregion GroupBy

        //region OrderBy
        $ordering = array();
        foreach ($order as $column => $direction) {
            if (!empty($direction) && is_string($direction)) {
                $direction = mb_strtoupper($direction);
                $ordering[] = "{$column} {$direction}";
            } else {
                $ordering[] = $column;
            }
        }
        if (!empty($ordering)) {
            $this->db->orderby(implode(', ', $ordering));
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

        return $this->db->query_all();
    }

    public function count_library_documents(array $params = array())
    {
        $multiple = false;
        $alias = 'FILES';
        $group = array();
        $with = array('companies' => true, 'categories' => true);
        $columns = array('COUNT(*) as AGGREGATE');
        $conditions = array();

        extract($params);
        extract($with, EXTR_PREFIX_ALL, 'with');
        extract($conditions, EXTR_PREFIX_ALL, 'condition');

        $this->db->select($this->prepareColumns($columns));
        $this->db->from("{$this->library_table} as {$alias}");

        //region Joins
        if (!empty($with)) {
            if (isset($with_companies) && $with_companies) {
                $this->with_seller($alias, 'id_seller', $with_companies);
            }
            if (isset($with_categories) && $with_categories) {
                $this->with_categories($alias, 'id_category', $with_categories);
            }
        }
        //endregion Joins

        //region Conditions
        if (!empty($conditions)) {
            if (isset($condition_created_from)) {
                $this->db->where("{$alias}.add_date_file >= ?", $condition_created_from);
            }

            if (isset($condition_created_to)) {
                $this->db->where("{$alias}.add_date_file <= ?", $condition_created_to);
            }

            if (isset($condition_updated_from)) {
                $this->db->where("{$alias}.edit_date_file >= ?", $condition_updated_from);
            }

            if (isset($condition_updated_to)) {
                $this->db->where("{$alias}.edit_date_file <= ?", $condition_updated_to);
            }

            if (isset($condition_search)) {
                if (str_word_count_utf8($condition_search) > 1) {
                    $escaped_search_string = $this->db->getConnection()->quote(trim($condition_search));
                    $search_parts = preg_split("/\b/", trim($escaped_search_string, "'"));
                    $search_parts = array_map('trim', $search_parts);
                    $search_parts = array_filter($search_parts);
                    if (!empty($search_parts)) {
                        // Drop array keys
                        $search_parts = array_values($search_parts);
                        // Unite words - each consecutive word have lesser contribution
                        $search_condition = implode('* <', $search_parts);
                        $search_condition = "{$search_condition}*";
                        $this->db->where_raw("MATCH ({$alias}.title_file, {$alias}.description_file) AGAINST (? IN BOOLEAN MODE)", $search_condition);
                    }
                } else {
                    $this->db->where_raw("({$alias}.title_file LIKE ? OR {$alias}.description_file LIKE ?)", ["%{$condition_search}%", "%{$condition_search}%"]);
                }
            }

            if (isset($condition_document)) {
                $this->db->where("{$alias}.id_file = ?", (int) $condition_document);
            }

            if (isset($condition_documents) && !empty($condition_documents)) {
                $this->db->in("{$alias}.id_file", array_map('intval', $condition_documents));
            }

            if (isset($condition_access)) {
                $this->db->where("{$alias}.type_file = ?", $condition_access);
            }

            if (isset($condition_category)) {
                $this->db->where("{$alias}.id_category = ?", (int) $condition_category);
            }

            if (isset($condition_categories)) {
                $condition_categories = getArrayFromString($condition_categories);
                $this->db->in("{$alias}.id_category", array_map('intval', $condition_categories));
            }

            if (isset($condition_seller)) {
                $this->db->where("{$alias}.id_seller = ?", (int) $condition_seller);
            }

            if (isset($condition_company) && $with_companies) {
                $this->db->where('COMPANIES.id_company = ?', (int) $condition_company);
            }

            if (isset($condition_visibility) && $with_companies) {
                $this->db->where('COMPANIES.visible_company = ?', (int) $condition_visibility);
            }

            if (isset($condition_company_type) && $with_companies) {
                $this->db->where('COMPANIES.type_company = ?', $condition_company_type);
            }
        }
        //endregion Conditions

        //region GroupBy
        foreach ($group as $column) {
            $this->db->groupby($column);
        }
        //endregion GroupBy

        if ($multiple) {
            if (!$this->db->query()) {
                return array();
            }

            return $this->db->getQueryResult()->fetchAllAssociative();
        }

        return (int) ($this->db->query_one()['AGGREGATE'] ?? 0);
    }

    public function has_library_document($document_title, array $conditions = array())
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from("{$this->library_table} AS DOCUMENTS");
        $this->db->where('DOCUMENTS.title_file = ?', $document_title);

        extract($conditions, EXTR_PREFIX_ALL, 'condition');

        if (!empty($conditions)) {
            if (!empty($condition_not)) {
                $condition_not = getArrayFromString($condition_not);
                $this->db->where_raw("DOCUMENTS.id_file NOT IN (" . implode(',', array_fill(0, count($condition_not), '?')) . ")", array_map('intval', $condition_not));
            }

            if (isset($condition_category)) {
                $this->db->where('DOCUMENTS.id_category = ?', (int) $condition_category);
            }

            if (isset($condition_seller)) {
                $this->db->where('DOCUMENTS.id_seller = ?', (int) $condition_seller);
            }

            if (isset($condition_company)) {
                $this->db->where('DOCUMENTS.id_company = ?', (int) $condition_company);
            }
        }

        return (bool) (int) ($this->db->query_one()['AGGREGATE'] ?? false);
    }

    public function add_library_category(array $data)
    {
        return $this->db->insert($this->library_categories_table, $data);
    }

    public function update_library_category($category_id, array $data)
    {
        $this->db->where('id_category', (int) $category_id);
        return $this->db->update($this->library_categories_table, $data);
    }

    public function delete_library_category($category_id)
    {
        $this->db->where('id_category', (int) $category_id);
        return $this->db->delete($this->library_categories_table);
    }

    public function get_library_category($category_id, array $conditions = array())
    {
        $this->db->select('*');
        $this->db->from("{$this->library_categories_table} AS CATEGORIES");
        $this->db->where('CATEGORIES.id_category = ?', (int) $category_id);

        extract($conditions, EXTR_PREFIX_ALL, 'condition');

        if (!empty($conditions)) {
            if (isset($condition_seller)) {
                $this->db->where('CATEGORIES.id_seller = ?', (int) $condition_seller);
            }
        }

        return $this->db->query_one() ?: null;
    }

    public function get_library_categories(array $params = array())
    {
        $skip = null;
        $limit = null;
        $alias = 'CATEGORIES';
        $with = array();
        $order = array('id_category' => 'ASC');
        $group = array();
        $columns = array('*');
        $conditions = array();

        extract($params);
        extract($with, EXTR_PREFIX_ALL, 'with');
        extract($conditions, EXTR_PREFIX_ALL, 'condition');

        $this->db->select($this->prepareColumns($columns));
        $this->db->from("{$this->library_categories_table} AS {$alias}");

        //region Joins
        if (!empty($with)) {
            // Here be dragons
        }
        //endregion Joins

        //region Conditions
        if (!empty($conditions)) {
            if (isset($condition_categories)) {
                $condition_categories = getArrayFromString($condition_categories);
                $this->db->in("{$alias}.id_category", array_map('intval', $condition_categories));
            }

            if (isset($condition_seller)) {
                $this->db->where("{$alias}.id_seller = ?", (int) $condition_seller);
            }

            if (isset($condition_created_from)) {
                $this->db->where("{$alias}.created_at >= ?", $condition_created_from);
            }

            if (isset($condition_created_to)) {
                $this->db->where("{$alias}.created_at <= ?", $condition_created_to);
            }

            if (isset($condition_updated_from)) {
                $this->db->where("{$alias}.updated_at >= ?", $condition_updated_from);
            }

            if (isset($condition_updated_to)) {
                $this->db->where("{$alias}.updated_at <= ?", $condition_updated_to);
            }

            if (isset($condition_search)) {
                if (str_word_count_utf8($condition_search) > 1) {
                    $escaped_search_string = $this->db->getConnection()->quote(trim($condition_search));
                    $search_parts = preg_split("/\b/", trim($escaped_search_string, "'"));
                    $search_parts = array_map('trim', $search_parts);
                    $search_parts = array_filter($search_parts);
                    if (!empty($search_parts)) {
                        // Drop array keys
                        $search_parts = array_values($search_parts);
                        // Unite words - each consecutive word have lesser contribution
                        $search_condition = implode('* <', $search_parts);
                        $search_condition = "{$search_condition}*";
                        $this->db->where_raw("MATCH ({$alias}.category_title) AGAINST (? IN BOOLEAN MODE)", $search_condition);
                    }
                } else {
                    $this->db->where_raw("{$alias}.category_title LIKE ?", "%{$condition_search}%");
                }
            }
        }
        //endregion Conditions

        //region GroupBy
        foreach ($group as $column) {
            $this->db->groupby($column);
        }
        //endregion GroupBy

        //region OrderBy
        $ordering = array();
        foreach ($order as $column => $direction) {
            if (!empty($direction) && is_string($direction)) {
                $direction = mb_strtoupper($direction);
                $ordering[] = "{$column} {$direction}";
            } else {
                $ordering[] = $column;
            }
        }
        if (!empty($ordering)) {
            $this->db->orderby(implode(', ', $ordering));
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

        return $this->db->query_all();
    }

    public function count_library_categories(array $params = array())
    {
        $multiple = false;
        $alias = 'CATEGORIES';
        $group = array();
        $with = array();
        $columns = array('COUNT(*) as AGGREGATE');
        $conditions = array();

        extract($params);
        extract($with, EXTR_PREFIX_ALL, 'with');
        extract($conditions, EXTR_PREFIX_ALL, 'condition');

        $this->db->select($this->prepareColumns($columns));
        $this->db->from("{$this->library_categories_table} as {$alias}");

        //region Joins
        if (!empty($with)) {
            // Here be dragons
        }
        //endregion Joins

        //region Conditions
        if (!empty($conditions)) {
            if (isset($condition_categories)) {
                $condition_categories = getArrayFromString($condition_categories);
                $this->db->in("{$alias}.id_category", array_map('intval', $condition_categories));
            }

            if (isset($condition_seller)) {
                $this->db->where("{$alias}.id_seller = ?", (int) $condition_seller);
            }

            if (isset($condition_created_from)) {
                $this->db->where("{$alias}.created_at >= ?", $condition_created_from);
            }

            if (isset($condition_created_to)) {
                $this->db->where("{$alias}.created_at <= ?", $condition_created_to);
            }

            if (isset($condition_updated_from)) {
                $this->db->where("{$alias}.updated_at >= ?", $condition_updated_from);
            }

            if (isset($condition_updated_to)) {
                $this->db->where("{$alias}.updated_at <= ?", $condition_updated_to);
            }

            if (isset($condition_search)) {
                if (str_word_count_utf8($condition_search) > 1) {
                    $escaped_search_string = $this->db->getConnection()->quote(trim($condition_search));
                    $search_parts = preg_split("/\b/", trim($escaped_search_string, "'"));
                    $search_parts = array_map('trim', $search_parts);
                    $search_parts = array_filter($search_parts);
                    if (!empty($search_parts)) {
                        // Drop array keys
                        $search_parts = array_values($search_parts);
                        // Unite words - each consecutive word have lesser contribution
                        $search_condition = implode('* <', $search_parts);
                        $search_condition = "{$search_condition}*";
                        $this->db->where_raw("MATCH ({$alias}.category_title) AGAINST (? IN BOOLEAN MODE)", $search_condition);
                    }
                } else {
                    $this->db->where_raw("{$alias}.category_title LIKE ?", "%{$condition_search}%");
                }
            }
        }
        //endregion Conditions

        //region GroupBy
        foreach ($group as $column) {
            $this->db->groupby($column);
        }
        //endregion GroupBy

        if ($multiple) {
            if (!$this->db->query()) {
                return array();
            }

            return $this->db->getQueryResult()->fetchAllAssociative();
        }

        return (int) ($this->db->query_one()['AGGREGATE'] ?? 0);
    }

    public function has_library_category($category_title, array $conditions = array())
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from("{$this->library_categories_table} AS CATEGORIES");
        $this->db->where('CATEGORIES.category_title = ?', $category_title);

        extract($conditions, EXTR_PREFIX_ALL, 'condition');

        if (!empty($conditions)) {
            if (!empty($condition_not)) {
                $condition_not = getArrayFromString($condition_not);
                $this->db->where_raw("CATEGORIES.id_category NOT IN (" . implode(',', array_fill(0, count($condition_not), '?')) . ")", array_map('intval', $condition_not));
            }

            if (isset($condition_seller)) {
                $this->db->where('CATEGORIES.id_seller = ?', (int) $condition_seller);
            }
        }

        return (bool) (int) ($this->db->query_one()['AGGREGATE'] ?? false);
    }

    public function exist_documents_in_category($category_id)
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from("{$this->library_table}");
        $this->db->where('id_category = ?', (int) $category_id);
        return (bool) (int) ($this->db->query_one()['AGGREGATE'] ?? false);
    }

    private function with_categories($table, $binding, $relation)
    {
        $this->db->join("{$this->library_categories_table} AS CATEGORIES", "CATEGORIES.id_category = {$table}.{$binding}", 'left');
        if (is_callable($relation)) {
            $relation($this->db, $this);
        }
    }

    private function with_seller($table, $binding, $relation)
    {
        $this->db->join("{$this->companies_table} AS COMPANIES", "COMPANIES.id_user = {$table}.{$binding}", 'left');
        if (is_callable($relation)) {
            $relation($this->db, $this);
        }
    }
}
