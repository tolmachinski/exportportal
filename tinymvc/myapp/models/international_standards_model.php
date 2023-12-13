<?php

/**
 * International_standards_Model.php
 *
 * International Standards Model
 *
 * @author Cravciuc Andrei
 */
class International_standards_Model extends TinyMVC_Model {

	// HOLD THE CURRENT CONTROLLER INSTANCE
	var $obj;
	private $international_standards_table = "international_standards";
	private $international_standards_regions_table = "international_standards_regions";
	private $port_country_table = "port_country";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

    // INSERT STANDARD
	public function insert_standard($data = array()){
        if(empty($data)){
            return false;
        }

		$this->db->insert($this->international_standards_table, $data);
		return $this->db->last_insert_id();
	}

    // UPDATE STANDARD
	public function update_standard($id_standard, $data = array()){
        if(empty($data)){
            return false;
        }

        $this->db->where('id_standard', $id_standard);
		return $this->db->update($this->international_standards_table, $data);
	}

	// DELETE STANDARD
	public function delete_standard($id_standard){
        $this->db->where('id_standard', $id_standard);
		return $this->db->delete($this->international_standards_table);
	}

	// GET STANDARD BY ID
	public function get_standard($id_standard){
        $sql = "SELECT *
                FROM $this->international_standards_table
                WHERE id_standard = ?";

        return $this->db->query_one($sql, array($id_standard));
	}

    // GET STANDARDS BY CONDITIONS
    public function get_standards($conditions = array()){
        $where = array();
        $params = array();
		$per_p = 10;
        $order_by = " ist.id_standard DESC ";
		$rel = "";
        extract($conditions);

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		if(isset($standards_list)){
			$where[] = " ist.id_standard IN (" . implode(',', array_fill(0, count($standards_list), '?')) . ") ";
            array_push($params, ...$standards_list);
		}

		if(isset($id_country)){
			$where[] = " ist.standard_country = ? ";
			$params[] = $id_country;
		}

        if(!empty($keywords)){
			if(str_word_count_utf8($keywords) > 1){
				$order_by = " id_standard DESC ";
				$where[] = " MATCH (ist.standard_title) AGAINST (?)";
				$params[] = $keywords;
			} else{
				$where[] = " ist.standard_title LIKE ?";
                $params[] = '%' . $keywords . '%';
			}
        }

        $sql = "SELECT ist.*, pc.country
                FROM $this->international_standards_table ist
				LEFT JOIN $this->port_country_table pc ON ist.standard_country = pc.id";

        if(!empty($where)){
            $sql .= " WHERE " . implode(" AND ", $where);
        }

		$sql .= " ORDER BY " . $order_by;

		if(isset($start))
			$sql .= " LIMIT $start, $per_p ";

		if(isset($page)){
			$start = ($page == 1) ? 0 : ($page * $per_p) - $per_p;
			$sql .= " LIMIT $start, $per_p ";
		}

        return $this->db->query_all($sql, $params);
    }

    // COUNT STANDARDS BY CONDITIONS
    public function count_standards($conditions = array()){
        $where = array();
        $params = array();
        extract($conditions);

		if(isset($standards_list)){
			$where[] = " id_standard IN (" . implode(',', array_fill(0, count($standards_list), '?')) . ") ";
            array_push($params, ...$standards_list);
		}

		if(isset($id_region)){
			$where[] = " standard_region = ? ";
			$params[] = $id_region;
		}

		if(isset($id_country)){
			$where[] = " standard_country = ? ";
			$params[] = $id_country;
		}

        if(isset($keywords)){
			$words = explode(' ', $keywords);
			if(count($words) > 1){
				$where[] = " MATCH(standard_title) AGAINST ( ? ) ";
				$params[] = $keywords;
			} else{
				$where[] = " standard_title LIKE ? ";
                $params[] = '%' . $keywords . '%';
			}
        }

        $sql = "SELECT COUNT(*) as counter
                FROM $this->international_standards_table";

        if(!empty($where)){
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $res = $this->db->query_one($sql, $params);
        return $res['counter'];
    }

    // ADDITIONAL QUERIES
    function get_countries(){
        $sql = "SELECT *
                FROM $this->port_country_table
				ORDER BY country ASC";
        return $this->db->query_all($sql);
    }

    function get_country($id_country){
        $sql = "SELECT *
                FROM $this->port_country_table
				WHERE id = ?";
        return $this->db->query_one($sql, array($id_country));
    }

	function get_used_countries($conditions = array()){
		$where = array();
        $params = array();

        extract($conditions);

		if(isset($exclude_countries)){
            $exclude_countries = getArrayFromString($exclude_countries);
			$where[] = " pc.id NOT IN(" . implode(',', array_fill(0, count($exclude_countries), '?')) . ")";
            array_push($params, ...$exclude_countries);
		}

        $sql = "SELECT pc.*
                FROM $this->port_country_table pc
				INNER JOIN $this->international_standards_table ist ON pc.id = ist.standard_country";

		if(!empty($where))
            $sql .= " WHERE " . implode(" AND ", $where);

		$sql .= " GROUP BY pc.id
				ORDER BY pc.country ";

        return $this->db->query_all($sql, $params);
	}
}

