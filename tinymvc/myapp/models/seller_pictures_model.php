<?php

declare(strict_types=1);

use App\Common\Database\BaseModel;

/**
 * seller_pictures_model.php
 * model for seller pictures.
 *
 * @author Cravciuc Andrei
 */
class Seller_Pictures_Model extends BaseModel
{

    /**
     * Name of the  table.
     *
     * @var string
     */
    private $pictures_table = 'seller_photo';

    /**
     * Name of the pictures categories table.
     *
     * @var string
     */
    private $pictures_categories_table = 'seller_photo_categories';

    /**
     * Name of the pictures table.
     *
     * @var string
     */
    private $comment_pictures_table = 'seller_photo_comments';

    /**
     * Name of the companies table.
     *
     * @var string
     */
    private $companies_table = 'company_base';

    /**
     * Name of the users table.
     *
     * @var string
     */
    private $users_table = 'users';

    /**
     * Name of the groups table.
     *
     * @var string
     */
    private $groups_table = 'user_groups';

    /**
     * Name of the countries table.
     *
     * @var string
     */
    private $countries_table = 'port_country';

    public function add_picture(array $data)
    {
        return $this->db->insert($this->pictures_table, $data);
    }

    public function get_picture($picture_id)
    {
        $this->db->select($this->prepareColumns(array('PICTURES.*', 'CATEGORIES.category_title')));
        $this->db->from("{$this->pictures_table} AS PICTURES");
        $this->db->join("{$this->pictures_categories_table} AS CATEGORIES", 'CATEGORIES.id_category = PICTURES.id_category', 'LEFT');
        $this->db->where('id_photo = ?', (int) $picture_id);

        return $this->db->query_one();
    }

    public function get_seller_pictures(array $conditions = array())
    {
        $page = 1;
        $per_p = 20;
        $order_by = 'sp.id_photo DESC';
        $pagination = true;

        extract($conditions);

        $this->db->select('sp.*, spc.category_title');
        $this->db->from("{$this->pictures_table} sp");
        $this->db->join("{$this->pictures_categories_table} spc", 'sp.id_category = spc.id_category', 'left');

        if (isset($actulized_photo)) {
            $this->db->where('sp.actulized_photo = ?', (int) $actulized_photo);
        }

        if (isset($id_company)) {
            $this->db->where('sp.id_company = ?', (int) $id_company);
        }

        if (!empty($id_photo)) {
            $id_photo = getArrayFromString($id_photo);
            $this->db->in('sp.id_photo', array_map('intval', $id_photo));
        }

        if (!empty($not_photo)) {
            $not_photo = getArrayFromString($not_photo);
            $this->db->where_raw('sp.id_photo NOT IN (' . implode(',', array_fill(0, count($not_photo), '?')) . ')', $not_photo);
        }

        switch ($sort_by) {
            case 'title_asc': $order_by = 'sp.title_photo ASC'; break;
            case 'title_desc': $order_by = 'sp.title_photo DESC'; break;
            case 'date_asc': $order_by = 'sp.add_date_photo ASC'; break;
            case 'date_desc': $order_by = 'sp.add_date_photo DESC'; break;
            case 'rand': $order_by = ' RAND()'; break;
        }

        $this->db->orderby($order_by);

        if (isset($start) && isset($limit)) {
            $this->db->limit($limit, $start);
        } elseif ($pagination) {
            $per_p = (int) $per_p !== 0 ? $per_p : 20;

            $page = $page > 0 ? $page : 1;
            $limit = $per_p;
            $skip = ($page - 1) * $per_p;
            if ($skip < 0) {
                $skip = 0;
            }

            $this->db->limit($limit, $skip);
        }

        return $this->db->query_all($sql);
    }

