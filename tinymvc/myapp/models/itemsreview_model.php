<?php
/**
 * itemsreview_model.php
 * reviews of items model
 * @author  Litra Andrei
 * @deprecated in favor of \Product_Reviews_Model
 */
class ItemsReview_Model extends TinyMVC_Model {
    // hold the current controller instance
    var $obj;
    private $review_table = 'item_reviews';
    private $reviews_helpful_table = 'item_reviews_user_helpful';
    private $user_table = 'users';
    private $item_table = 'items';

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

    public function set_review($data){
    	if(!count($data))
    		return false;

        $this->db->insert($this->review_table, $data);
        return $this->db->last_insert_id();
    }

	function get_reviews_last_id(){
		$sql = "SELECT id_review
				FROM " . $this->review_table . "
				ORDER BY id_review DESC
				LIMIT 0,1";

		$rez = $this->db->query_one($sql);

        return $rez['id_review'] ?: 0;
	}

	function get_count_new_reviews($id_review){
		$sql = "SELECT COUNT(*) as counter
				FROM " . $this->review_table . "
				WHERE id_review > ? ";

		$rez = $this->db->query_one($sql, array($id_review));
		return $rez['counter'];
	}

	public function getReview($id_review){
		$sql = "SELECT ir.*, it.id_seller
				FROM " . $this->review_table . " ir
    			LEFT JOIN " . $this->item_table . " it ON ir.id_item = it.id
				WHERE id_review = ?";
        return $this->db->query_one($sql, array($id_review));
    }

	public function update_review($id, $data){
        $this->db->where('id_review', $id);
        return $this->db->update($this->review_table, $data);
    }

	public function delete_review($id_review) {
		$this->db->where("id_review", $id_review);
        return $this->db->delete($this->review_table);
    }

    public function counterByStatus($status = null){
		$sql = "SELECT COUNT(id_review) as counter
                FROM " . $this->review_table . "
                WHERE  `rev_status` =  ?";
		$rez = $this->db->query_one($sql, array($status));
		return $rez['counter'];
	}

    public function counterByItem($id_item){
        $sql = "SELECT COUNT(id_review) as counter
				FROM " . $this->review_table . "
				WHERE	id_item = ?";
        $couters = $this->db->query_one($sql, array($id_item));
        return $couters['counter'];
    }

    public function countersByItems($id_list){
        $id_list = getArrayFromString($id_list);

        $sql = "SELECT id_item, COUNT(id_review) as counter
				FROM " . $this->review_table . "
				WHERE id_item IN (" . implode(',', array_fill(0, count($id_list), '?')) . ")"
		. " GROUP BY id_item";
        return $this->db->query_all($sql, $id_list);
    }

    public function getRaitingByItem($id_item){
        $sql = "SELECT AVG(  `rev_raiting` ) as raiting
                FROM  " . $this->review_table . "
                WHERE `id_item` = ?";

        $rez = $this->db->query_one($sql, array($id_item));

        return (null === $rez['raiting'] ? 0 : round($rez['raiting']));
    }

    public function getRatingsByItems($id_list){
        $id_list = getArrayFromString($id_list);

	    $sql = "SELECT `id_item`, ROUND(AVG(  `rev_raiting` )) as raiting
                FROM " . $this->review_table . "
                WHERE `id_item` IN (" . implode(',', array_fill(0, count($id_list), '?')) . ") " .
	       "GROUP BY `id_item`";

        return $this->db->query_all($sql, $id_list);
    }

	public function get_all_rating_counter($id_item){
		$sql = "SELECT rev_raiting as rating, COUNT(rev_raiting) as count_rating
				FROM $this->review_table
				WHERE id_item = ?
				GROUP BY rev_raiting";
        return $this->db->query_all($sql, array($id_item));
    }

