<?php
/**
*Prototype_model.php
*
*Prototype model
*
*@author
*
*@deprecated in favor of \Prototypes_Model
*/

class Prototype_Model extends TinyMVC_Model
{
	private $prototype_table = 'item_prototype';

	public function get_prototype($id_prototype, array $conditions = array()){
        extract($conditions);

        $where = ['id_prototype = ?'];
        $params = [$id_prototype];

		if (isset($seller)) {
			$where[] = " id_seller = ? ";
            $params[] = $seller;
		}

		if (isset($buyer)) {
			$where[] = " id_buyer = ? ";
            $params[] = $buyer;
		}

		$sql = "SELECT * FROM {$this->prototype_table}";

		if (!empty($where)) {
        	$sql .= " WHERE " . implode(" AND", $where);
        }

		return $this->db->query_one($sql, $params);
	}

	public function set_prototype($data){
		return $this->db->insert($this->prototype_table, $data);
	}

	public function update_prototype($id_prototype, $data){
        $this->db->where('id_prototype', $id_prototype);
        return $this->db->update($this->prototype_table, $data);
    }

	public function change_prototype_log($id_prototype, $data){
		$sql = "UPDATE {$this->prototype_table}
				SET log = CONCAT_WS(',', log, ?)
				WHERE id_prototype = ?";

		return $this->db->query($sql, [$data, $id_prototype]);
	}

	public function is_my_prototype($id_prototype, $conditions){
        extract($conditions);

		$where = ['id_prototype = ?'];
        $params = [$id_prototype];

		if (isset($seller)) {
			$where[] = " id_seller = ? ";
            $params[] = $seller;
		}

		if (isset($buyer)) {
			$where[] = " id_buyer = ? ";
            $params[] = $seller;
		}

		$sql = "SELECT COUNT(*) as counter FROM {$this->prototype_table}";

		if (!empty($where)) {
        	$sql .= " WHERE " . implode(" AND", $where);
        }

		return $this->db->query_one($sql, $params)['counter'];
	}
}
