<?php
/**
 * cache_config_model.php
 *
 * banner model
 *
 * @author
 */

class Cache_Config_Model extends TinyMVC_Model {

	var $obj;
	private $config_table = "cache_config";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

	function add_cache_config($data){
        return $this->db->insert($this->config_table, $data);
    }

    function update_cache_config($id, $data){
        $this->db->where('id_config', $id);
        return $this->db->update($this->config_table, $data);
	}

	function delete_cache_config($id){
		$this->db->where('id_config', $id);
        return $this->db->delete($this->config_table);
	}

    function check_key($key, $id = null){
        $params = [$key];

        $sql = "SELECT COUNT(*) as counter
				FROM " . $this->config_table . "
				WHERE cache_key = ?";

		if (!is_null($id)) {
			$sql .= " AND id_config != ?";
            $params[] = $id;
        }

        $rez = $this->db->query_one($sql, $params);
		return $rez['counter'];
	}

	function get_cache_config($id){
		$sql = "SELECT *
				FROM " . $this->config_table . "
				WHERE id_config = ?";
		return $this->db->query_one($sql, array($id));
	}

	function get_cache_options($key){
		$sql = "SELECT *
				FROM " . $this->config_table . "
				WHERE cache_key = ?";
		return $this->db->query_one($sql, array($key));
	}

	function get_cache_configs($conditions){
		$params = array();
		$where = array();

		extract($conditions);

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		if (isset($list_id)) {
            $list_id = getArrayFromString($list_id);
			$where[] = " id_config IN (" . implode(',', array_fill(0, count($list_id), '?')) . ")";
            array_push($params, ...$list_id);
        } elseif (isset($id)) {
			$where[] = " id_config = ?";
			$params[] = $id;
		}

		$sql = "SELECT *
				FROM " . $this->config_table;

		if(count($where))
		    $sql .= " WHERE " . implode(" AND", $where);

        if (!empty($order_by)) {
            $sql .= " ORDER BY {$order_by} ";
        }

        if (isset($limit)) {
            if (isset($start_from)) {
                $start_from = (int) $start_from;
                $limit = (int) $limit;
                $sql .= " LIMIT {$start_from}, {$limit} ";
            } else {
                $sql .= " LIMIT {$limit} ";
            }
        }

		return $this->db->query_all($sql, $params);
	}

	function count_cache_configs() {
        $this->db->select("COUNT(*) AS counter");

        $result = $this->db->get_one($this->config_table);

        return (int) $result['counter'];
	}
}
