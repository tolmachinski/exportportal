<?php

class External_feedbacks_Model extends TinyMVC_Model {

	private $item_external_reviews_table = 'item_external_reviews';
	private $user_external_feedback_table = 'user_external_feedback';

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

	public function get_external_feedbacks($conditions){
		$page = 1;
		$per_p = 10;
		$order_by = "uef.create_date DESC";
		$params = array();
		$where = array();

		extract($conditions);

		if (isset($sort_by)) {
			switch ($sort_by) {
				case 'rating_asc': $order_by = 'uef.rating ASC';break;
				case 'rating_desc': $order_by = 'uef.rating DESC';break;
				case 'date_asc': $order_by = 'uef.create_date ASC';break;
				case 'date_desc': $order_by = 'uef.create_date DESC';break;
				case 'rand': $order_by = ' RAND()';break;
			}
		}

		if(isset($id_company)){
			$where[] = " uef.id_company = ? ";
			$params[] = $id_company;
		}

		if(isset($email)){
			$where[] = " uef.email = ? ";
			$params[] = $email;
		}

		if(isset($confirm_code)){
			$where[] = " uef.confirm_code = ? ";
			$params[] = $confirm_code;
		}

		if(isset($confirmed)){
			$where[] = " uef.confirmed = ? ";
			$params[] = $confirmed;
		}

		$sql = "SELECT uef.*, cb.id_user as idu
				FROM $this->user_external_feedback_table uef
				LEFT JOIN company_base cb ON uef.id_company = cb.id_company";

		if(count($where))
			$sql .= " WHERE " . implode(" AND", $where);

		$sql .= " ORDER BY " . $order_by;

		if(isset($limit)){
			$sql .= " LIMIT " . $limit ;
		} else{
			if(!isset($start)){
				$start = ($page-1)*$per_p;

				if($start < 0) $start = 0;
			}

			$sql .= " LIMIT " . $start ;

			if($per_p > 0)
				$sql .= "," . $per_p;
		}

		return $this->db->query_all($sql, $params);
	}

	public function get_external_feedback($conditions){
		$params = array();
		$where = array();

		extract($conditions);

		if(isset($id_company)){
			$where[] = " id_company = ? ";
			$params[] = $id_company;
		}

		if(isset($email)){
			$where[] = " email = ? ";
			$params[] = $email;
		}

		if(isset($confirm_code)){
			$where[] = " confirm_code = ? ";
			$params[] = $confirm_code;
		}

		if(isset($confirmed)){
			$where[] = " confirmed = ? ";
			$params[] = $confirmed;
		}

		$sql = "SELECT *
				FROM ".$this->user_external_feedback_table;

		if(count($where))
			$sql .= " WHERE " . implode(" AND", $where);

		return $this->db->query_one($sql, $params);
	}

	public function exist_external_feedback($conditions){
		$params = array();
		$where = array();

		extract($conditions);

		if(isset($id_company)){
			$where[] = " id_company = ? ";
			$params[] = $id_company;
		}

		if(isset($email)){
			$where[] = " email = ? ";
			$params[] = $email;
		}

		if(isset($confirm_code)){
			$where[] = " confirm_code = ? ";
			$params[] = $confirm_code;
		}

		if(isset($confirmed)){
			$where[] = " confirmed = ? ";
			$params[] = $confirmed;
		}

		$sql = "SELECT COUNT(*) as counter
				FROM ".$this->user_external_feedback_table;

		if(count($where))
			$sql .= " WHERE " . implode(" AND", $where);

		$res = $this->db->query_one($sql, $params);
		return $res['counter'];
	}

	public function set_external_feedback($data){
		$this->db->insert($this->user_external_feedback_table, $data);
		return $this->db->last_insert_id();
	}

	function update_external_feedback($confirm_code, $data) {
		$this->db->where('confirm_code', $confirm_code);
		return $this->db->update($this->user_external_feedback_table, $data);
	}

