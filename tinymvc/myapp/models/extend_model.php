<?php
/**
 * orders_model.php
 *
 * orders model
 *
 * @author Andrei Litra
 */

class Extend_Model extends TinyMVC_Model {

    // hold the current controller instance
    var $obj;
    private $extends_table = "extends";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

    public function set_extend_request($data){
		$this->db->insert($this->extends_table, $data);
        return $this->db->last_insert_id();
    }

    public function delete_extend_request($id_extend){
		$this->db->where('id_extend', $id_extend);
		$this->db->delete($this->extends_table);
    }

    public function get_extend_request($id_request){
		$sql = "SELECT *
		        FROM " .$this->extends_table. "
				WHERE id_extend = ?";
        return $this->db->query_one($sql, array($id_request));
    }
}


