<?php
/**
*blog.php
*
*blog model
*
*@deprecated in favor of \Blogs_Model
*/
class Blog_Model extends TinyMVC_Model {

	private $blogs_table = 'blogs';
	private $blogs_category_table = 'blogs_category';
	private $blogs_category_i18n_table = 'blogs_category_i18n';
	private $items_table = 'items';
	private $users_table = 'users';
	private $port_country_table = 'port_country';

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

	public function set_blog($data = array()){
		if(empty($data)){
			return false;
		}

		$this->db->insert($this->blogs_table, $data);
        return $this->db->last_insert_id();
	}

    function get_cr_countries() {
        $sql = 'SELECT
                    id_country,
                    country_name
                FROM port_country pc
                    INNER JOIN cr_domains crd
                        ON crd.id_country = pc.id';

		return $this->db->query_all($sql);
    }

	function get_blogs_last_id(){
		$sql = 'SELECT id
				FROM ' . $this->blogs_table .'
				ORDER BY id DESC
				LIMIT 1';

		$rez = $this->db->query_one($sql);

		if(!empty($rez)) {
			return $rez['id'];
        } else {
			return 0;
        }
	}

	public function get_count_new_blogs($blogId)
    {
		$rez = $this->db->query_one("SELECT COUNT(*) as counter FROM {$this->blogs_table} WHERE id > ?", [$blogId]);

		return $rez['counter'];
	}

	public function get_blog($id_blog){
        $sql = "SELECT
                    b.*,
                    IF(b.lang != 'en' AND bc_i18n.id_category_i18n IS NOT NULL, bc_i18n.name , bc.name) as category_name,
                    IF(b.lang != 'en' AND bc_i18n.id_category_i18n IS NOT NULL, bc_i18n.url , bc.url) as category_url,
                    CONCAT_WS(' ', u.fname, u.lname) as user_name
				FROM {$this->blogs_table} b
                    LEFT JOIN {$this->blogs_category_table} bc
                        ON b.id_category = bc.id_category
                    LEFT JOIN {$this->blogs_category_i18n_table} bc_i18n
                        ON bc.id_category = bc_i18n.id_category AND bc_i18n.lang_category = b.lang
                    LEFT JOIN {$this->users_table} u
                        ON b.id_user = u.idu
				WHERE id = ? ";

		return $this->db->query_one($sql, array($id_blog));
	}

	public function get_public_blog($id_blog, $preview = false){
		$sql = "SELECT
					b.*,
                    bc.special_link as category_special_link,
					IF(b.lang != 'en' AND bc_i18n.id_category_i18n IS NOT NULL, bc_i18n.name , bc.name) as category_name,
                    IF(b.lang != 'en' AND bc_i18n.id_category_i18n IS NOT NULL, bc_i18n.url , bc.url) as category_url,
					u.user_photo, u.showed_status, CONCAT_WS(' ', u.fname, u.lname) as user_name
				FROM {$this->blogs_table} b
                    LEFT JOIN {$this->blogs_category_table} bc
                        ON b.id_category = bc.id_category
                    LEFT JOIN {$this->blogs_category_i18n_table} bc_i18n
                        ON bc.id_category = bc_i18n.id_category AND bc_i18n.lang_category = b.lang
                    LEFT JOIN {$this->users_table} u
                        ON b.id_user = u.idu
				WHERE id = ?";
		if(!$preview) {
            $sql .= ' AND b.visible=1
					  AND b.status="moderated"
					  AND b.published=1';
        }

		return $this->db->query_one($sql, array($id_blog));
	}

	public function get_blog_by_conditions($conditions){
		$where = array();
		$params = array();
		$order_by = 'date DESC';

		extract($conditions);

		if(isset($blog)){
			$where[] = ' b.id = ? ';
			$params[] = $blog;
		}

		if(isset($user)){
			$where[] = ' b.id_user = ? ';
			$params[] = $user;
		}

		if(isset($category)){
			$where[] = ' b.id_category = ? ';
			$params[] = $category;
		}

		if(isset($status)){
			$where[] = ' b.status = ? ';
			$params[] = $status;
		}

		if(isset($start_from)){
			$where[] = ' DATE(date) >= ?';
			$params[] = $start_from;
		}

		if(isset($start_to)){
			$where[] = ' DATE(date) <= ?';
			$params[] = $start_to;
		}

        $sql = "SELECT
                    b.*,
                    bc.name as category_name
				FROM {$this->blogs_table} b
                LEFT JOIN {$this->blogs_category_table} bc
                    ON b.id_category = bc.id_category";

		if(count($where)) {
			$sql .= ' WHERE ' . implode(' AND ', $where);
        }

		return $this->db->query_one($sql, $params);
	}

