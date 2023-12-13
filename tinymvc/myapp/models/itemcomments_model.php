<?php
/**
 * itemqcomments_model.php
 *
 * item's comments system model
 *
 * @author Litra Andrei
 */


class ItemComments_Model extends TinyMVC_Model {

    // hold the current controller instance
    private $obj;
    private $items_table = "items";
    private $categories_table = "item_category";
    private $photos_table = "item_photo";
    private $comments_table = "item_comments";
    private $user_table = "users";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

	public function comment_map($array = array()){
		$tree = array();
		if (!empty($array)) {
			foreach($array as $item)
				$ds[$item['id_comm']] = $item;

			foreach($ds as $key => &$node){
				if($node['reply_to_comm'] == 0)
					$tree[$key] = &$node;
				else
					$ds[$node['reply_to_comm']]['replies'][$key] = &$node;
			}
		}

		//print_r($tree);
		return $tree;
	}

	public function setComment(array $data){
        if (empty($data)) {
        	return false;
        }

        $this->db->insert($this->comments_table, $data);
        return $this->db->last_insert_id();
    }

	function get_items_comments_last_id(){
		$sql = "SELECT id_comm
				FROM " . $this->comments_table . "
				ORDER BY id_comm DESC
				LIMIT 0,1";

		$rez = $this->db->query_one($sql);

		if(!empty($rez['id_comm']))
			return $rez['id_comm'];
		else
			return 0;
	}

	function get_count_new_items_comments($id_comment){
		$sql = "SELECT COUNT(*) as counter
				FROM " . $this->comments_table . "
				WHERE id_comm > ? ";

		$rez = $this->db->query_one($sql, array($id_comment));
		return $rez['counter'];
	}

    public function getComment($id_comm){
        $sql = "SELECT  c.*,
						CONCAT(u.fname, ' ', u.lname) as username, u.idu, u.user_photo,
						pc.country, ug.gr_name as user_group,
						it.title, it.id_seller, ic.name, ic.p_or_m
				FROM $this->comments_table c
				LEFT JOIN $this->user_table u ON c.id_user = u.idu
				LEFT JOIN port_country pc ON pc.id = u.country
				LEFT JOIN user_groups ug ON u.user_group = ug.idgroup
				LEFT JOIN $this->items_table it ON c.id_item = it.id
				LEFT JOIN item_category ic ON it.id_cat = ic.category_id
                WHERE id_comm = ?";
        return $this->db->query_one($sql, array($id_comm));
    }

    public function is_comment_exists($comment_id, $author_id = null)
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from("{$this->comments_table} AS VIDEOS");
        $this->db->where('id_comm = ?', (int) $comment_id);

        if (!empty($author_id)) {
            $this->db->where('id_user = ?', (int) $author_id);
        }

        $counter = $this->db->query_one();
        if (!$counter || empty($counter)) {
            return false;
        }