    public function count_seller_pictures(array $conditions = array())
    {
        extract($conditions);

        $this->db->select('COUNT(*) as counter');
        $this->db->from("{$this->pictures_table} sp");
        $this->db->join("{$this->pictures_categories_table} spc", 'sp.id_category = spc.id_category', 'left');

        if (isset($id_company)) {
            $this->db->where('sp.id_company = ?', (int) $id_company);
        }

        if (!empty($id_photo)) {
            $id_photo = getArrayFromString($id_photo);
            $this->db->in('sp.id_photo', array_map('intval', $id_photo));
        }

        if (isset($not_photo) && !empty($not_photo)) {
            $not_photo = getArrayFromString($not_photo);
            $this->db->where_raw('sp.id_photo NOT IN (' . implode(',', array_fill(0, count($not_photo), '?')) . ')', $not_photo);
        }

        return (int) ($this->db->query_one($sql)['counter'] ?? 0);
    }

    public function update_picture($picture_id, array $data)
    {
        $this->db->where('id_photo = ?', (int) $picture_id);
        return $this->db->update($this->pictures_table, $data);
    }

    public function is_my_picture($picture_id, $company_id)
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from("{$this->pictures_table} AS PICTURES");
        $this->db->where('id_photo = ?', (int) $picture_id);
        $this->db->where('id_company = ?', (int) $company_id);