	public function searchReviews($conditions){
		$per_p = 10;
		$rev_status = '';
		$order_by = "rev.rev_date DESC";
		$where = array();
		$params = array();
		$select = "";
		$inner = "";
		$order = "";
	    $function_type = 'all'; //also can be count(count by all conditions), page(numebr of page by all conditions)

		extract($conditions);

		if(!empty($rev_status)){
			$where[] = " rev.rev_status = ? ";
			$params[] = $rev_status;
		}

		if (isset($added_start)) {
			$where[] = " rev.rev_date >= ?";
			$params[] = $added_start;
		}

		if (isset($added_finish)) {
			$where[] = " rev.rev_date <= ?";
			$params[] = $added_finish;
		}

		if(isset($moderated)){
			$where[] = " rev.rev_status = ? ";
			$params[] = $moderated;
		}

		if(isset($id_user)){
			$where[] = " rev.id_user = ? ";
			$params[] = $id_user;
		}

		if(isset($id_seller)){
			$where[] = " it.id_seller = ? ";
			$params[] = $id_seller;
		}

		if(isset($id_item)){
			$where[] = " rev.id_item = ? ";
			$params[] = $id_item;
		}

		if(isset($id_order)){
			$where[] = " io.id_order = ? ";
			$params[] = $id_order;
		}

		if(isset($review_number)){
			$where[] = " rev.id_review = ? ";
			$params[] = $review_number;
		}

		if(isset($replied)) {
			if($replied == 'yes') {
				$where[] = " rev.reply != '' ";
			} else if ($replied == 'no') {
				$where[] = " (rev.reply IS NULL OR rev.reply = '') ";
			}
		}

		if(!empty($order)){
			$str = explode("_",$order);
			$ord = $str[0];
			switch($ord){
				case 'name': $order_by = " us.fname"; break;
				case 'item': $order_by = " it.title"; break;
				case 'date': $order_by = " rev_date"; break;
				default:
					$order_by = " rev.rev_date";
			}
			$order_by .= " " . $str[1];
		}

		if(!empty($sort_by)) {
			$multi_order_by = array();
			foreach ($sort_by as $sort_item) {
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		if (isset($keywords)) {
			if (str_word_count_utf8($keywords) > 1) {
				$escaped_search_string = $this->db->getConnection()->quote(trim($keywords));
				$search_parts = preg_split("/\b/", trim($escaped_search_string, "'"));
				$search_parts = array_map('trim', $search_parts);
				$search_parts = array_filter($search_parts);
				if (!empty($search_parts)) {
					$search_parts = array_values($search_parts);
					$search_condition = implode('* <', $search_parts);
					$search_condition = "{$search_condition}*";
					$where[] = " MATCH(rev.rev_title, rev.rev_text, rev.reply) AGAINST (?) ";
					$params[] = $search_condition;
				}
			} else {
				$where[] = " (
					rev.rev_title = ? OR
					rev.rev_text = ? OR
					rev.reply = ? OR
					rev.rev_title LIKE ? OR
					rev.rev_text LIKE ? OR
					rev.reply LIKE ?
				) ";
				$params = array_merge(
					$params,
					array_fill(0, 3, $keywords),
					array_fill(0, 3, "%{$keywords}%")
				);
			}
		}

		if(isset($company_details)){
			$select = " ,cb.name_company, cb.index_name, cb.id_company, cb.type_company";
			$inner = " INNER JOIN company_base cb ON cb.id_user=it.id_seller AND cb.type_company='company'";
		}

		$sql = "SELECT rev.*,
						CONCAT(us.fname, ' ', us.lname) as fullname, us.status as user_status, us.logged,
						it.title, ic.name, ic.p_or_m, io.id_order, us.status, it.id_seller, it.rating as item_ratting,
						CONCAT(sel.fname, ' ', sel.lname) as sel_fullname ,
						its.id_snapshot, its.title as snapshot_title, its.main_image as snapshot_image
						{$select}
				FROM {$this->review_table} rev
				INNER JOIN {$this->user_table} us ON rev.id_user = us.idu
				INNER JOIN {$this->item_table} it ON rev.id_item = it.id
				INNER JOIN item_category ic ON it.id_cat = ic.category_id
				INNER JOIN item_ordered io ON io.id_ordered_item = rev.id_ordered_item
				INNER JOIN item_snapshots its ON its.id_snapshot = io.id_snapshot
				INNER JOIN {$this->user_table} sel ON it.id_seller = sel.idu
				{$inner}";

		if(!empty($where)){
			$sql .= " WHERE " . implode(" AND", $where);
		}

		// $res = $this->db->query_all($sql, $params);
	    /* block for count all items */
		$count = $this->db->numRows();
		if($function_type == 'count') {
			return $count;
		}

	    /* block for count pagination */
		$pages = ceil($count/$per_p);
		if($function_type == 'pages') {
			return $pages;
		}

		if(!empty($order_by)) {
			$sql .= " ORDER BY ".$order_by;
		}

		if($page > $pages) {
			$page = $pages;
		}
		$start = ($page-1)*$per_p;
		if($start < 0) {
			$start = 0;
		}

		if(isset($limit)) {
			$sql .= ' LIMIT ' . $limit;
		} else {
			$sql .= " LIMIT " . $start . "," . $per_p;
		}

	    // $limit =  " LIMIT $start, $per_p";
	    // $sql .= $limit;//echo $sql."<br />";
		// $reviews =  $this->db->query_all($sql, $params);
		// foreach($reviews as $key => $review){
			// $reviews[$key]['reply'] = $this->getReply($review['id_review'], 'all');
		// }

		return $this->db->query_all($sql, $params);
	}

	public function countReviews($conditions){
	    $rev_status = '';
	    $where = array();
	    $params = array();

	    extract($conditions);

	    if(!empty($rev_status)){
			$where[] = " rev.rev_status = ? ";
			$params[] = $rev_status;
	    }

	    if (isset($added_start)) {
			$where[] = " DATE(rev.rev_date)>=?";
			$params[] = $added_start;
	    }

	    if (isset($added_finish)) {
			$where[] = " DATE(rev.rev_date)<=?";
			$params[] = $added_finish;
	    }


	    if(isset($moderated)){
			$where[] = " rev.rev_status = ? ";
			$params[] = $moderated;
	    }

	    if(isset($id_user)){
	        $where[] = " rev.id_user = ? ";
	        $params[] = $id_user;
	    }

	    if(isset($id_seller)){
	        $where[] = " it.id_seller = ? ";
	        $params[] = $id_seller;
	    }

	    if(isset($id_item)){
	        $where[] = " rev.id_item = ? ";
	        $params[] = $id_item;
	    }

	    if(isset($id_order)){
	        $where[] = " io.id_order = ? ";
	        $params[] = $id_order;
	    }

	    if(isset($review_number)){
	        $where[] = " rev.id_review = ? ";
	        $params[] = $review_number;
	    }

		if (isset($keywords)) {
			if (str_word_count_utf8($keywords) > 1) {
				$escaped_search_string = $this->db->getConnection()->quote(trim($keywords));
				$search_parts = preg_split("/\b/", trim($escaped_search_string, "'"));
				$search_parts = array_map('trim', $search_parts);
				$search_parts = array_filter($search_parts);
				if (!empty($search_parts)) {
					$search_parts = array_values($search_parts);
					$search_condition = implode('* <', $search_parts);
					$search_condition = "{$search_condition}*";
					$where[] = " MATCH(rev.rev_title, rev.rev_text, rev.reply) AGAINST (?) ";
					$params[] = $search_condition;
				}
			} else {
				$where[] = " (
					rev.rev_title = ? OR
					rev.rev_text = ? OR
					rev.reply = ? OR
					rev.rev_title LIKE ? OR
					rev.rev_text LIKE ? OR
					rev.reply LIKE ?
				) ";
				$params = array_merge(
					$params,
					array_fill(0, 3, $keywords),
					array_fill(0, 3, "%{$keywords}%")
				);
			}
		}

        if(isset($replied)) {
			if($replied == 'yes') {
				$where[] = " rev.reply != '' ";
			} else if ($replied == 'no') {
				$where[] = " (rev.reply IS NULL OR rev.reply = '') ";
			}
		}

	    $sql = "SELECT COUNT(*) as counter
		    	FROM {$this->review_table} rev
				INNER JOIN {$this->user_table} us ON rev.id_user = us.idu
				INNER JOIN {$this->item_table} it ON rev.id_item = it.id
				INNER JOIN item_category ic ON it.id_cat = ic.category_id
				INNER JOIN item_ordered io ON io.id_ordered_item = rev.id_ordered_item";

	    if(!empty($where)){
			$sql .= " WHERE " . implode(" AND", $where);
		}

	    $reviews = $this->db->query_one($sql, $params);
	    return $reviews['counter'];
	}

