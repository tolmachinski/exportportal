<?php

declare(strict_types=1);

use App\Common\Database\BaseModel;

/**
 * seller_updates_model.php
 * model for seller updates.
 *
 * @author Cravciuc Andrei
 */
class Seller_Updates_Model extends BaseModel
{
    /**
     * Name of the seller updates table.
     *
     * @var string
     */
    private $updates_table = 'seller_updates';

    /**
     * Name of the companies table.
     *
     * @var string
     */
    private $companies_table = 'company_base';

    public function get_update($update_id)
    {
        $this->db->select('*');
        $this->db->from($this->updates_table);
        $this->db->where('id_update = ?', (int) $update_id);

        return $this->db->query_one() ?: null;
    }

    public function get_seller_updates(array $params = array())
    {
        $page = 1;
        $per_p = 20;
        $order = array('id_update' => 'DESC');

        extract($params);

        switch ($sort_by) {
            case 'date_asc':
                $order = array('date_update' => 'ASC');

            break;
            case 'date_desc':
                $order = array('date_update' => 'DESC');

            break;
            case 'rand':
                $order = array('RAND()' => '');

            break;
        }

        if (isset($id_company)) {
            $conditions['company'] = (int) $id_company;
        }
        if (isset($id_update)) {
            $conditions['updates'] = $id_update;
        }

        $skip = null;
        $limit = null;
        if ($pagination) {
            $per_p = (int) $per_p !== 0 ? $per_p : 20;
            $page = $page > 0 ? $page : 1;
            $limit = $per_p;
            $skip = ($page - 1) * $per_p;
            if ($skip < 0) {
                $skip = 0;
            }
        }

        return $this->get_updates(compact('conditions', 'order', 'limit', 'skip'));
    }

    public function get_updates(array $params = array())
    {
        $skip = null;
        $limit = null;
        $alias = 'UPDATES';
        $with = array();
        $order = array("{$alias}.date_update" => 'DESC');
        $group = array();
        $columns = array();
        $conditions = array();

        extract($params);
        extract($with, EXTR_PREFIX_ALL, 'with');
        extract($conditions, EXTR_PREFIX_ALL, 'condition');

        $this->db->select($this->prepareColumns($columns));
        $this->db->from("{$this->updates_table} AS {$alias}");

        //region Joins
        if (!empty($with)) {
            if (isset($with_companies) && $with_companies) {
                $this->with_companies($alias, 'id_company', $with_companies);
            }
        }
        //endregion Joins

        //region Conditions
        if (!empty($conditions)) {
            if (isset($condition_created_from)) {
                $this->db->where("{$alias}.date_create >= ?", $condition_created_from);
            }

            if (isset($condition_created_to)) {
                $this->db->where("{$alias}.date_create <= ?", $condition_created_to);
            }

            if (isset($condition_updated_from)) {
                $this->db->where("{$alias}.date_update >= ?", $condition_updated_from);
            }

            if (isset($condition_updated_to)) {
                $this->db->where("{$alias}.date_update <= ?", $condition_updated_to);
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
                        $this->db->where_raw("MATCH ({$alias}.text_update) AGAINST (? IN BOOLEAN MODE)", $search_condition);
                    }
                } else {
                    $this->db->where_raw("({$alias}.text_update LIKE ?)", "%{$condition_search}%");
                }
            }
            if (isset($condition_update)) {
                $this->db->where("{$alias}.id_update = ?", (int) $condition_update);
            }

            if (!empty($condition_updates)) {
                $condition_updates = getArrayFromString($condition_updates);
                $this->db->in('id_update', array_map('intval', $condition_updates));
            }

