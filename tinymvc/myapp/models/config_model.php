<?php

/**
 * @deprecated 2.27.0 in favor of \Configs_Model
 */
class Config_Model extends TinyMVC_Model {

	var $obj;
	private $config_table = "config";
	private $my_config_file;

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
		$this->my_config_file = tmvc::instance()->getContainer()->getParameter('kernel.build_dir') . '/customConfigs.php';
	}

	function get_configs($conditions = array()){
		$where = array();
		$params = array();
		$order_by = ' key_config ASC ';
		extract($conditions);

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		if(isset($keywords)){
			$where[] = " (description LIKE ? OR value LIKE ? OR key_config LIKE ?) ";
            array_push($params, ...array_fill(0, 3, '%' . $keywords . '%'));
		}

		$sql = "SELECT key_config, value, description
				FROM {$this->config_table}";

		if(!empty($where)){
			$sql .= " WHERE " . implode(" AND", $where);
		}

		$sql .= " ORDER BY {$order_by}";
		if(isset($start, $per_p)){
            $start = (int) $start;
            $per_p = (int) $per_p;
			$sql .= " LIMIT {$start}, {$per_p}";
		}

		return $this->db->query_all($sql, $params);
	}

	function get_configs_count($conditions = array()){
		$where = array();
		$params = array();

		extract($conditions);

		if(isset($keywords)){
			$where[] = " (description LIKE ? OR value LIKE ? OR key_config LIKE ?) ";
            array_push($params, ...array_fill(0, 3, '%' . $keywords . '%'));
		}

		$sql = "SELECT COUNT(*) as counter
				FROM {$this->config_table}";

		if(!empty($where)){
			$sql .= " WHERE " . implode(" AND", $where);
		}

		$temp = $this->db->query_one($sql, $params);
		return $temp['counter'];
	}

	public function set_config($data){
		global $tmvc;

		$this->db->insert($this->config_table, $data);

		is_array($tmvc->my_config) ?  $tmvc->my_config[$data['key_config']] = $data['value'] : $tmvc->my_config = array($data['key_config'] => $data['value']);
		$this->save_in_file();
		return true;
	}

	public function get_config($key){
		$sql = "SELECT *
				FROM " . $this->config_table . "
				WHERE key_config = ?";
		return $this->db->query_one($sql, array($key));
	}

	public function exist_config($value){
		$sql = "SELECT count(*) as exist
				FROM " . $this->config_table . "
				WHERE key_config = ?";
		$rez = $this->db->query_one($sql, array($value));
		return $rez['exist'];
	}

	public function update_config($key, $data){
		global $tmvc;
		$this->db->where('key_config', $key);
		is_array($tmvc->my_config) ?
			$tmvc->my_config[$key] = $data['value'] :
			$tmvc->my_config = array($key => $data['value']);
		$this->save_in_file();
		return $this->db->update($this->config_table, $data);
	}

	public function delete_config($key){
		global $tmvc;
		if(is_array($tmvc->my_config))
			unset($tmvc->my_config[$key]);
		$this->save_in_file();
		$this->db->where('key_config', $key);
		return $this->db->delete($this->config_table);
	}

	public function save_in_file(){
		global $tmvc;
		$f = fopen($this->my_config_file, "w");
		fwrite($f, '<?php return  ' . var_export($tmvc->my_config, true) . ';');
		fclose($f);
	}
}