    public function getReply($id_review, $status = 'moderated'){
        $params = [$id_review];

		$sql = "SELECT rep.*,
					CONCAT(us.fname, ' ', us.lname) as fullname, us.status as user_status, us.logged
                FROM " . $this->reply_table . " rep
				INNER JOIN " . $this->user_table . " us ON rep.id_user = us.idu
				WHERE id_review = ?";

		if(in_array($status, ['new', 'moderated'])){
			$sql .= " AND rep_status = ?";
            $params[] = $status;
		}

		return $this->db->query_one($sql, $params);
	}

	public function get_item_ordered_review($id_ordered_item = 0, $type = 'snapshot'){
		switch ($type) {
			default:
			case 'snapshot':
				$sql = "SELECT
							r.*,
							i.title as item_name, i.id_seller, i.main_image as photo_name,
							u.user_photo, u.lname, u.fname,
							CONCAT(iu.fname, '', iu.lname) as seller_fullname,
							pc.country as user_country
						FROM $this->review_table r
						INNER JOIN users u ON r.id_user = u.idu
						INNER JOIN port_country pc ON u.country = pc.id
						INNER JOIN item_ordered io ON io.id_ordered_item = r.id_ordered_item
						INNER JOIN item_snapshots i ON i.id_snapshot = io.id_snapshot
						INNER JOIN users iu ON i.id_seller = iu.idu
						WHERE io.id_ordered_item = ?";
			break;
			case 'item':
				$sql = "SELECT
							r.*,
							i.title as item_name, i.id_seller, ip.photo_name,
							u.user_photo, u.lname, u.fname,
							CONCAT(iu.fname, '', iu.lname) as seller_fullname,
							pc.country as user_country
						FROM $this->review_table r
						INNER JOIN items i ON r.id_item = i.id
						INNER JOIN item_photo ip ON r.id_item = ip.sale_id AND ip.main_photo = 1
						INNER JOIN users u ON r.id_user = u.idu
						INNER JOIN users iu ON i.id_seller = iu.idu
						INNER JOIN port_country pc ON u.country = pc.id
						WHERE r.id_ordered_item = ?";
			break;
		}

        return $this->db->query_one($sql, array($id_ordered_item));
    }