    public function get_blogs($conditions = array()) {
        $where = array();
        $params = array();
        $order_by = 'date DESC';

        extract($conditions);

        $page = (int) ($page ?? 0);
        $per_p = (int) ($per_p ?? 20);
        $start = (int) ($start ?? 0);

        if(isset($sort_by)){
            foreach($sort_by as $sort_item){
                $sort_item = explode('-', $sort_item);
                $multi_order_by[] = $sort_item[0].' '.$sort_item[1];
            }
            if(!empty($multi_order_by)) {
                $order_by = implode(',', $multi_order_by);
            }
        }

        if(isset($user)){
            $where[] = ' b.id_user = ? ';
            $params[] = $user;
        }

        if(isset($id_country)){
            $where[] = ' b.id_country = ? ';
            $params[] = $id_country;
        }

        if(isset($category)){
            $where[] = ' b.id_category = ? ';
            $params[] = $category;
        }

        if(isset($country)){
            $where[] = ' b.id_country = ? ';
            $params[] = $country;
        }

        if(isset($tags)){
            $where[] = ' b.tags LIKE ? ';
            $params[] = '%' . $tags . '%';
        }

        if(isset($archived)){
            list($month, $year) = explode('-',$archived);
            $where[] = ' YEAR(b.date) = ? ';
            $params[] = $year;

            $where[] = ' MONTH(b.date) = ? ';
            $params[] = $month;
        }

        if(isset($status)){
            $where[] = ' b.status = ? ';
            $params[] = $status;
        }

        if(isset($start_from)){
            $where[] = ' DATE(date) >= ? ';
            $params[] = $start_from;
        }

        if(isset($start_to)){
            $where[] = ' DATE(date) <= ? ';
            $params[] = $start_to;
        }

        if(isset($visible)){
            $where[] = ' b.visible = ? ';
            $params[] = $visible;
        }

        if(isset($published)){
            $where[] = ' b.published = ? ';
            $params[] = $published;
        }

        if(isset($publish_on)){
            $where[] = ' DATE(b.publish_on) = ? ';
            $params[] = $publish_on;
        }

        if(isset($publish_from)){
            $where[] = ' DATE(b.publish_on) >= ? ';
            $params[] = $publish_from;
        }

        if(isset($publish_to)){
            $where[] = ' DATE(b.publish_on) <= ? ';
            $params[] = $publish_to;
        }

        if(isset($id_blog)){
            $where[] = ' b.id = ? ';
            $params[] = $id_blog;
        }

        if(isset($lang)){
            $where[] = ' b.lang = ? ';
            $params[] = $lang;
        }

        if(isset($not_id_blog)){
            $where[] = ' b.id != ? ';
            $params[] = $not_id_blog;
        }

        if(isset($blog_list)){
            $blog_list = getArrayFromString($blog_list);
            $where[] = " b.id IN (" . implode(',', array_fill(0, count($blog_list), '?')) . ") ";
            array_push($params, ...$blog_list);
        }

        if(isset($keywords)){
            $temp_words = explode(' ', $keywords);
            $temp_where = array();
            foreach ($temp_words as $temp_word) {
                $temp_word = trim($temp_word);
                if (mb_strlen($temp_word) >= 3) {
                    $temp_where[] = " title LIKE ?
                                    OR short_description LIKE ?
                                    OR content LIKE ?
                                    OR tags LIKE ? ";
                    array_push($params, ...array_fill(0, 4, '%' . $temp_word . '%'));
                }
            }

            if(!empty($temp_where)){
                $where[] = ' (' . implode(' OR ', $temp_where) . ') ';
            }
        }

        $sql = "SELECT
                    b.*,
                    IF(b.lang != 'en' AND bc_i18n.id_category_i18n IS NOT NULL, bc_i18n.name , bc.name) as category_name,
                    IF(b.lang != 'en' AND bc_i18n.id_category_i18n IS NOT NULL, bc_i18n.url , bc.url) as category_url,
                    pc.country,
                    u.fname,
                    u.lname
                FROM {$this->blogs_table} b
                    LEFT JOIN port_country pc
                        ON b.id_country = pc.id
                    LEFT JOIN {$this->blogs_category_table} bc
                        ON b.id_category = bc.id_category
                    LEFT JOIN {$this->blogs_category_i18n_table} bc_i18n
                        ON bc.id_category = bc_i18n.id_category AND b.lang = bc_i18n.lang_category
                    LEFT JOIN ".$this->users_table." u
                        ON b.id_user = u.idu";

        if(count($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' GROUP BY b.id ORDER BY ' . $order_by;

        $start = $start < 0 ? 0 : $start;
        $sql .= ' LIMIT ' . $start ;

        if($per_p > 0) {
            $sql .= ',' . $per_p;
        }

        return $this->db->query_all($sql, $params);
    }

    public function get_all_posts()
    {
        return $this->db->get($this->blogs_table);
    }

	public function get_blogs_by_date($conditions){
		$where = array();
		$params = array();

		extract($conditions);

        $page = (int) ($page ?? 0);
		$per_p = (int) ($per_p ?? 20);

		if(isset($visible)){
			$where[] = ' b.visible = ? ';
			$params[] = $visible;
		}

		if(isset($status)){
			$where[] = " b.status = ? ";
			$params[] = $status;
		}

        $sql = "SELECT
                    count(*) as counter,
                    YEAR(b.date) as blog_year,
                    MONTHNAME(b.date) as month_name,
                    DATE_FORMAT( b.date, '%m') as blog_month
				FROM {$this->blogs_table} b";

		if(count($where)) {
			$sql .= ' WHERE ' . implode(' AND ', $where);
        }

		$sql .= ' GROUP BY YEAR(b.date) DESC, MONTH(b.date) ASC ';

		if(!isset($count)) {
			$count = $this->counter_by_conditions($conditions);
        }

		$pages = ceil($count/$per_p);

		if(!isset($start)){
            if ($page > $pages) {
                $page = $pages;
            }
			$start = ($page - 1) * $per_p;

            if($start < 0) {
                $start = 0;
            }
		}

		$sql .= ' LIMIT ' . $start ;

		if($per_p > 0) {
			$sql .= ',' . $per_p;
        }

		return $this->db->query_all($sql, $params);
	}

	public function get_blogs_by_author($author_id, $not_id = ''){
        $sql = "SELECT
                    b.id,
                    b.title,
                    b.date
				FROM {$this->blogs_table} b
                WHERE b.id_user = ?
                    AND b.status = 'moderated'
                    AND visible=1 "
				. (empty($not_id) ? '' : 'AND b.id <> ?') . "
                ORDER BY b.date DESC
                LIMIT 5";

		return $this->db->query_all($sql, array_filter([$author_id, (int) ($not_id ?: 0)]));
	}

	public function get_blogs_by_category($id_category, $limit = 3)
	{
		$this->db->from($this->blogs_table);
		$this->db->where('id_category', $id_category);
		$this->db->where('status', 'moderated');
		$this->db->where('visible', 1);
		$this->db->orderby('date DESC');
		$this->db->limit((int) $limit);

		return $this->db->query_all();
	}

	public function counter_by_conditions($conditions){
		$where = array();
		$params = array();
        $select = '';
		extract($conditions);

		if(isset($user)){
			$where[] = ' id_user = ? ';
			$params[] = $user;
		}

		if(isset($category)){
			$where[] = ' id_category = ? ';
			$params[] = $category;
		}

		if(isset($country)){
			$where[] = ' id_country = ? ';
			$params[] = $country;
		}

		if(isset($tags)){
			$where[] = ' MATCH (tags) AGAINST (?)';
			$params[] = $tags;
		}

		if(isset($keywords)){
			$temp_words = explode(' ', $keywords);
			$temp_where = array();
			foreach ($temp_words as $temp_word) {
				$temp_word = trim($temp_word);
				if(mb_strlen($temp_word) >= 3){
                    $temp_where[] = " title LIKE ?
                                    OR short_description LIKE ?
                                    OR content LIKE ?
                                    OR tags LIKE ? ";

                    array_push($params, ...array_fill(0, 4, '%' . $temp_word . '%'));
				}
			}

			if(!empty($temp_where)){
				$where[] = ' ('.implode(' OR ', $temp_where).') ';
			}
		}

		if(isset($archived)){
			list($month, $year) = explode('-',$archived);
			$where[] = ' YEAR(date) = ? ';
			$params[] = $year;
			$where[] = ' MONTH(date) = ? ';
			$params[] = $month;
		}

		if(isset($status)){
			$where[] = ' status = ? ';
			$params[] = $status;
		}

		if(isset($start_from)){
			$where[] = ' DATE(date) >= ?';
			$params[] = $start_from;
		}

		if(isset($start_to)){
			$where[] = ' DATE(date) <= ?';
			$params[] = $start_to;
		}

		if(isset($status_count)){
			$select = ',status ';
			$group_by = 'status';
			$all_blog = 1;
		}

		if(isset($visible)){
			$where[] = ' visible = ? ';
			$params[] = $visible;
		}

		if(isset($published)){
			$where[] = ' published = ? ';
			$params[] = $published;
		}

		if(isset($publish_from)){
			$where[] = ' DATE(publish_on) >= ? ';
			$params[] = $publish_from;
		}

		if(isset($publish_to)){
			$where[] = ' DATE(publish_on) <= ? ';
			$params[] = $publish_to;
		}

		if(isset($id_blog)){
			$where[] = ' id = ? ';
			$params[] = $id_blog;
		}

		if(isset($not_id_blog)){
			$where[] = ' id != ? ';
			$params[] = $not_id_blog;
		}

		if(isset($lang)){
			$where[] = ' lang = ? ';
			$params[] = $lang;
		}

        if(isset($blog_list)){
            $blog_list = getArrayFromString($blog_list);
            $where[] = " id IN (" . implode(',', array_fill(0, count($blog_list), '?')) . ") ";
            array_push($params, ...$blog_list);
        }

        $sql = "SELECT
                    COUNT(*) as counter
                    {$select}
				FROM {$this->blogs_table}";

		if(count($where)) {
			$sql .= ' WHERE ' . implode(' AND ', $where);
        }

		if(isset($group_by)) {
			$sql .= ' GROUP BY ' . $group_by;
        }

		if (isset($all_blog) && $all_blog){
			return $this->db->query_all($sql, $params);
        }

        $rez = $this->db->query_one($sql, $params);
        return $rez['counter'];
	}

	public function update_blog($id_blog = 0, $data = array(), $update_elasticBlogs = true){
		if(empty($data)){
			return false;
		}

		$this->db->where('id', $id_blog);
		$rez =  $this->db->update($this->blogs_table, $data);

		if($update_elasticBlogs){
			$this->obj->load->model('Elasticsearch_Blogs_Model', 'elasticBlogModel');
			$this->obj->elasticBlogModel->index($id_blog);
		}

		return $rez;
	}

	public function update_blogs_by_user($id_user, $data){
		$this->db->where('id_user', $id_user);
		return $this->db->update($this->blogs_table, $data);
	}

	public function change_published_status($id_blog = false, $update_elasticBlogs = true) {

		$this->db->where('published', 0);
		$this->db->where('publish_on', date('Y-m-d'));

		if($id_blog !== false){
			$this->db->where('id', (int) $id_blog);
		}

		$rez = $this->db->update($this->blogs_table, array('published' => 1));

		if($update_elasticBlogs){
			$this->obj->load->model('Elasticsearch_Blogs_Model', 'elasticBlogModel');
			$this->obj->elasticBlogModel->change_published_status();
		}

		return $rez;
    }

	public function delete_blog($id_blog) {
		$this->db->where('id', $id_blog);
		$rez =  $this->db->delete($this->blogs_table);

        $this->obj->load->model('Elasticsearch_Blogs_Model', 'elasticBlogModel');
        $this->obj->elasticBlogModel->delete($id_blog);

        return $rez;
	}

	public function get_count_blog_by_category(){
        $sql = "SELECT
                    bc.id_category,
                    bc.name,
                    COUNT(*) as counter
				FROM {$this->blogs_table} b
                LEFT JOIN {$this->blogs_category_table} bc
                    ON b.id_category = bc.id_category
                WHERE b.visible=1
                    AND b.status='moderated'
				GROUP BY b.id_category
				ORDER BY bc.name";
		return $this->db->query_all($sql);
    }

    public function get_category($category_id, $lang_code = null)
    {
        if(null === $lang_code || empty($lang_code) || 'en' === $lang_code) {
            return $this->get_blog_category($category_id);
        }

        return $this->get_blog_category_i18n(array('id_category' => $category_id, 'lang_category' => $lang_code));
    }

	public function get_blog_category($id_category = 0){
		$sql = "SELECT *
				FROM {$this->blogs_category_table}
				WHERE id_category = ?";

		return $this->db->query_one($sql, array($id_category));
	}

    public function get_blog_category_special_link($special_link){
        $sql = "SELECT *
                FROM {$this->blogs_category_table}
                WHERE special_link = ?";

        return $this->db->query_one($sql, array($special_link));
    }

    public function getBlogCategoryByName($name) {
        $sql = "SELECT *
        FROM {$this->blogs_category_table}
        WHERE `name` = ?";

        return $this->db->query_one($sql, [$name]);
    }

	public function get_blog_category_i18n($conditions = array()){
		if(empty($conditions)){
			return false;
		}

		$where = array();
		$params = array();

		extract($conditions);

		if(isset($id_category_i18n)){
			$where[] = ' c_i18n.id_category_i18n = ? ';
			$params[] = $id_category_i18n;
		}

		if(isset($id_category)){
			$where[] = ' c_i18n.id_category = ? ';
			$params[] = $id_category;
		}

		if(isset($lang_category)){
			$where[] = ' c_i18n.lang_category = ? ';
			$params[] = $lang_category;
		}

        $sql = "SELECT
                    c_i18n.*,
                    c.translations_data
				FROM {$this->blogs_category_i18n_table} c_i18n
                    LEFT JOIN {$this->blogs_category_table} c
                        ON c_i18n.id_category = c.id_category
				WHERE " . implode(' AND ', $where);

		return $this->db->query_one($sql, $params);
	}

	public function get_blog_categories($conditions = array()){
		$where = array();
		$params = array();
		$order_by = 'name ASC';

		extract($conditions);

        $page = (int) ($page ?? 0);
        $per_p = (int) ($per_p ?? 20);

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

        if(isset($translated_in)) {
            $where[] = "JSON_CONTAINS_PATH(translations_data, 'one', ?)";
            $params[] = "$.{$translated_in}";
        }

        if(isset($en_updated_from)) {
            $where[] = 'STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(translations_data, \'$.en.updated_at\')), "%Y-%c-%d") >= DATE(?)';
            $params[] = $en_updated_from;
        }

        if(isset($en_updated_to)) {
            $where[] = 'STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(translations_data, \'$.en.updated_at\')), "%Y-%c-%d") <= DATE(?)';
            $params[] = $en_updated_to;
        }

        if(isset($not_translated_in)) {
            $where[] = "NOT JSON_CONTAINS_PATH(translations_data, 'one', ?)";
            $params[] = "$.{$not_translated_in}";
        }

        $sql = "SELECT
                    *,
                    STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(bct.translations_data, '$.en.updated_at')), '%Y-%c-%d %H:%i:%s') as updated_at
                FROM {$this->blogs_category_table} bct";

		if(count($where)) {
			$sql .= ' WHERE ' . implode(' AND ', $where);
        }

        if (isset($cat_list)) {
            $cat_list = getArrayFromString($cat_list);
            $sql .= " WHERE bct.id_category IN ( " . implode(',', array_fill(0, count($cat_list), '?')) . " ) ";
            array_push($params, ...$cat_list);
        }

		$sql .= ' ORDER BY ' . $order_by;

		$pages = ceil($count/$per_p);

		if(!isset($start)) {
            if ($page > $pages) {
                $page = $pages;
            }

			$start = ($page - 1) * $per_p;

            if($start < 0) {
                $start = 0;
            }
		}

		$sql .= ' LIMIT ' . $start ;

		if($per_p > 0) {
			$sql .= ',' . $per_p;
        }

		return $this->db->query_all($sql, $params);
	}