	public function get_external_reviews($conditions){
		$page = 0;
		$per_p = 10;
		$order_by = "er.create_date DESC";
		$params = array();
		$where = array();

		extract($conditions);

		if (isset($sort_by)) {
			switch ($sort_by) {
				case 'rating_asc': $order_by = 'er.rating ASC';break;
				case 'rating_desc': $order_by = 'er.rating DESC';break;
				case 'date_asc': $order_by = 'er.create_date ASC';break;
				case 'date_desc': $order_by = 'er.create_date DESC';break;
				case 'rand': $order_by = ' RAND()';break;
			}
		}

		if(isset($id_company)){
			$where[] = " er.id_company = ? ";
			$params[] = $id_company;
		}

		if(isset($id_item)){
			$where[] = " er.id_item = ? ";
			$params[] = $id_item;
		}

		if(isset($email)){
			$where[] = " er.email = ? ";
			$params[] = $email;
		}

		if(isset($confirm_code)){
			$where[] = " er.confirm_code = ? ";
			$params[] = $confirm_code;
		}

		if(isset($confirmed)){
			$where[] = " er.confirmed = ? ";
			$params[] = $confirmed;
		}

		$sql = "SELECT er.*, i.title as item_name, i.id_seller
				FROM $this->item_external_reviews_table er
				LEFT JOIN items i ON er.id_item = i.id";

		if(count($where)) {
			$sql .= ' WHERE ' . implode(' AND', $where);
        }

		$sql .= ' ORDER BY ' . $order_by;

		if(isset($limit)){
			$sql .= ' LIMIT ' . $limit ;
		} else{
			if(!isset($count)) {
				$count = $this->exist_external_feedback($conditions);
            }

			$pages = ceil($count/$per_p);

			if(!isset($start)){
				if ($page > $pages) $page = $pages;
				$start = ($page-1)*$per_p;

				if($start < 0) $start = 0;
			}

			$sql .= ' LIMIT ' . $start ;

			if($per_p > 0) {
				$sql .= ',' . $per_p;
            }
		}

		return $this->db->query_all($sql, $params);
	}

	public function get_external_review($conditions){
		$params = array();
		$where = array();

		extract($conditions);

		if(isset($id_item)){
			$where[] = " id_item = ? ";
			$params[] = $id_item;
		}

		if(isset($email)){
			$where[] = " email = ? ";
			$params[] = $email;
		}

		if(isset($confirm_code)){
			$where[] = " confirm_code = ? ";
			$params[] = $confirm_code;
		}

		if(isset($confirmed)){
			$where[] = " confirmed = ? ";
			$params[] = $confirmed;
		}

		$sql = "SELECT *
				FROM ".$this->item_external_reviews_table;

		if(count($where))
			$sql .= " WHERE " . implode(" AND", $where);

		return $this->db->query_one($sql, $params);
	}

	public function exist_external_review($conditions){
		$params = array();
		$where = array();

		extract($conditions);

		if(isset($id_company)){
			$where[] = " id_company = ? ";
			$params[] = $id_company;
		}

		if(isset($id_item)){
			$where[] = " id_item = ? ";
			$params[] = $id_item;
		}

		if(isset($email)){
			$where[] = " email = ? ";
			$params[] = $email;
		}

		if(isset($confirm_code)){
			$where[] = " confirm_code = ? ";
			$params[] = $confirm_code;
		}

		if(isset($confirmed)){
			$where[] = " confirmed = ? ";
			$params[] = $confirmed;
		}

		$sql = "SELECT COUNT(*) as counter
				FROM ".$this->item_external_reviews_table;

		if(count($where))
			$sql .= " WHERE " . implode(" AND", $where);

		$res = $this->db->query_one($sql, $params);
		return $res['counter'];
	}

	public function set_external_review($data){
		$this->db->insert($this->item_external_reviews_table, $data);
		return $this->db->last_insert_id();
	}

	function update_external_review($confirm_code, $data) {
		$this->db->where('confirm_code', $confirm_code);
		return $this->db->update($this->item_external_reviews_table, $data);
	}

	public function get_all_rating_counter_review($id_item){
		$sql = "SELECT rating, COUNT(rating) as count_rating
				FROM $this->item_external_reviews_table
				WHERE id_item = $id_item AND confirmed = 1
				GROUP BY rating";
        return $this->db->query_all($sql, array($id_item));
    }
}