	public function get_review($id_review = 0, $type = 'snapshot'){
		switch ($type) {
			default:
			case 'snapshot':
				$sql = "SELECT
							r.*,
							i.title as item_name, i.id_seller, i.main_image as photo_name,
							u.user_photo, u.lname, u.fname,
							CONCAT(iu.fname, '', iu.lname) as seller_fullname,
							pc.country as user_country
						FROM $this->review_table r
						INNER JOIN users u ON r.id_user = u.idu
						INNER JOIN port_country pc ON u.country = pc.id
						INNER JOIN item_ordered io ON io.id_ordered_item = r.id_ordered_item
						INNER JOIN item_snapshots i ON i.id_snapshot = io.id_snapshot
						INNER JOIN users iu ON i.id_seller = iu.idu
						WHERE r.id_review = ?";
			break;
			case 'item':
				$sql = "SELECT
							r.*,
							i.title as item_name, i.id_seller, ip.photo_name,
							u.user_photo, u.lname, u.fname,
							CONCAT(iu.fname, '', iu.lname) as seller_fullname,
							pc.country as user_country
						FROM $this->review_table r
						INNER JOIN items i ON r.id_item = i.id
						INNER JOIN item_photo ip ON r.id_item = ip.sale_id AND ip.main_photo = 1
						INNER JOIN users u ON r.id_user = u.idu
						INNER JOIN users iu ON i.id_seller = iu.idu
						INNER JOIN port_country pc ON u.country = pc.id
						WHERE r.id_review = ?";
			break;
		}

        return $this->db->query_one($sql, array($id_review));
    }