	public function get_blog_categories_i18n(array $conditions = array()){
		$where = array();
		$params = array();
		$order_by = 'name ASC';
		$lang_category = __SITE_LANG;
		$use_lang = true;

		extract($conditions);

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		if($use_lang){
			$where[] = ' lang_category = ?';
			$params[] = $lang_category;
		}

		if(isset($id_category)){
			$where[] = ' id_category = ?';
			$params[] = $id_category;
		}

        $sql = "SELECT *
                FROM {$this->blogs_category_i18n_table}";

		if(count($where)) {
			$sql .= ' WHERE ' . implode(' AND ', $where);
        }

		$sql .= ' ORDER BY ' . $order_by;

		return $this->db->query_all($sql, $params);
	}

	public function counter_by_blog_categories($conditions){
		$where = array();
		$params = array();

		extract($conditions);

        $sql = "SELECT
                    COUNT(*) as counter
                FROM {$this->blogs_category_table}";

        if(isset($en_updated_from)) {
            $where[] = 'STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(translations_data, \'$.en.updated_at\')), "%Y-%c-%d") > DATE(?)';
            $params[] = $en_updated_from;
        }

        if(isset($en_updated_to)) {
            $where[] = 'STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(translations_data, \'$.en.updated_at\')), "%Y-%c-%d") <= DATE(?)';
            $params[] = $en_updated_to;
        }

		if(count($where)) {
			$sql .= ' WHERE ' . implode(' AND ', $where);
        }

		if(isset($group_by)) {
			$sql .= ' GROUP BY ' . $group_by;
        }

		$rez = $this->db->query_one($sql, $params);
		return $rez['counter'];
	}

