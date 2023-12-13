<?php

declare(strict_types=1);

use App\Common\Database\BaseModel;
use App\Common\Exceptions\DependencyException;
use App\Common\Exceptions\NotFoundException;
use App\Common\Exceptions\NotUniqueException;
use App\Common\Exceptions\OwnershipException;

/**
 * seller_videos_model.php
 * model for seller videos.
 *
 * @author Cravciuc Andrei
 */
class Seller_Videos_Model extends BaseModel
{
    /**
     * Name of the videos table.
     *
     * @var string
     */
    private $video_table = 'seller_videos';

    /**
     * Name of the videso categories table.
     *
     * @var string
     */
    private $videos_categories_table = 'seller_video_categories';

    /**
     * Name of the videos comments table.
     *
     * @var string
     */
    private $comments_video_table = 'seller_video_comments';

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
     * Name of the companies table.
     *
     * @var string
     */
    private $companies_table = 'company_base';

    /**
     * Name of the countries table.
     *
     * @var string
     */
    private $countries_table = 'port_country';

    public function is_my_video($video_id, $company_id)
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from("{$this->video_table} AS VIDEOS");
        $this->db->where('id_video = ?', (int) $video_id);
        $this->db->where('id_company = ?', (int) $company_id);

        return (bool) (int) ($this->db->query_one()['AGGREGATE'] ?? false);
    }

    public function is_video_exist($video_id)
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from("{$this->video_table} AS VIDEOS");
        $this->db->where('id_video = ?', (int) $video_id);

        return (bool) (int) ($this->db->query_one()['AGGREGATE'] ?? false);
    }

    public function has_video($video_title, array $conditions = array())
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from("{$this->video_table} AS VIDEOS");
        $this->db->where('VIDEOS.title_video = ?', $video_title);

        extract($conditions, EXTR_PREFIX_ALL, 'condition');

        if (!empty($conditions)) {
            if (!empty($condition_not)) {
                $condition_not = getArrayFromString($condition_not);
                $this->db->where_raw('VIDEOS.id_video NOT IN (' . implode(',', array_fill(0, count($condition_not), '?')) . ')', $condition_not);
            }

            if (isset($condition_category)) {
                $this->db->where('VIDEOS.id_category = ?', (int) $condition_category);
            }

            if (isset($condition_company)) {
                $this->db->where('VIDEOS.id_company = ?', (int) $condition_company);
            }

            if (isset($condition_seller)) {
                $this->db->where('VIDEOS.id_seller = ?', (int) $condition_seller);
            }
        }

        return (bool) (int) ($this->db->query_one()['AGGREGATE'] ?? false);
    }

    public function get_video($video_id)
    {
        $this->db->select($this->prepareColumns(array('VIDEOS.*', 'CATEGORIES.category_title')));
        $this->db->from("{$this->video_table} AS VIDEOS");
        $this->db->join("{$this->videos_categories_table} AS CATEGORIES", 'CATEGORIES.id_category = VIDEOS.id_category', 'LEFT');
        $this->db->where('id_video = ?', (int) $video_id);

        return $this->db->query_one();
    }

    public function get_videos(array $params = array())
    {
        $skip = null;
        $limit = null;
        $alias = 'VIDEOS';
        $with = array();
        $order = array();
        $group = array();
        $columns = array('*');
        $conditions = array();

        extract($params);
        extract($with, EXTR_PREFIX_ALL, 'with');
        extract($conditions, EXTR_PREFIX_ALL, 'condition');

        $this->db->select($this->prepareColumns($columns));
        $this->db->from("{$this->video_table} AS {$alias}");

        //region Joins
        if (!empty($with)) {
            if (isset($with_sellers) && $with_sellers) {
                $this->with_sellers($alias, 'id_seller', $with_sellers);
            }

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
                $this->db->where("{$alias}.add_date_video >= ?", $condition_created_from);
            }

            if (isset($condition_created_to)) {
                $this->db->where("{$alias}.add_date_video <= ?", $condition_created_to);
            }

            if (isset($condition_updated_from)) {
                $this->db->where("{$alias}.edit_date_video >= ?", $condition_updated_from);
            }

            if (isset($condition_updated_to)) {
                $this->db->where("{$alias}.edit_date_video <= ?", $condition_updated_to);
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
                        $this->db->where_raw("MATCH ({$alias}.title_video, {$alias}.description_video) AGAINST (? IN BOOLEAN MODE)", $search_condition);
                    }
                } else {
                    $this->db->where_raw("({$alias}.title_video LIKE ? OR {$alias}.description_video LIKE ?)", ["%{$condition_search}%", "%{$condition_search}%"]);
                }
            }

            if (isset($condition_video)) {
                $this->db->where("{$alias}.id_video = ?", (int) $condition_document);
            }

            if (!empty($condition_videos)) {
                $condition_videos = getArrayFromString($condition_videos);
                $this->db->in("{$alias}.id_video", array_map('intval', $condition_videos));
            }

            if (!empty($condition_not)) {
                $condition_not = getArrayFromString($condition_not);
                $this->db->where_raw('VIDEOS.id_video NOT IN (' . implode(',', array_fill(0, count($condition_not), '?')) . ')', $condition_not);
            }

            if (isset($condition_category)) {
                $this->db->where("{$alias}.id_category = ?", (int) $condition_category);
            }

            if (isset($condition_categories)) {
                $condition_categories = getArrayFromString($condition_categories);
                $this->db->in("{$alias}.id_category", array_map('intval', $condition_categories));
            }

            if (isset($condition_source)) {
                $this->db->where("{$alias}.source_video = ?", $condition_source);
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

    /**
     * Find video owned by specified company.
     *
     * @param int   $category_id
     * @param int   $company_id
     * @param mixed $video_id
     *
     * @throws \App\Common\Exceptions\NotFoundException  if category doesn' exists
     * @throws \App\Common\Exceptions\OwnershipException if company doesn't own the video
     *
     * @return array
     */
    public function find_company_video($video_id, $company_id = null)
    {
        if (
            empty($video_id) ||
            empty($category = $this->get_video($video_id))
        ) {
            throw new NotFoundException('The video with such ID is not found on this server');
        }
        if (null !== $company_id) {
            if ((int) $company_id !== (int) $category['id_company']) {
                throw new OwnershipException('The video does not belong to this company');
            }
        }

        return $category;
    }

    /**
     * Creates new video record from th raw data.
     *
     * @param array $data
     */
    public function add_video(array $data)
    {
        return $this->db->insert($this->video_table, $data);
    }

    /**
     * Creates one video record.
     *
     * @param int         $category
     * @param int         $company
     * @param int         $owner
     * @param string      $title
     * @param string      $description
     * @param string      $url
     * @param string      $video_id
     * @param string      $type
     * @param null|string $thumb
     *
     * @return bool|int
     */
    public function create_video(
        $category,
        $company,
        $owner,
        $title,
        $description,
        $url,
        $video_id,
        $type = 'youtube',
        $thumb = null
    ) {
        if ($this->has_video($title, array(
            'company' => (int) $company,
            'seller'  => (int) $owner,
        ))) {
            throw new NotUniqueException('The video title is not unique');
        }

        return $this->add_video(array(
            'id_seller'         => (int) $owner,
            'id_company'        => (int) $company,
            'id_category'       => (int) $category,
            'title_video'       => $title,
            'description_video' => $description,
            'image_video'       => $thumb,
            'url_video'         => $url,
            'short_url_video'   => $video_id,
            'source_video'      => $type,
        ));
    }

    /**
     * Update record with raw data.
     *
     * @param int   $video_id
     * @param array $data
     *
     * @return bool|int
     */
    public function update_video($video_id, array $data)
    {
        $this->db->where('id_video = ?', (int) $video_id);

        return $this->db->update($this->video_table, $data);
    }

    /**
     * Creates one video record.
     *
     * @param int         $video
     * @param int         $category
     * @param int         $company
     * @param int         $owner
     * @param string      $title
     * @param string      $description
     * @param null|string $url
     * @param null|string $video_id
     * @param null|string $type
     * @param null|string $thumb
     *
     * @return bool|int
     */
    public function change_video(
        $video,
        $category,
        $company,
        $owner,
        $title,
        $description,
        $url = null,
        $video_id = null,
        $type = null,
        $thumb = null
    ) {
        if (null !== $title) {
            if ($this->has_video($title, array(
                'company' => (int) $company,
                'seller'  => (int) $owner,
                'not'     => (int) $video,
            ))) {
                throw new NotUniqueException('The video title is not unique');
            }
        }

        if (null !== $url && filter_var($url, FILTER_VALIDATE_URL) && is_string($url) && false !== strpos($url, '&amp;')) {
            $url = str_replace('&amp;', '&', $url);
        }

        $update = array_filter(
            array(
                'id_category'       => (int) $category,
                'title_video'       => $title,
                'description_video' => $description,
                'image_video'       => $thumb,
                'url_video'         => $url,
                'short_url_video'   => $video_id,
                'source_video'      => $type,
            ),
            function ($item) {
                return null !== $item;
            }
        );

        if (empty($update)) {
            return true;
        }

        return $this->update_video($video, array_merge($update, array('edit_date_video' => date('Y-m-d H:i:s'))));
    }

    public function delete_video($video_id)
    {
        if (empty($video_id)) {
            return true;
        }

        $this->db->where('id_video = ?', (int) $video_id);

        return $this->db->delete($this->video_table);
    }

    public function delete_videos($videos)
    {
        if (empty($videos)) {
            return true;
        }

        $videos = getArrayFromString($videos);
        $this->db->in('id_video', array_map('intval', $videos));

        return $this->db->delete($this->video_table);
    }

    public function get_seller_videos(array $params = array())
    {
        $page = 1;
        $per_p = 20;
        $order = array('id_video' => 'DESC');

        extract($params);

        switch ($sort_by) {
            case 'title_asc':
                $order = array('title_video' => 'ASC');

            break;
            case 'title_desc':
                $order = array('title_video' => 'DESC');

            break;
            case 'date_asc':
                $order = array('add_date_video' => 'ASC');

            break;
            case 'date_desc':
                $order = array('add_date_video' => 'DESC');

            break;
            case 'rand':
                $order = array('RAND()' => '');

            break;
        }

        if (isset($not)) {
            $conditions['not'] = $not;
        }

        if (isset($id_company)) {
            $conditions['company'] = (int) $id_company;
        }

        if (isset($id_video)) {
            if (is_array($id_video)) {
                $conditions['videos'] = array_map('intval', $id_video);
            } elseif (is_string($id_video) && false !== strpos($id_video, ',')) {
                $conditions['videos'] = array_map('intval', explode(',', $id_video));
            } else {
                $conditions['videos'] = (int) $id_video;
            }
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

        return $this->get_videos(compact('conditions', 'order', 'limit', 'skip'));
    }

    public function count_videos(array $params = array())
    {
        $multiple = false;
        $alias = 'VIDEOS';
        $with = array();
        $group = array();
        $columns = array('COUNT(*) as AGGREGATE');
        $conditions = array();

        extract($params);
        extract($with, EXTR_PREFIX_ALL, 'with');
        extract($conditions, EXTR_PREFIX_ALL, 'condition');

        $this->db->select($this->prepareColumns($columns));
        $this->db->from("{$this->video_table} as {$alias}");

        //region Joins
        if (!empty($with)) {
            if (isset($with_sellers) && $with_sellers) {
                $this->with_sellers($alias, 'id_seller', $with_sellers);
            }

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
                $this->db->where("{$alias}.add_date_video >= ?", $condition_created_from);
            }

            if (isset($condition_created_to)) {
                $this->db->where("{$alias}.add_date_video <= ?", $condition_created_to);
            }

            if (isset($condition_updated_from)) {
                $this->db->where("{$alias}.edit_date_video >= ?", $condition_updated_from);
            }

            if (isset($condition_updated_to)) {
                $this->db->where("{$alias}.edit_date_video <= ?", $condition_updated_to);
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
                        $this->db->where_raw("MATCH ({$alias}.title_video, {$alias}.description_video) AGAINST (? IN BOOLEAN MODE)", $search_condition);
                    }
                } else {
                    $this->db->where_raw("({$alias}.title_video LIKE ? OR {$alias}.description_video LIKE ?)", ["%{$condition_search}%", "%{$condition_search}%"]);
                }
            }

            if (isset($condition_video)) {
                $this->db->where("{$alias}.id_video = ?", (int) $condition_video);
            }

            if (!empty($condition_videos)) {
                $condition_videos = getArrayFromString($condition_videos);
                $this->db->in("{$alias}.id_video", array_map('intval', $condition_videos));
            }

            if (!empty($condition_not)) {
                $condition_not = getArrayFromString($condition_not);
                $this->db->where_raw('VIDEOS.id_video NOT IN (' . implode(',', array_fill(0, count($condition_not), '?')) . ')', $condition_not);
            }

            if (isset($condition_category)) {
                $this->db->where("{$alias}.id_category = ?", (int) $condition_category);
            }

            if (isset($condition_categories)) {
                $condition_categories = getArrayFromString($condition_categories);
                $this->db->in("{$alias}.id_category", array_map('intval', $condition_categories));
            }

            if (isset($condition_source)) {
                $this->db->where("{$alias}.source_video = ?", $condition_source);
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

    public function count_seller_videos(array $params = array())
    {
        extract($params);

        $conditions = [];

        if (isset($not)) {
            $conditions['not'] = $not;
        }

        if (isset($id_company)) {
            $conditions['company'] = (int) $id_company;
        }

        if (isset($id_video)) {
            if (is_array($id_video)) {
                $conditions['videos'] = array_map('intval', $id_video);
            } elseif (is_string($id_video) && false !== strpos($id_video, ',')) {
                $conditions['videos'] = array_map('intval', explode(',', $id_video));
            } else {
                $conditions['videos'] = (int) $id_video;
            }
        }

        return $this->count_videos(compact('conditions'));
    }

    public function is_my_comment($comment_id, $user_id)
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from("{$this->comments_video_table} AS COMMENTS");
        $this->db->where('COMMENTS.id_comment = ?', (int) $comment_id);
        $this->db->where('COMMENTS.id_user = ?', (int) $user_id);

        return (bool) (int) ($this->db->query_one()['AGGREGATE'] ?? false);
    }

    public function get_video_comment($comment_id)
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
            '`GROUPS`.gr_name',
            'COUNTRIES.country',
        )));
        $this->db->from("{$this->comments_video_table} AS COMMENTS");
        $this->db->join("{$this->users_table} AS USERS", 'USERS.idu = COMMENTS.id_user', 'LEFT');
        $this->db->join("{$this->groups_table} AS `GROUPS`", '`GROUPS`.idgroup = USERS.user_group', 'LEFT');
        $this->db->join("{$this->countries_table} COUNTRIES", 'COUNTRIES.id = USERS.country', 'LEFT');

        //region Conditions
        $this->db->where('COMMENTS.id_comment = ?', (int) $comment_id);
        //endregion Conditions

        return $this->db->query_one();
    }

    /**
     * Find comment written by speicified user.
     *
     * @param int $comment_id
     * @param int $user_id
     *
     * @throws \App\Common\Exceptions\NotFoundException  if comment doesn' exists
     * @throws \App\Common\Exceptions\OwnershipException if user is not the author of the comment
     *
     * @return array
     */
    public function find_video_comment($comment_id, $user_id = null)
    {
        if (
            empty($comment_id) ||
            empty($comment = $this->get_video_comment($comment_id))
        ) {
            throw new NotFoundException('The comment with such ID is not found on this server');
        }
        if (null !== $user_id) {
            if ((int) $user_id !== (int) $comment['id_user']) {
                throw new OwnershipException('The user is not the author of the comment');
            }
        }

        return $comment;
    }

    public function get_video_comments($video_id, array $params = array())
    {
        $group = array('id_comment');
        $order = array('`COMMENTS`.date_comment' => 'ASC');
        $columns = isset($params['columns']) ? '`COMMENTS`.*' : array(
            '`COMMENTS`.*',
            "CONCAT(`USERS`.fname, ' ', `USERS`.lname) as username",
            '`USERS`.idu',
            '`USERS`.user_photo',
            '`GROUPS`.gr_name as user_group',
            '`COUNTRIES`.country',
        );
        $conditions = array();

        extract($params);
        extract($conditions, EXTR_PREFIX_ALL, 'condition');

        $this->db->select($this->prepareColumns($columns));
        $this->db->from("{$this->comments_video_table} AS `COMMENTS`");
        $this->db->join("{$this->users_table} AS `USERS`", '`USERS`.idu = `COMMENTS`.id_user', 'INNER');
        $this->db->join("{$this->groups_table} AS `GROUPS`", '`GROUPS`.idgroup = `USERS`.user_group', 'INNER');
        $this->db->join("{$this->countries_table} `COUNTRIES`", '`COUNTRIES`.id = `USERS`.country', 'INNER');

        //region Conditions
        $this->db->where('`COMMENTS`.id_video = ?', (int) $video_id);
        if (isset($condition_reply_to)) {
            $this->db->where('`COMMENTS`.reply_to_comment = ?', (int) $condition_reply_to);
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

        return $this->db->query_all();
    }

    /**
     * Find category by provided category ID and seller ID.
     *
     * @param int $category_id
     * @param int $seller_id
     *
     * @throws \App\Common\Exceptions\NotFoundException  if category doesn' exists
     * @throws \App\Common\Exceptions\OwnershipException if seller is not the owner of the category
     *
     * @return array
     */
    public function find_category($category_id, $seller_id)
    {
        if (
            empty($category_id) ||
            empty($category = $this->get_video_category($category_id))
        ) {
            throw new NotFoundException('The category with such ID is not found on this server');
        }
        if ((int) $seller_id !== (int) $category['id_seller']) {
            throw new OwnershipException('The category belongs to another user');
        }

        return $category;
    }

    public function add_video_comment(array $data)
    {
        return empty($data) ? false : $this->db->insert($this->comments_video_table, $data);
    }

    public function update_comment($comment_id, array $data)
    {
        $this->db->where('id_comment = ?', (int) $comment_id);
        return $this->db->update($this->comments_video_table, $data);
    }

    public function censor_comment($comment_id)
    {
        $this->db->where('id_comment = ?', (int) $comment_id);
        return $this->db->update($this->comments_video_table, array('censored' => 1));
    }

    public function moderate_comment($comment_id)
    {
        $this->db->where('id_comment = ?', (int) $comment_id);
        return $this->db->update($this->comments_video_table, array('moderated' => 1));
    }

    public function update_video_comment_counter($video_id, $increment)
    {
        return $this->db->query(
            "UPDATE {$this->video_table} SET comments_count = comments_count + ? WHERE id_video = ?",
            array($increment, (int) $video_id)
        );
    }

    public function delete_video_comment($comment_id)
    {
        if (empty($comment_id)) {
            return true;
        }

        $this->db->where('id_comment', (int) $comment_id);
        return $this->db->delete($this->comments_video_table);
    }

    public function delete_all_video_comments($video_id)
    {
        if (empty($video_id)) {
            return true;
        }

        $video_id = getArrayFromString($video_id);
        $this->db->in('id_video', array_map('intval', $video_id));

        return $this->db->delete($this->comments_video_table);
    }

    public function is_category_exist($category)
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from("{$this->videos_categories_table} AS CATEGORIES");
        $this->db->where('id_category = ?', (int) $category);

        return (bool) (int) ($this->db->query_one()['AGGREGATE'] ?? false);
    }

    public function is_used_video_category($category_id)
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from("{$this->video_table}");
        $this->db->where('id_category = ?', (int) $category_id);
        return (bool) (int) ($this->db->query_one()['AGGREGATE'] ?? false);
    }

    public function has_video_category($category_title, array $conditions = array())
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from("{$this->videos_categories_table} AS CATEGORIES");
        $this->db->where('CATEGORIES.category_title = ?', $category_title);

        extract($conditions, EXTR_PREFIX_ALL, 'condition');

        if (!empty($conditions)) {
            if (!empty($condition_not)) {
                $condition_not = getArrayFromString($condition_not);
                $this->db->where_raw('CATEGORIES.id_category NOT IN (' . implode(',', array_fill(0, count($condition_not), '?')) . ')', array_map('intval', $condition_not));
            }

            if (isset($condition_seller) && null !== $condition_seller) {
                $this->db->where('CATEGORIES.id_seller = ?', (int) $condition_seller);
            }
        }

        return (bool) (int) ($this->db->query_one()['AGGREGATE'] ?? false);
    }

    /**
     * Returns the video category filtered by set of conditions.
     *
     * @param int   $category_id
     * @param array $conditions
     *
     * @return null|array
     */
    public function get_video_category($category_id, array $conditions = array())
    {
        $this->db->select('*');
        $this->db->from("{$this->videos_categories_table} AS CATEGORIES");
        $this->db->where('CATEGORIES.id_category = ?', (int) $category_id);

        extract($conditions, EXTR_PREFIX_ALL, 'condition');

        if (!empty($conditions)) {
            if (isset($condition_seller)) {
                $this->db->where('CATEGORIES.id_seller = ?', (int) $condition_seller);
            }
        }

        return $this->db->query_one() ?: null;
    }

    /**
     * Returns a set of video categories filtered by provided set of praramets.
     *
     * @param array $params
     *
     * @return array[]
     */
    public function get_video_categories(array $params = array())
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
        $this->db->from("{$this->videos_categories_table} AS {$alias}");

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

    /**
     * Creates new category from th raw data.
     *
     * @param array $data
     *
     * @return bool|int
     */
    public function add_video_category(array $data)
    {
        return $this->db->insert($this->videos_categories_table, $data);
    }

    /**
     * Creates the unique video category with the provided title
     * If owner is specified, uniqueness will be checked in subset of categories which belongs to him.
     *
     * @param string   $category_title
     * @param null|int $owner_id
     *
     * @throws \App\Common\Exceptions\NotUniqueException if category title is not unique
     *
     * @return bool|int
     */
    public function create_video_category($category_title, $owner_id = null)
    {
        if ($this->has_video_category($category_title, array(
            'seller' => $owner_id,
        ))) {
            throw new NotUniqueException('The category with this name already exists.');
        }

        return $this->db->insert($this->videos_categories_table, array(
            'id_seller'      => (int) $owner_id,
            'category_title' => $category_title,
        ));
    }

    /**
     * Updates the category with the raw data.
     *
     * @param int   $category_id
     * @param array $data
     *
     * @return bool|int
     */
    public function update_video_category($category_id, array $data)
    {
        $this->db->where('id_category', (int) $category_id);
        return $this->db->update($this->videos_categories_table, $data);
    }

    /**
     * Renames video category with the unique title.
     * If owner is specified, uniqueness will be checked in subset of categories which belongs to him.
     *
     * @param int      $category_id
     * @param string   $category_title
     * @param null|int $owner_id
     *
     * @throws \App\Common\Exceptions\NotUniqueException if category title is not unique
     *
     * @return bool
     */
    public function rename_video_category($category_id, $category_title, $owner_id = null)
    {
        if ($this->has_video_category($category_title, array(
            'not'    => $category_id,
            'seller' => $owner_id,
        ))) {
            throw new NotUniqueException('The category with this name already exists.');
        }

        return (bool) $this->update_video_category($category_id, array(
            'category_title' => $category_title,
        ));
    }

    /**
     * Removes the video category by its ID.
     *
     * @param int $category_id
     *
     * @return bool
     */
    public function delete_video_category($category_id)
    {
        $this->db->where('id_category', (int) $category_id);
        return $this->db->delete($this->videos_categories_table);
    }

    /**
     * Removes video category that has no relationships with vidoes.
     *
     * @param int $category_id
     *
     * @throws \App\Common\Exceptions\DependencyException if catgory has relationships with videos
     *
     * @return bool
     */
    public function remove_video_category($category_id)
    {
        if ($this->is_used_video_category($category_id)) {
            throw new DependencyException('This category cannot be deleted because it has related videos');
        }

        return $this->delete_video_category($category_id);
    }

    public function count_video_categories(array $params = array())
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
        $this->db->from("{$this->videos_categories_table} as {$alias}");

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

    private function with_categories($table, $binding, $relation)
    {
        $this->db->join("{$this->videos_categories_table} AS CATEGORIES", "CATEGORIES.id_category = {$table}.{$binding}", 'left');
        if (is_callable($relation)) {
            $relation($this->db, $this);
        }
    }

    private function with_sellers($table, $binding, $relation)
    {
        $this->db->join("{$this->users_table} AS USERS", "USERS.idu = {$table}.{$binding}", 'left');
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