	public function get_user_reviews($conditions = array()){
		$page = 0;
		$order_by = "r.rev_date DESC";
        $where = array();
        $params = array();

        extract($conditions);
        if(isset($sort_by)){
			switch($sort_by){
	            case 'rating_asc': $order_by = 'r.rev_raiting ASC'; break;
	            case 'rating_desc': $order_by = 'r.rev_raiting DESC'; break;
	            case 'date_asc': $order_by = 'r.rev_date ASC'; break;
	            case 'date_desc': $order_by = 'r.rev_date DESC'; break;
	            case 'rand': $order_by = ' RAND()'; break;
	        }
		}
        if(isset($item)){
            $where[] = " r.id_item = ? ";
            $params[] = $item;
        }

        if(isset($id_ordered)){
            $where[] = " r.id_ordered_item = ? ";
            $params[] = $id_ordered;
        }
		if(isset($user)){
			$where[] = " r.id_user = ? ";
			$params[] = $user;
		}
		if(isset($id_seller)){
			$where[] = " i.id_seller = ? ";
			$params[] = $id_seller;
		}

		if(isset($id_review)){
            $id_review = getArrayFromString($id_review);
			$where[] = " r.id_review IN (" . implode(',', array_fill(0, count($id_review), '?')) . ") ";
            array_push($params, ...$id_review);
		}

		if(isset($review_status)){
			$where[] = " r.rev_status = ?";
			$params[] = $review_status;
		}

		if(isset($review_replied)){
			if($review_replied == TRUE){
				$where[] = " r.reply IS NULL ";
			} else{
				$where[] = " r.reply IS NOT NULL  ";
			}
		}

		$sql = "SELECT
					r.id_review, r.id_item, r.id_user, r.rev_title,
					r.rev_text, r.rev_date, r.reply, r.reply_date, r.rev_raiting, r.count_plus, r.count_minus, r.rev_status,
					ip.photo_name,
					i.title as item_name, i.id_seller,
					u.user_photo, u.lname, u.fname,
					CONCAT(iu.fname, '', iu.lname) as seller_fullname,
					pc.country as user_country
				FROM $this->review_table r
				LEFT JOIN items i ON r.id_item = i.id
				LEFT JOIN item_photo ip ON r.id_item = ip.sale_id AND ip.main_photo = 1
				LEFT JOIN users u ON r.id_user = u.idu
				LEFT JOIN users iu ON i.id_seller = iu.idu
				LEFT JOIN port_country pc ON u.country = pc.id";

		if(!empty($where)){
			$sql .= " WHERE " . implode(" AND", $where);
		}

		$sql .= " GROUP BY r.id_review ";
        $sql .= " ORDER BY ".$order_by;

		if(isset($per_p)){
			$pages = ceil($count/$per_p);

			if ($page > $pages) {
				$page = $pages;
			}

			$start = ($page-1)*$per_p;

			if($start < 0){
				$start = 0;
			}

			$sql .= " LIMIT " . $start ;

			if($per_p > 0){
				$sql .= "," . $per_p;
			}
		}

        return $this->db->query_all($sql, $params);
    }

	public function counter_by_conditions($conditions = array()){
		$status = "";
        $where = array();
        $params = array();

        extract($conditions);

        if(!empty($status)){
            $where[] = " r.rev_status = ? ";
            $params[] = $status;
        }

		if(isset($user)){
			$where[] = " r.id_user = ? ";
			$params[] = $user;
		}

        if(isset($item)){
			$where[] = " r.id_item = ? ";
			$params[] = $item;
		}

		if(isset($id_seller)){
			$where[] = " it.id_seller = ? ";
			$params[] = $id_seller;
		}

		if(isset($id_review)){
			$id_review = getArrayFromString($id_review);
			$where[] = " r.id_review IN (" . implode(',', array_fill(0, count($id_review), 'trim')) . ") ";
            array_push($params, ...$id_review);
		}

		if(isset($review_status)){
			$where[] = " r.rev_status = ?";
			$params[] = $review_status;
		}

		if(isset($review_replied)){
			if($review_replied == TRUE){
				$where[] = " r.reply IS NULL ";
			} else{
				$where[] = " r.reply IS NOT NULL  ";
			}
		}

		$sql = "SELECT COUNT(*) as counter
				FROM {$this->review_table} r
				INNER JOIN {$this->item_table} it ON r.id_item = it.id";

		if(!empty($where)){
			$sql .= " WHERE " . implode(" AND", $where);
		}

		$res = $this->db->query_one($sql, $params);
		return $res['counter'];
    }

	public function get_helpful_by_review($list_review, $id_user){
        $params = $list_review = getArrayFromString($list_review);
        $params[] = $id_user;

        $sql = "SELECT id_review, help
                FROM " . $this->reviews_helpful_table."
                WHERE id_review IN (" . implode(',', array_fill(0, count($list_review), '?')) . ") AND id_user = ? ";
		$rez = $this->db->query_all($sql, $params);

        return array_column($rez, 'help', 'id_review');
    }