	public function set_category_blog($data){
		$this->db->insert($this->blogs_category_table, $data);
		return $this->db->last_insert_id();
	}

	public function set_category_blog_i18n($data){
		$this->db->insert($this->blogs_category_i18n_table, $data);
		return $this->db->last_insert_id();
	}

	public function update_category_blog($id_category, $data){
		$this->db->where('id_category', $id_category);
		$result = $this->db->update($this->blogs_category_table, $data);

        if($result) {
			if(isset($data['name'])){
				$this->obj->load->model('Elasticsearch_Blogs_Model', 'elasticBlogsModel');
				$this->obj->elasticBlogsModel->update_category_blog($id_category, $data['name']);
			}
        }

        return $result;
	}

	public function update_category_blog_i18n($id_category_i18n, $data){
		$this->db->where('id_category_i18n', $id_category_i18n);
		$result = $this->db->update($this->blogs_category_i18n_table, $data);

        if($result && isset($data['id_category'])) {
            $this->obj->load->model('Elasticsearch_Blogs_Model', 'elasticBlogsModel');
            $this->obj->elasticBlogsModel->update_category_blog($data['id_category'], $data['name'], $data['lang_category']);
        }

        return $result;
	}

	public function delete_category_blog($id_category) {
		$this->db->where('id_category', $id_category);
        if(!$this->db->delete($this->blogs_category_table)) {
            return false;
        };

        $this->db->where('id_category', $id_category);
        return $this->db->delete($this->blogs_category_i18n_table);
	}

	public function get_blog_tags(){
        $sql = 'SELECT
                    GROUP_CONCAT(tags SEPARATOR ",") as all_tags
                FROM ' . $this->blogs_table . '
                WHERE visible=1
                    AND status="moderated"';

		return $this->db->query_one($sql);
	}

	function increment_blog_views($id) {
        $sql = 'UPDATE ' . $this->blogs_table . ' SET views = views + 1 WHERE id = ' . $id . ' LIMIT 1';
        $this->db->query($sql);
    }
}