        return (bool) (int) ($this->db->query_one()['AGGREGATE'] ?? false);
    }

    public function exist_picture($picture_id)
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from("{$this->pictures_table} AS PICTURES");
        $this->db->where('id_photo = ?', (int) $picture_id);

        return (bool) (int) ($this->db->query_one()['AGGREGATE'] ?? false);
    }

    public function delete_picture($picture_id)
    {
        if (empty($picture_id)) {
            return true;
        }

        $this->db->where('id_photo = ?', (int) $picture_id);
        return $this->db->delete($this->pictures_table);
    }

    public function delete_pictures($photos)
    {
        if (empty($photos)) {
            return true;
        }

        $photos = getArrayFromString($photos);
        $this->db->in('id_photo', array_map('intval', $photos));

        return $this->db->delete($this->pictures_table);
    }

    public function get_pictures(array $params = array())
    {
        $alias = 'PICTURES';
        $with = array('companies' => true, 'categories' => true);
        $order = array();
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
        $this->db->from("{$this->pictures_table} as {$alias}");

        //region Joins
        if (!empty($with)) {
            if (isset($with_companies) && $with_companies) {
                $this->with_companies($alias, 'id_company', $with_companies);
            }
            if (isset($with_categories) && $with_categories) {
                $this->with_categories($alias, 'id_category', $with_categories);
            }
        }
        //endregion Joins

        //region Conditions
        if (!empty($conditions)) {
            if (isset($condition_created_from)) {
                $this->db->where("{$alias}.add_date_photo >= ?", $condition_created_from);
            }

            if (isset($condition_created_to)) {
                $this->db->where("{$alias}.add_date_photo <= ?", $condition_created_to);
            }

            if (isset($condition_updated_from)) {
                $this->db->where("{$alias}.edit_date_photo >= ?", $condition_updated_from);
            }

            if (isset($condition_updated_to)) {
                $this->db->where("{$alias}.edit_date_photo <= ?", $condition_updated_to);
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
                        $this->db->where_raw("MATCH ({$alias}.title_photo, {$alias}.description_photo) AGAINST (? IN BOOLEAN MODE)", $search_condition);
                    }
                } else {
                    $this->db->where_raw("({$alias}.title_photo LIKE ? OR {$alias}.description_photo LIKE ?)", ["%{$condition_search}%", "%{$condition_search}%"]);
                }
            }

            if (isset($condition_photo)) {
                $this->db->where("{$alias}.id_photo = ?", (int) $condition_photo);
            }

            if (isset($condition_photos)) {
                if (!empty($condition_photos)) {
                    $this->db->in("{$alias}.id_photo", array_map('intval', $condition_photos));
                }
            }

            if (isset($condition_category)) {
                $this->db->where("{$alias}.id_category = ?", (int) $condition_category);
            }

            if (isset($condition_categories)) {
                $condition_categories = getArrayFromString($condition_categories);
                $this->db->in("{$alias}.id_category", array_map('intval', $condition_categories));
            }

            if (isset($condition_company)) {
                $this->db->where("{$alias}.id_company = ?", (int) $condition_company);
            }

            if (isset($condition_visibility) && $with_companies) {
                $this->db->where('COMPANIES.visible_company = ?', (int) $condition_visibility);
            }

            if (isset($condition_seller) && $with_companies) {
                $this->db->where('COMPANIES.id_user = ?', (int) $condition_seller);
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

        return $this->db->query_all() ?: [];
    }

    public function count_pictures(array $params = array())
    {
        $multiple = false;
        $alias = 'PICTURES';
        $group = array();
        $with = array('companies' => true, 'categories' => true);
        $columns = array('COUNT(*) as AGGREGATE');
        $conditions = array();

        extract($params);
        extract($with, EXTR_PREFIX_ALL, 'with');
        extract($conditions, EXTR_PREFIX_ALL, 'condition');

        $this->db->select($this->prepareColumns($columns));
        $this->db->from("{$this->pictures_table} as {$alias}");

        //region Joins
        if (!empty($with)) {
            if (isset($with_companies) && $with_companies) {
                $this->with_companies($alias, 'id_company', $with_companies);
            }
            if (isset($with_categories) && $with_categories) {
                $this->with_categories($alias, 'id_category', $with_categories);
            }
        }
        //endregion Joins

        //region Conditions
        if (!empty($conditions)) {
            if (isset($condition_created_from)) {
                $this->db->where("{$alias}.add_date_photo >= ?", $condition_created_from);
            }

            if (isset($condition_created_to)) {
                $this->db->where("{$alias}.add_date_photo <= ?", $condition_created_to);
            }

            if (isset($condition_updated_from)) {
                $this->db->where("{$alias}.edit_date_photo >= ?", $condition_updated_from);
            }

            if (isset($condition_updated_to)) {
                $this->db->where("{$alias}.edit_date_photo <= ?", $condition_updated_to);
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
                        $this->db->where_raw("MATCH ({$alias}.title_photo, {$alias}.description_photo) AGAINST (? IN BOOLEAN MODE)", $search_condition);
                    }
                } else {
                    $this->db->where_raw("({$alias}.title_photo LIKE ? OR {$alias}.description_photo LIKE ?)", ["%{$condition_search}%", "%{$condition_search}%"]);
                }
            }

            if (isset($condition_photo)) {
                $this->db->where("{$alias}.id_photo = ?", (int) $condition_photo);
            }

            if (!empty($condition_photos)) {
                $this->db->in("{$alias}.id_photo", array_map('intval', $condition_photos));
            }

            if (isset($condition_category)) {
                $this->db->where("{$alias}.id_category = ?", (int) $condition_category);
            }

            if (isset($condition_categories)) {
                $condition_categories = getArrayFromString($condition_categories);
                $this->db->in("{$alias}.id_category", array_map('intval', $condition_categories));
            }

            if (isset($condition_company)) {
                $this->db->where("{$alias}.id_company = ?", (int) $condition_company);
            }

            if (isset($condition_visibility) && $with_companies) {
                $this->db->where('COMPANIES.visible_company = ?', (int) $condition_visibility);
            }

            if (isset($condition_seller) && $with_companies) {
                $this->db->where('COMPANIES.id_user = ?', (int) $condition_seller);
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

    public function add_comment(array $data)
    {
        return empty($data) ? false : $this->db->insert($this->comment_pictures_table, $data);
    }

    public function get_comment($comment_id)
    {
        $this->db->select($this->prepareColumns(array(
            'COMMENTS.*',
            'USERS.idu',
            'USERS.fname',
            'USERS.lname',
            'USERS.email',
            'USERS.`status`',
            'USERS.user_group',
            'USERS.logged',
            'USERS.registration_date',
            'USERS.user_photo',
            "CONCAT(USERS.fname, ' ', USERS.lname) as username",
            'GROUPS.gr_name',
            'COUNTRIES.country',
        )));
        $this->db->from("{$this->comment_pictures_table} AS COMMENTS");
        $this->db->join("{$this->users_table} AS USERS", 'USERS.idu = COMMENTS.id_user', 'LEFT');
        $this->db->join("{$this->groups_table} AS `GROUPS`", 'GROUPS.idgroup = USERS.user_group', 'LEFT');
        $this->db->join("{$this->countries_table} COUNTRIES", 'COUNTRIES.id = USERS.country', 'LEFT');

        //region Conditions
        $this->db->where('COMMENTS.id_comment = ?', (int) $comment_id);
        //endregion Conditions

        return $this->db->query_one();
    }

    public function update_comment($comment_id, array $data)
    {
        $this->db->where('id_comment = ?', (int) $comment_id);

        return $this->db->update($this->comment_pictures_table, $data);
    }

    public function censor_comment($comment_id)
    {
        $this->db->where('id_comment = ?', (int) $comment_id);

        return $this->db->update($this->comment_pictures_table, array('censored' => 1));
    }

    public function update_picture_comments_counter($picture_id, $increment)
    {
        return $this->db->query(
            "UPDATE {$this->pictures_table} SET comments_count = comments_count + ? WHERE id_photo = ?",
            array($increment, (int) $picture_id)
        );
    }

    public function is_my_comment($comment_id, $user_id)
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from("{$this->comment_pictures_table} AS COMMENTS");
        $this->db->where('COMMENTS.id_comment = ?', (int) $comment_id);
        $this->db->where('COMMENTS.id_user = ?', (int) $user_id);

        return (bool) (int) ($this->db->query_one()['AGGREGATE'] ?? false);
    }

    public function get_picture_comments($picture_id, array $params = array())
    {
        $group = array('id_comment');
        $order = array('reply_to_comment' => 'ASC', 'COMMENTS.date_comment' => 'ASC');
        $columns = isset($params['columns']) ? 'COMMENTS.*' : array(
            'COMMENTS.*',
            "CONCAT(USERS.fname, ' ', USERS.lname) as username",
            'USERS.idu',
            'USERS.user_photo',
            'GROUPS.gr_name as user_group',
            'COUNTRIES.country',
        );
        $conditions = array();

        extract($params);
        extract($conditions, EXTR_PREFIX_ALL, 'condition');

        $this->db->select($this->prepareColumns($columns));
        $this->db->from("{$this->comment_pictures_table} AS COMMENTS");
        $this->db->join("{$this->users_table} AS USERS", 'USERS.idu = COMMENTS.id_user', 'INNER');
        $this->db->join("{$this->groups_table} AS `GROUPS`", 'GROUPS.idgroup = USERS.user_group', 'INNER');
        $this->db->join("{$this->countries_table} COUNTRIES", 'COUNTRIES.id = USERS.country', 'INNER');

        //region Conditions
        $this->db->where('COMMENTS.id_photo = ?', (int) $picture_id);
        if (!empty($conditions)) {
            if (isset($condition_reply_to)) {
                $this->db->where('COMMENTS.reply_to_comment = ?', (int) $condition_reply_to);
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

        return $this->comment_map($this->db->query_all());
    }

    public function delete_picture_comment($comment_id)
    {
        if (empty($comment_id)) {
            return true;
        }

        $this->db->where('id_comment', (int) $comment_id);

        return $this->db->delete($this->comment_pictures_table);
    }

    public function delete_all_picture_comments($photo_id)
    {
        if (empty($photo_id)) {
            return true;
        }

        $photo_id = getArrayFromString($photo_id);

        $this->db->in('id_photo', array_map('intval', $photo_id));
        return $this->db->delete($this->comment_pictures_table);
    }

    public function moderate_comment($comment_id)
    {
        $this->db->where('id_comment = ?', (int) $comment_id);
        return $this->db->update($this->comment_pictures_table, array('moderated' => 1));
    }

    public function has_picture($picture_title, array $conditions = array())
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from("{$this->pictures_table} AS PICTURES");
        $this->db->where('PICTURES.title_photo = ?', $picture_title);

        extract($conditions, EXTR_PREFIX_ALL, 'condition');

        if (!empty($conditions)) {
            if (!empty($condition_not)) {
                $condition_not = getArrayFromString($condition_not);
                $this->db->where_raw('PICTURES.id_photo NOT IN (' . implode(',', array_fill(0, count($condition_not), '?')) . ')', array_map('intval', $condition_not));
            }

            if (isset($condition_category)) {
                $this->db->where('PICTURES.id_category = ?', (int) $condition_category);
            }

            if (isset($condition_company)) {
                $this->db->where('PICTURES.id_company = ?', (int) $condition_company);
            }
        }

        return (bool) (int) ($this->db->query_one()['AGGREGATE'] ?? false);
    }

    public function add_pictures_category(array $data)
    {
        return $this->db->insert($this->pictures_categories_table, $data);
    }

    public function update_pictures_category($category_id, array $data)
    {
        $this->db->where('id_category', (int) $category_id);
        return $this->db->update($this->pictures_categories_table, $data);
    }

    public function delete_pictures_category($category_id)
    {
        $this->db->where('id_category', (int) $category_id);
        return $this->db->delete($this->pictures_categories_table);
    }

    public function get_pictures_category($category_id, array $conditions = array())
    {
        $this->db->select('*');
        $this->db->from("{$this->pictures_categories_table} AS CATEGORIES");
        $this->db->where('CATEGORIES.id_category = ?', (int) $category_id);

        extract($conditions, EXTR_PREFIX_ALL, 'condition');

        if (!empty($conditions)) {
            if (isset($condition_company)) {
                $this->db->where('CATEGORIES.id_company = ?', (int) $condition_company);
            }
        }

        return $this->db->query_one() ?: null;
    }

    public function get_pictures_categories(array $params = array())
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
        $this->db->from("{$this->pictures_categories_table} AS {$alias}");

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

            if (isset($condition_company)) {
                $this->db->where("{$alias}.id_company = ?", (int) $condition_company);
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

    public function count_pictures_categories(array $params = array())
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
        $this->db->from("{$this->pictures_categories_table} as {$alias}");

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

            if (isset($condition_company)) {
                $this->db->where("{$alias}.id_company = ?", (int) $condition_company);
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

    public function has_pictures_category($category_title, array $conditions = array())
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from("{$this->pictures_categories_table} AS CATEGORIES");
        $this->db->where('CATEGORIES.category_title = ?', $category_title);

        extract($conditions, EXTR_PREFIX_ALL, 'condition');

        if (!empty($conditions)) {
            if (!empty($condition_not)) {
                $condition_not = getArrayFromString($condition_not);
                $this->db->where_raw('CATEGORIES.id_category NOT IN (' . implode(',', array_fill(0, count($condition_not), '?')) . ')', $condition_not);
            }

            if (isset($condition_company)) {
                $this->db->where('CATEGORIES.id_company = ?', (int) $condition_company);
            }
        }

        return (bool) (int) ($this->db->query_one()['AGGREGATE'] ?? false);
    }

    public function exist_pictures_in_category($category_id)
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from("{$this->pictures_table}");
        $this->db->where('id_category = ?', (int) $category_id);

        return (bool) (int) ($this->db->query_one()['AGGREGATE'] ?? false);
    }

    private function comment_map(array $array)
    {
        $tree = [];
        $ds = array_column($array, null, 'id_comment');

        foreach ($ds as $key => &$node) {
            if (0 == $node['reply_to_comment']) {
                $tree[$key] = &$node;
            } else {
                $ds[$node['reply_to_comment']]['replies'][$key] = &$node;
            }
        }

        return $tree;
    }

    private function with_categories($table, $binding, $relation)
    {
        $this->db->join("{$this->pictures_categories_table} AS CATEGORIES", "CATEGORIES.id_category = {$table}.{$binding}", 'left');
        if (is_callable($relation)) {
            $relation($this->db, $this);
        }
    }

    private function with_companies($table, $binding, $relation)
    {
        $this->db->join("{$this->companies_table} AS COMPANIES", "COMPANIES.id_company = {$table}.{$binding}", 'left');
        if (is_callable($relation)) {
            $relation($this->db, $this);
        }
    }
}