	public function exist_helpful($id_review, $id_user){
		$sql = "SELECT count(*) as counter, help
				FROM ".$this->reviews_helpful_table."
				WHERE id_review = ? AND id_user = ?";
        return $this->db->query_one($sql, array($id_review, $id_user));
    }

	public function update_helpful($id_review, $data, $id_user){
        $this->db->where('id_review = ? AND id_user = ?',array($id_review, $id_user));
        return $this->db->update($this->reviews_helpful_table, $data);
    }

	public function delete_helpful($id_review) {
		$this->db->in("id_review", $id_review);
        return $this->db->delete($this->reviews_helpful_table);
	}

	public function remove_user_helpful($id_review, $id_user) {
		$this->db->where('id_review', $id_review);
		$this->db->where('id_user', $id_user);

        return $this->db->delete($this->reviews_helpful_table);
    }

	public function modify_counter_helpfull($id, $columns){
		$sql = "UPDATE " . $this->review_table . " SET ";
		foreach($columns as $column => $sign)
			$set[] = $column ." = ". $column." ".$sign." 1 ";

		$sql .= implode(',',$set). " WHERE id_review = ?";
		return $this->db->query($sql, array($id));
    }

	public function set_helpful($data){
		if(!count($data)) return false;
        $this->db->insert($this->reviews_helpful_table, $data);
        return $this->db->last_insert_id();
    }

    public function iWroteReview($id_user, $id_item){
        $sql = "SELECT COUNT(*) as counter
           	    FROM ".$this->review_table."
         	    WHERE id_ordered_item = ? AND id_user = ?";
       	$counter = $this->db->query_one($sql, array($id_item,$id_user));
        return $counter['counter'];
    }

    public function isReviewForUserItem($id_review, $id_user){
    	$sql = "SELECT COUNT(*) as for_user
    			FROM " . $this->review_table . " ir
    			LEFT JOIN " . $this->item_table . " it ON ir.id_item = it.id
    			WHERE ir.id_review = ? AND it.id_seller = ?";
    	$counter = $this->db->query_one($sql, array($id_review, $id_user));
    	return $counter['for_user'];
	}

    public function isReviewOfUser($id_review, $id_user){
    	$sql = "SELECT COUNT(*) as of_user
    			FROM " . $this->review_table . " ir
    			WHERE ir.id_review = ? AND ir.id_user = ?";
    	$counter = $this->db->query_one($sql, array($id_review, $id_user));
    	return $counter['of_user'];
	}

    public function moderateReviews($ids){
        $ids = getArrayFromString($ids);

        $sql = 'UPDATE ' . $this->review_table . '
                SET rev_status="moderated"
                WHERE id_review IN (' . implode(',', array_fill(0, count($ids), '?')) .')';
        return $this->db->query($sql, $ids);
    }

    public function deleteReviews($ids){
        $this->delete_helpful($ids);

        $ids = getArrayFromString($ids);
        $sql = "DELETE FROM $this->review_table
                WHERE id_review IN (" . implode(',', array_fill(0, count($ids), '?')) . ")";
        return $this->db->query($sql, $ids);
    }

	public function reviews_owner($reviews) {
		$reviews = getArrayFromString($reviews);

		$sql = "SELECT id_user
				FROM item_reviews
				WHERE id_review IN (" . implode(',', array_fill(0, count($reviews), '?')) . ")";
		return $this->db->query_all($sql, $reviews);
	}

	public function get_simple_reviews($conditions = array()){
		$this->db->select("*");
		$this->db->from("item_reviews");

        extract($conditions);

		if(isset($id_user)){
			if(!is_array($id_user)){
				$id_user = array_map("intval", explode(',', $id_user));
			}

			$this->db->in("id_user", $id_user);
		}

		if(isset($review)){
			if(!is_array($review)){
				$review = array_map("intval", explode(',', $review));
			}

			$this->db->in("id_review", $review);
		}

		if(isset($status)){
			$this->db->where("rev_status = ?", $status);
		}

        if(isset($not_replied) && $not_replied){
			$this->db->where_raw("reply IS NULL");
		}

		$records = $this->db->get();

		return !empty($records) ? $records : array();
	}