            if (isset($condition_seller)) {
                $this->db->where("{$alias}.id_seller = ?", (int) $condition_seller);
            }
            if (isset($condition_company)) {
                $this->db->where("{$alias}.id_company = ?", (int) $condition_company);
            }
            if (isset($condition_company_visibility) && $with_companies) {
                $this->db->where('COMPANIES.visible_company = ?', (int) $condition_company_visibility);
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

    public function count_seller_updates(array $params = array())
    {
        extract($params);

        $conditions = [];

        if (isset($id_company)) {
            $conditions['company'] = (int) $id_company;
        }
        if (isset($id_update)) {
            $conditions['updates'] = $id_update;
        }

        return $this->count_updates(compact('conditions'));
    }

    public function count_updates(array $params = array())
    {
        $multiple = false;
        $alias = 'UPDATES';
        $with = array();
        $group = array();
        $columns = array('COUNT(*) as AGGREGATE');
        $conditions = array();

        extract($params);
        extract($with, EXTR_PREFIX_ALL, 'with');
        extract($conditions, EXTR_PREFIX_ALL, 'condition');

        $this->db->select($this->prepareColumns($columns));
        $this->db->from("{$this->updates_table} AS {$alias}");

        //region Joins
        if (!empty($with)) {
            if (isset($with_companies) && $with_companies) {
                $this->with_companies($alias, 'id_company', $with_companies);
            }
        }
        //endregion Joins

        //region Conditions
        if (!empty($conditions)) {
            if (isset($condition_created_from)) {
                $this->db->where("{$alias}.date_create >= ?", $condition_created_from);
            }

            if (isset($condition_created_to)) {
                $this->db->where("{$alias}.date_create <= ?", $condition_created_to);
            }

            if (isset($condition_updated_from)) {
                $this->db->where("{$alias}.date_update >= ?", $condition_updated_from);
            }

            if (isset($condition_updated_to)) {
                $this->db->where("{$alias}.date_update <= ?", $condition_updated_to);
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
                        $this->db->where_raw("MATCH ({$alias}.text_update) AGAINST (? IN BOOLEAN MODE)", $search_condition);
                    }
                } else {
                    $this->db->where_raw("({$alias}.text_update LIKE ?)", "%{$condition_search}%");
                }
            }

            if (isset($condition_update)) {
                $this->db->where("{$alias}.id_update = ?", (int) $condition_update);
            }

            if (!empty($condition_updates)) {
                $condition_updates = getArrayFromString($condition_updates);
                $this->db->in('id_update', array_map('intval', $condition_updates));
            }

            if (isset($condition_seller)) {
                $this->db->where("{$alias}.id_seller = ?", (int) $condition_seller);
            }

            if (isset($condition_company)) {
                $this->db->where("{$alias}.id_company = ?", (int) $condition_company);
            }

            if (isset($condition_company_visibility) && $with_companies) {
                $this->db->where('COMPANIES.visible_company = ?', (int) $condition_company_visibility);
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

    public function exist_update($update_id)
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from("{$this->updates_table} AS `UPDATES`");
        $this->db->where('id_update = ?', (int) $update_id);

        return (bool) (int) ($this->db->query_one()['AGGREGATE'] ?? false);
    }

    public function is_my_update($update_id, $company_id)
    {
        $this->db->select('COUNT(*) as AGGREGATE');
        $this->db->from($this->updates_table);
        $this->db->where('id_update = ?', (int) $update_id);
        $this->db->where('id_company = ?', (int) $company_id);
        return (bool) (int) ($this->db->query_one()['AGGREGATE'] ?? false);
    }

    public function add_seller_update(array $data)
    {
        return $this->db->insert($this->updates_table, $data);
    }

    public function change_update($update_id, $data)
    {
        $this->db->where('id_update', $update_id);
        return $this->db->update($this->updates_table, $data);
    }

    public function delete_update($update_id)
    {
        if (empty($update_id)) {
            return true;
        }

        $this->db->where('id_update = ?', (int) $update_id);
        return $this->db->delete($this->updates_table);
    }

    public function delete_all_updates($update_id)
    {
        if (empty($update_id)) {
            return true;
        }

        $update_id = getArrayFromString($update_id);

        $this->db->in('id_update', array_map('intval', $update_id));

        return $this->db->delete($this->updates_table);
    }

    private function with_companies($table, $binding, $relation)
    {
        $this->db->join("{$this->companies_table} AS COMPANIES", "COMPANIES.id_company = {$table}.{$binding}", 'left');
        if (is_callable($relation)) {
            $relation($this->db, $this);
        }
    }
}
