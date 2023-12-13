<?php
/**
 * banner_model.php
 *
 * banner model
 *
 * @author
 */

class Banner_Model extends TinyMVC_Model {

    // hold the current controller instance
    var $obj;
    private $_table = "banner";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

    function create_banner($results){
	   return $this->db->insert('banner', $results);
	}

	function get_all_banner() {
        $this->db->query("SELECT * FROM banner ORDER BY id ASC");

        if($this->db->numRows() > 0) {
            while($row = $this->db->next()) {
                $results[] = array('id' => $row['id'],
                                   'name' => $row['name'],
								   'type' => $row['type'],
								   'link' => $row['link'],
								   'banner' => $row['banner']);
            }

            return $results;
        }
        else return null;
    }

	function delete_banner($id)
	{
	    $this->db->where('id', $id);
        return $this->db->delete($this->_table);
	}

	function validate_banner_id($id) {
        $id = (int) $id;

        $this->db->query("SELECT * FROM banner WHERE id = ?", [$id]);

        return $this->db->numRows() > 0;
    }

    public function get_banners($conditions = [])
    {
        $params = [];
		$order_by = 'b.id ASC';
        $limit = 10;

		extract($conditions);

		if (isset($sort_by)) {
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
        }

        $sql = "SELECT
                    b.*,
                    b_type.type_name
                FROM {$this->_table} b
                LEFT JOIN `banner_type` b_type
                    ON b.type = b_type.id
                ORDER BY {$order_by}";

		if ($limit) {
			$sql .= ' LIMIT ' . $limit;
        }

		return $this->db->query_all($sql, $params);
    }

    public function count($conditions)
    {
		$where = array();
        $params = array();
		$lang = __SITE_LANG;

        extract($conditions);

        $sql = "SELECT COUNT(*) as counter
                FROM {$this->_table}";

		if(!empty($where)) {
			$sql .= ' WHERE ' . implode(' AND ', $where);
        }

		$rez = $this->db->query_one($sql,$params);
		return $rez['counter'];
    }

    public function get_banner($id){

        $sql = "SELECT b.*, b_type.type_name
                FROM {$this->_table} b
                LEFT JOIN `banner_type` b_type
                    ON b.type = b_type.id
                WHERE b.id = ?";

        return $this->db->query_one($sql, array($id));
    }

    public function get_types()
    {
        $sql = "SELECT *
        FROM `banner_type`";

        return $this->db->query_all($sql);
    }

	function update_banner($id,$results)
	{
        $this->db->where('id', $id);

        return $this->db->update('banner', $results);
	}
}