        return isset($counter['AGGREGATE']) ? (bool) (int) $counter['AGGREGATE'] : false;
    }

    public function get_comment_simple($id_comm, $additional_conditions = array()) {
        $this->db->from($this->comments_table);
        $this->db->where('id_comm', $id_comm);

        if ( ! empty($additional_conditions)) {
            if ( ! empty($additional_conditions['id_item'])) {
                $this->db->where('id_item', $additional_conditions['id_item']);
            }

            if ( ! empty($additional_conditions['id_user'])) {
                $this->db->where('id_user', $additional_conditions['id_user']);
            }
        }

        return $this->db->query_one();
    }

    public function get_comments_to_delete($list_id_comm, $result){
        $list_id_comm = getArrayFromString($list_id_comm);

        $sql = "SELECT GROUP_CONCAT(id_comm) as list_id
        		FROM item_comments
                WHERE reply_to_comm IN (" . implode(',', array_fill(0, count($list_id_comm), '?')) . ")";
        $rez = $this->db->query_one($sql, $list_id_comm);
        if(!empty($rez['list_id'])){
        	$new_result = $result .','.$rez['list_id'];
        	return $this->get_comments_to_delete($rez['list_id'], $new_result);
		}else{
			return $result;
		}
    }

    public function isMyComment($id_comment, $id_user){
        $sql = "SELECT COUNT(*) as counter
                FROM " . $this->comments_table . "
                WHERE id_comm = ? AND id_user = ?";
        $counter = $this->db->query_one($sql, array($id_comment, $id_user));
        return $counter['counter'];
    }

	public function get_comments_owners($comments_list) {
		$comments_list = getArrayFromString($comments_list);

		$sql = "SELECT id_user
				FROM " . $this->comments_table . "
				WHERE id_comm IN (" . implode(',', array_fill(0, count($comments_list), '?')) . ")";
		return $this->db->query_all($sql, $comments_list);

	}

    public function isItemComment($id_comment, $id_item){
        $sql = "SELECT COUNT(*) as counter
                FROM " . $this->comments_table . "
                WHERE id_comm = ? AND id_item = ?";
        $counter = $this->db->query_one($sql, array($id_comment, $id_item));
        return $counter['counter'];
    }

    public function get_comments($conditions = array()){
		$map_tree = true;
		$page = 1;
		$status = "";
		$order_by = "c.comment_date DESC";
        $where = array();
        $params = array();
		$rel = "";

		extract($conditions);

        if(isset($id_comment)){
            $where[] = " c.id_comm = ? ";
            $params[] = $id_comment;
        }

		if(isset($added_after_time)){
            $where[] = " c.comment_date >= ? ";
            $params[] = $added_after_time;
        }

		if (isset($added_before_time)) {
			$where[] = " c.comment_date <= ?";
			$params[] = $added_before_time;
		}

        if(isset($item)){
            $where[] = " c.id_item = ? ";
            $params[] = $item;
        }

		if(isset($reply_to_comm)){
			$where[] = ' reply_to_comm = ? ';
			$params[] = $reply_to_comm;
		}

		if(isset($user)){
			$where[] = " c.id_user = ? ";
			$params[] = $user;
		}

        if(!empty($status)){
            $status = getArrayFromString($status);
            $where[] = " c.status IN (" . implode(',', array_fill(0, count($status), '?')) . ") ";
            array_push($params, ...$status);
        }

		if(isset($seller)){
			$where[] = " it.id_seller = ? ";
			$params[] = $seller;
		}

		if(isset($parent)){
			$where[] = " c.reply_to_comm = ? ";
			$params[] = $parent;
		}

		if(isset($general_comment)){
            $general_comment = getArrayFromString($general_comment);
			$where[] = " c.general_comment IN (" . implode(',', array_fill(0, count($general_comment), '?')) . ") ";
            array_push($params, ...$general_comment);
		}

		if (isset($keywords)) {
	    	$where[] = " MATCH(comment) AGAINST (?)";
            $params[] = $keywords;
        }

        if(isset($order)){
            $str = explode("_",$order);
            $ord = $str[0];
            switch($ord){
                case 'username': $order_by = " username"; break;
                case 'item': $order_by = " it.title"; break;
                case 'date':
				default: $order_by = " c.comment_date"; break;
            }
            $order_by .= " " . $str[1];
        }

		if(!empty($keywords)){
			if(str_word_count_utf8($keywords) > 1){
				$order_by = $order_by . ", REL DESC";
				$where[] = " MATCH(c.comment) AGAINST ( ? ) ";
				$params[] = $keywords;
			} else{
				$where[] = " c.comment LIKE ? ";
                $params[] = '%' . $keywords . '%';
			}
        }

		$sql = "SELECT  c.*,
						CONCAT(u.fname, ' ', u.lname) as username, u.idu, u.user_photo, u.fname, u.lname, u.`status` as user_status,
						pc.country, ug.gr_name as user_group, ug.idgroup,
						it.title, iph.photo_name, ic.name, ic.p_or_m
			FROM $this->comments_table c
			INNER JOIN $this->user_table u ON c.id_user = u.idu
			INNER JOIN port_country pc ON pc.id = u.country
			INNER JOIN user_groups ug ON u.user_group = ug.idgroup
			INNER JOIN $this->items_table it ON c.id_item = it.id
			INNER JOIN item_category ic ON it.id_cat = ic.category_id
			LEFT JOIN item_photo iph ON c.id_item = iph.sale_id AND iph.main_photo = 1 ";

		if(count($where)) $sql .= " WHERE " . implode(" AND", $where);

		$sql .= " GROUP BY c.id_comm ";

        $sql .= " ORDER BY ".$order_by.", reply_to_comm ASC";

		if(isset($per_p)){
			if(!isset($count))
				$count = $this->count_comments($conditions);

			$pages = ceil($count/$per_p);
	        if($page > $pages)
	            $page = $pages;
	        $start = ($page-1)*$per_p;
	        if($start < 0)
	            $start = 0;

	        $sql .= " LIMIT " . $start ;

			if($per_p > 0)
				$sql .= "," . $per_p;
		}
		if(isset($limit))
			$sql .= " LIMIT " . $limit;

        $rez = $this->db->query_all($sql, $params);
		if($map_tree)
			$rez = $this->comment_map($rez);

        return $rez;
    }

    public function count_comments($conditions = array()){
		$status = "";
        $where = array();
        $params = array();

        extract($conditions);

        if(isset($item)){
            $where[] = " c.id_item = ? ";
            $params[] = $item;
        }

		if(isset($reply_to_comm)){
			$where[] = 'reply_to_comm = ?';
			$params[] = $reply_to_comm;
		}

		if(isset($user)){
			$where[] = " c.id_user = ? ";
			$params[] = $user;
		}

		if(isset($added_after_time)){
				$where[] = " c.comment_date >= ? ";
				$params[] = $added_after_time;
			}


		if (isset($added_before_time)) {
			$where[] = " c.comment_date <= ?";
			$params[] = $added_before_time;
		}

		if(!empty($status)){
            $status = getArrayFromString($status);
			$where[] = " c.status IN (" . implode(',', array_fill(0, count($status), '?')) . ") ";
            array_push($params, ...$status);
		}

		if(isset($seller)){
			$where[] = " it.id_seller = ? ";
			$params[] = $seller;
		}

		if(!empty($keywords)){
			$words = explode(' ', $keywords);
			if(count($words) > 1){
				$where[] = " MATCH(c.comment) AGAINST ( ? ) ";
				$params[] = $keywords;
			} else{
				$where[] = " c.comment LIKE ? ";
                $params[] = '%' . $keywords . '%';
			}
        }

		$sql = "SELECT COUNT(*)	as counter
			FROM " . $this->comments_table . " c
			INNER JOIN " . $this->items_table . " it ON c.id_item = it.id";

		if(count($where))
			$sql .= " WHERE " . implode(" AND", $where);

		$res = $this->db->query_one($sql, $params);

		return $res['counter'];
	}

    public function updateComment($id_comm, $data){
        $this->db->where('id_comm', $id_comm);
        return $this->db->update($this->comments_table, $data);
    }

    public function delete_comment($list_id_comm){
        $list_id_comm = getArrayFromString($list_id_comm);
		$sql = "DELETE FROM ".$this->comments_table."
				WHERE id_comm IN (" . implode(',', array_fill(0, count($list_id_comm), '?')) . ")";
        return $this->db->query($sql, $list_id_comm);
    }

    public function moderate_comments($ids){
        $ids = getArrayFromString($ids);
        $sql = 'UPDATE ' . $this->comments_table . '
			SET status="moderated"
			WHERE id_comm IN (' . implode(',', array_fill(0, count($ids), '?')) .')';

        return $this->db->query($sql, $ids);
    }

    public function get_field($name_field, $conditions){
        $where = array();
        $params = array();

        extract($conditions);

        if(isset($id_comm)){
            $where[] = "id_comm = ?";
            $params[] = $id_comm;
        }

        $sql = "SELECT " . $name_field . " FROM " . $this->comments_table .
            " as c INNER JOIN " . $this->items_table . " as it ON "
            . " it.id_item=c.id_item";
        if(count($where)) {
            $sql .= implode(" AND ", $where);
        }

        return $this->db->query($sql, $params);
    }

    public function get_comments_my($conditions)
    {
		$order_by = " ic.comment_date DESC ";

		extract($conditions);

        $start = (int) ($start ?? 0);
        $per_p = (int) ($per_p ?? 20);

        $where = $params = [];

        if (isset($user)) {
            $where = array(" ic.id_user = ? ");
		    $params[] = $user;
        }

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
            }

            if(!empty($multi_order_by)) {
                $order_by = implode(',', $multi_order_by);
            }
		}

		if(isset($keywords)){
            $where[] = " MATCH (ic.comment) AGAINST (?)";
			$params[] = $keywords;
        }

        if(isset($date_from)){
            $where[] = " ic.comment_date >= ? ";
            $params[] = formatDate($date_from, 'Y-m-d H:i:s');
        }

		if(isset($date_to)){
            $where[] = " ic.comment_date <= ? ";
            $params[] = formatDate($date_to, 'Y-m-d H:i:s');
        }

		if(isset($item)){
            $where[] = " ic.id_item = ? ";
            $params[] = $item;
        }

		if(isset($status)){
            $where[] = " ic.status = ? ";
            $params[] = $status;
        }

		if(isset($comment_number)){
			$where[] = " ic.id_comm = ? ";
			$params[] = $comment_number;
        }

        if (!empty($id_seller)) {
            $where[] = " i.id_seller = ? ";
			$params[] = $id_seller;
        }

        if (!empty($not_id_user)) {
            $where[] = " ic.id_user != ? ";
		    $params[] = $not_id_user;
        }

        if (isset($has_replies)) {
            $cond = " icr.nr_replies is NULL ";

            if ($has_replies) {
                $cond = " icr.nr_replies > 0 ";
            }

            $where[] = $cond;
        }

		$sql = "SELECT ic.*, icr.nr_replies,
                    i.id as item_id, i.title as item_title,
                    ict.name as category_name, ict.p_or_m, ict.breadcrumbs as item_breadcrumbs,
                    TRIM(CONCAT(u.fname, ' ', u.lname)) as username,
                    ip.photo_name
				FROM {$this->comments_table} ic
                LEFT JOIN (
                    SELECT id_comm, reply_to_comm, COUNT(*) AS nr_replies
                    FROM item_comments
                    GROUP BY reply_to_comm
                ) icr ON ic.id_comm = icr.reply_to_comm
				LEFT JOIN users u ON u.idu=ic.id_user
				LEFT JOIN items i ON i.id=ic.id_item
                LEFT JOIN item_category ict ON i.id_cat = ict.category_id
                LEFT JOIN item_photo ip ON ip.sale_id=ic.id_item AND ip.main_photo = 1
                ";

        if(count($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

		$sql .= " ORDER BY ".$order_by;
        $sql .= ' LIMIT ' . $start . ',' . $per_p;

		return $this->db->query_all($sql, $params);
	}

    public function get_comments_my_count($conditions)
    {
		extract($conditions);

        $where = $params = [];

		if (isset($user)) {
            $where = array(" ic.id_user = ? ");
		    $params[] = $user;
        }

		if(isset($keywords)){
            $where[] = " MATCH (ic.comment) AGAINST (?)";
			$params[] = $keywords;
        }

        if(isset($date_from)){
            $where[] = " ic.comment_date >= ? ";
            $params[] = formatDate($date_from, 'Y-m-d H:i:s');
        }

		if(isset($date_to)){
            $where[] = " ic.comment_date <= ? ";
            $params[] = formatDate($date_to, 'Y-m-d H:i:s');
        }

		if(isset($status)){
            $where[] = " ic.status = ? ";
            $params[] = $status;
        }

		if(isset($item)){
            $where[] = " ic.id_item = ? ";
            $params[] = $item;
        }

		if(isset($comment_number)){
			$where[] = " ic.id_comm = ? ";
			$params[] = $comment_number;
        }

        if (!empty($id_seller)) {
            $where[] = " i.id_seller = ? ";
			$params[] = $id_seller;
        }

        if (!empty($not_id_user)) {
            $where[] = " ic.id_user != ? ";
		    $params[] = $not_id_user;
        }

        $sql = "SELECT COUNT(*) as counter FROM ".$this->comments_table." ic LEFT JOIN items i ON i.id=ic.id_item";

        if (isset($has_replies)) {
            $cond = " icr.nr_replies is NULL ";

            if ($has_replies) {
                $cond = " icr.nr_replies > 0 ";
            }

            $where[] = $cond;

            $sql = "SELECT COUNT(*) as counter FROM {$this->comments_table} ic
                        LEFT JOIN items i ON i.id=ic.id_item
                        LEFT JOIN (
                            SELECT id_comm, reply_to_comm, COUNT(*) AS nr_replies
                            FROM item_comments
                            GROUP BY reply_to_comm
                        ) icr ON ic.id_comm = icr.reply_to_comm";
        }

        if(count($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $temp = $this->db->query_one($sql, $params);

		return $temp['counter'];
    }

    public function has_replies($comment)
    {
        if(empty($comment)) {
            return false;
        }

        $this->db->select('COUNT(IC.`reply_to_comm`) as `AGGREGATE`');
        $this->db->from("{$this->comments_table} as IC");
        $this->db->where('IC.reply_to_comm > ?', 0);
        $this->db->where('IC.id_comm = ?', (int) $comment);
        if(!($data = $this->db->query_one())) {
            return false;
        }

        return (bool) $data['AGGREGATE'];
    }

    public function get_comment_replies($comment, $limit = null, $offset = null)
    {
        if(empty($comment) || !is_numeric($comment)) {
            return array();
        }

        $this->db->select("
            IC.*,
            TRIM(CONCAT(U.fname, ' ', U.lname)) as `username`, U.user_photo,
            I.id as item_id, I.title,
            C.name as category_name, C.breadcrumbs as category_breadcrumbs, C.p_or_m,
            P.photo_name
        ");
        $this->db->from("{$this->comments_table} as IC");
        $this->db->join("{$this->user_table} U", "U.idu = IC.id_user", 'left');
        $this->db->join("{$this->items_table} I", "I.id = IC.id_item", 'left');
        $this->db->join("{$this->categories_table} C", "C.category_id = I.id_cat", 'left');
        $this->db->join("{$this->photos_table} P", "P.sale_id = IC.id_item", 'left');
        $this->db->where('IC.reply_to_comm = ?', (int) $comment);
        $this->db->orderby('IC.comment_date ASC');
        $this->db->groupby('IC.id_comm');
        if(null !== $limit) {
            $this->db->limit((int) $limit, $offset);
        }
        if(!($data = $this->db->query_all())) {
            return array();
        }

        return $data;
    }
}
