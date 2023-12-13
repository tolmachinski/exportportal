<?php
/**
 * import_companies_model.php
 * users model
 * @author Cravciuc Andrei
 */

class Admin_import_Model extends TinyMVC_Model {

	// hold the current controller instance
	var $obj;
	private $users_table = "users";
	private $temp_import_table = "temp_import";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

    function get_import_data($conditions = array()){
        $where = array();
        $params = array();
        $order_by = '';
        extract($conditions);

        if (isset($sort_by)) {
            foreach ($sort_by as $sort_item) {
                $sort_item = explode('-', $sort_item);
                $multi_order_by[] = $sort_item[0] . ' ' . $sort_item[1];
            }
            $order_by = implode(',', $multi_order_by);
        }

        if (isset($id_import)) {
            $where[] = " id = ? ";
            $params[] = $id_import;
        }

        if (isset($imports_list)) {
            $imports_list = getArrayFromString($imports_list);
            $where[] = " id IN (" . implode(',', array_fill(0, count($imports_list), '?')) . ") ";
            array_push($params, ...$imports_list);
        }

        if (isset($type)) {
            $where[] = " type = ? ";
            $params[] = $type;
        }

        if (isset($status)) {
            $where[] = " status = ? ";
            $params[] = $status;
        }

        if (isset($start_date)) {
            $where[] = " date >= ? ";
            $params[] = $start_date;
        }

        if (isset($finish_date)) {
            $where[] = " date <= ? ";
            $params[] = $finish_date;
        }

		$sql = "SELECT *
				FROM $this->temp_import_table";

        if (count($where))
            $sql .= " WHERE " . implode(" AND ", $where);

        if ($order_by)
            $sql .= " ORDER BY " . $order_by;

        if($per_p > 0)
            $sql .= " LIMIT " . $start . ', ' . $per_p;

        return $this->db->query_all($sql, $params);
	}

    function get_import_data_count($conditions = array()){
        $where = array();
        $params = array();

        extract($conditions);

        if (isset($id_import)) {
            $where[] = " id = ? ";
            $params[] = $id_import;
        }

        if (isset($type)) {
            $where[] = " type = ? ";
            $params[] = $type;
        }

        if (isset($status)) {
            $where[] = " status = ? ";
            $params[] = $status;
        }

        if (isset($start_date)) {
            $where[] = " date >= ? ";
            $params[] = $start_date;
        }

        if (isset($finish_date)) {
            $where[] = " date <= ? ";
            $params[] = $finish_date;
        }

		$sql = "SELECT COUNT(*) as import_count
				FROM $this->temp_import_table";

        if (count($where))
            $sql .= " WHERE " . implode(" AND ", $where);

        $result = $this->db->query_one($sql, $params);
        return $result['import_count'];
	}

    function get_import($id_import){
		$sql = "SELECT *
				FROM $this->temp_import_table
                WHERE id = ?";
		return $this->db->query_one($sql, array($id_import));
	}

    function update_import_data($id_import, $data = array()){
        if(empty($data)){
            return false;
        }

		$this->db->where('id', $id_import);
		return $this->db->update($this->temp_import_table , $data);
	}
}

