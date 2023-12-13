<?php

class Auto_Extend_Model extends TinyMVC_Model {

  // hold the current controller instance
  var $obj;
  private $auto_extends_table = "auto_extends";

  public function __construct(TinyMVC_PDO $connectionHandler)
  {
      parent::__construct($connectionHandler);

      $this->obj = tmvc::instance()->controller;
  }

  public function set_extend_request($data){
    $this->db->insert($this->auto_extends_table, $data);
    return $this->db->last_insert_id();
  }

  public function delete_extend_request($id_auto_extend){
    $this->db->where('id_auto_extend', $id_auto_extend);
    $this->db->delete($this->auto_extends_table);
  }

  public function delete_extend_request_by_item($id_item){
    $this->db->where('id_item', $id_item);
    $this->db->delete($this->auto_extends_table);
  }

  public function get_extend_request($id_request){
    $sql = "SELECT *
            FROM " .$this->auto_extends_table. "
        WHERE id_auto_extend = ?";
        return $this->db->query_one($sql, array($id_request));
  }

  public function get_extend_requests_by_condition($conditions = array()){
    $where = array();
		$params = array();
		$order_by = " date_create DESC ";

    extract($conditions);

    if(isset($id_auto_extend)){
			$where[] = " id_auto_extend = ? ";
			$params[] = $id_auto_extend;
    }

    if(isset($id_item)){
			$where[] = " id_item = ? ";
			$params[] = $id_item;
    }

    if(isset($status_buyer)){
			$where[] = " status_buyer = ? ";
			$params[] = $status_buyer;
    }

    if(isset($status_seller)){
			$where[] = " status_seller = ? ";
			$params[] = $status_seller;
    }

    if(isset($remain_hours)){
			$where[] = " TIMESTAMPDIFF(HOUR,CURRENT_TIMESTAMP(),date_order_expired) <= ? ";
			$params[] = $remain_hours;
		}

    $sql = "SELECT *
            FROM " .$this->auto_extends_table;

    if(!empty($where))
			$sql .= " WHERE " . implode(" AND ", $where);

    $sql .= " ORDER BY " . $order_by;

    if (isset($limit)) {
			$sql .= " LIMIT " . $limit;
    }

		return $this->db->query_all($sql, $params);
  }

  public function get_extend_request_by_order($id_order){
    $sql = "SELECT *
            FROM " .$this->auto_extends_table. "
        WHERE id_item = ?";
        return $this->db->query_one($sql, array($id_order));
  }

  public function update_extend_request($id_request, $data = array()) {
    if(empty($data))
      return;

    $this->db->where('id_auto_extend', $id_request);
    return $this->db->update($this->auto_extends_table, $data);
  }
}


