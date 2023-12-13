<?php

class Shipper_Countries_Model extends TinyMVC_Model {

    private $port_country_table = 'port_country';
    private $shipper_countries_table = 'shipper_countries';

    function get_shipper_countries($conditions = array()){
		$order_by = " c.country ASC ";
		extract($conditions);

		$this->db->select("*");
		$this->db->from("{$this->shipper_countries_table} sc");
		$this->db->join("{$this->port_country_table} c", "sc.id_country = c.id", "inner");

		if (isset($id_user)) {
			$this->db->where("id_user = ?", (int) $id_user);
		}

		$this->db->orderby($order_by);

        return $this->db->query_all() ?: [];
	}

	function exist_shipper_countries($id_user){
		$this->db->select("COUNT(*) as total_rows");
		$this->db->from($this->shipper_countries_table);
		$this->db->where("id_user = ?", (int) $id_user);

		return (int) $this->db->query_one()['total_rows'];
    }

	function delete_shipper_countries_by_user($id_user) {
		$this->db->where('id_user', $id_user);
		return $this->db->delete($this->shipper_countries_table);
	}

	function set_shipper_countries($data = array()) {
		if(empty($data)){
            return;
		}

		$this->db->insert_batch($this->shipper_countries_table, $data);

		return $this->db->getAffectableRowsAmount();
	}

	function worldwide_shipper_countries($id_user){
		$this->db->select("COUNT(*) as total_rows");
		$this->db->from($this->shipper_countries_table);
		$this->db->where("id_user = ?", (int) $id_user);
		$this->db->where("id_country = ?", 0);

		return (int) $this->db->query_one()['total_rows'];
	}

	function get_shippers_by_country($conditions = array()){
        $visible = 1;
		$order_by = " os.co_name ";

		extract($conditions);

		$where = $params = [];

		if (isset($countries_list)) {
            $countries_list = getArrayFromString($countries_list);
			$where[] = " sc.id_country IN (" . implode(',', array_fill(0, count($countries_list), '?')) . ") ";
            array_push($params, ...$countries_list);
		}

		if (isset($shippers_list)) {
            $shippers_list = getArrayFromString($shippers_list);
			$where[] = " os.id IN (" . implode(',', array_fill(0, count($shippers_list), '?')) . ") ";
            array_push($params, ...$shippers_list);
		}

		if($visible != 'all'){
			$where[] = " os.visible = ? ";
			$params[] = $visible;
		}

		$sql = "SELECT os.*
				FROM $this->shipper_countries_table sc
				LEFT JOIN $this->orders_shippers_table os ON sc.id_user = os.id_user";

		if (!empty($where)) {
			$sql .= ' WHERE ' . implode(' AND ' , $where);
        }

		$sql .= " GROUP BY os.id ORDER BY {$order_by}";

		return $this->db->query_all($sql, $params);
	}
}