	public function get_simple_review($id_review, $conditions = array()){
		$this->db->select("*");
		$this->db->from($this->review_table);

		extract($conditions);

		$this->db->where("id_review = ?", $id_review);

        if(isset($id_user)){
			if(!is_array($id_user)){
				$id_user = array_map("intval", explode(',', $id_user));
			}

			$this->db->in("id_user", $id_user);
		}

        if(isset($status)){
            $this->db->where("rev_status = ?", $status);
        }

        if(isset($not_replied) && $not_replied){
            $this->db->where_raw("reply IS NULL");
        }

		$record = $this->db->get_one();

		return !empty($record) ? $record : array();
	}

    function get_orders_for_review($conditions=array()){
		$this->db->select("iod.*, isp.id_item, isp.title, isp.main_image");
		$this->db->from("item_ordered iod");
		$this->db->join("item_reviews ir", "iod.id_ordered_item = ir.id_ordered_item", "left outer");
		$this->db->join("item_orders io", "io.id=iod.id_order", "inner");
		$this->db->join("item_snapshots isp", "iod.id_snapshot = isp.id_snapshot", "left");

		$this->db->where("io.status = ?", 11);
		$this->db->where_raw("ir.id_review IS NULL");

		extract($conditions);

        if(isset($id_buyer)){
			$this->db->where("io.id_buyer = ?", (int) $id_buyer);
		}

        if(isset($id_seller)){
			$this->db->where("io.id_seller = ?", (int) $id_seller);
		}

        if(isset($id_item)){
			$this->db->where("isp.id_item = ?", (int) $id_item);
		}

        $records = $this->db->get();

		return !empty($records) ? $records : array();
    }

    function get_order_for_review($id_order = 0, $conditions=array()){
		$this->db->select("iod.*, isp.id_item, isp.title, isp.main_image");
		$this->db->from("item_ordered iod");
		$this->db->join("item_reviews ir", "iod.id_ordered_item = ir.id_ordered_item", "left outer");
		$this->db->join("item_orders io", "io.id=iod.id_order", "inner");
		$this->db->join("item_snapshots isp", "iod.id_snapshot = isp.id_snapshot", "left");

		$this->db->where("io.id = ?", (int) $id_order);
		$this->db->where("io.status = ?", 11);
		$this->db->where_raw("ir.id_review IS NULL");

		extract($conditions);

        if(isset($id_buyer)){
			$this->db->where("io.id_buyer = ?", (int) $id_buyer);
		}

        if(isset($id_seller)){
			$this->db->where("io.id_seller = ?", (int) $id_seller);
		}

        if(isset($id_item)){
			$this->db->where("isp.id_item = ?", (int) $id_item);
		}

        $records = $this->db->get_one();

		return !empty($records) ? $records : array();
    }

	function check_user_review($conditions=array()){
        $where = array();
        $params = array();
        extract($conditions);
        if (isset($id_buyer)) {
            $where[] = " io.id_buyer = ? ";
            $params[] = $id_buyer;
        }
        if (isset($id_seller)) {
            $where[] = " io.id_seller = ? ";
            $params[] = $id_seller;
        }
        if (isset($id_item)) {
            $id_item = getArrayFromString($id_item);
            $where[] = " iod.id_item IN (" . implode(',', array_fill(0, count($id_item), '?')) . ") ";
            array_push($params, ...$id_item);
        }
        if (isset($id_ordered_item)) {
            $id_ordered_item = getArrayFromString($id_ordered_item);
            $where[] = " iod.id_ordered_item IN (" . implode(',', array_fill(0, count($id_ordered_item), '?')) . ") ";
            array_push($params, ...$id_ordered_item);
        }
        $sql = "SELECT iod.*
                FROM item_ordered iod
                LEFT OUTER JOIN item_reviews ir ON iod.id_ordered_item = ir.id_ordered_item
                INNER JOIN item_orders io ON io.id=iod.id_order
				WHERE io.status = 11
					AND ir.id_review is null ";
        if (!empty($where)) {
            $sql .= " AND " . implode(" AND ", $where);
        }

		return $this->db->query_all($sql, $params);
    }
}
