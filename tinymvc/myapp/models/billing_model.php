<?php
/**
 * billing_model.php
 *
 * billing model
 *
 * @author Litra Andrei
 * @deprecated in favor of \Bills_Model
 */
class Billing_Model extends TinyMVC_Model {
    private $bills_table = 'users_bills';
    private $bills_table_alias = 'USER_BILLS';
    private $bills_table_primary_key = 'id_bill';
    public $external_bills_table = 'external_bills_requests';

    /**
     * Returns the billing table name.
     *
     * @return string
     */
    public function get_billing_table() : string
    {
        return $this->bills_table;
    }

    /**
     * Returns the billing table alias.
     *
     * @return string
     */
    public function get_billing_table_alias() : string
    {
        return $this->bills_table_alias;
    }

    /**
     * Returns the billing table primary key.
     *
     * @return string
     */
    public function get_billing_table_primary_key() : string
    {
        return $this->bills_table_primary_key;
    }

    public function setOperation($data) {
        $this->db->insert('billing', $data);
        return $this->db->last_insert_id();
    }

    public function getOperation($op){
        $sql = "SELECT * FROM billing WHERE idop = ?";
        return $this->db->query_one($sql, array($op));
    }

    public function getInitOperationByUser($user){
        $sql = "SELECT *
                FROM billing
                WHERE user = ?
                AND status = 'init'";
        return $this->db->query_one($sql, array($user));
    }

    public function getOperations($conditions) {
        $where = array();
        $params = array();
        $function_type = 'all'; //also can be page(numebr of page by all conditions)
        $order_by = " op_date DESC";
        extract($conditions);

        $page = (int) ($page ?? 1);
        $per_p = (int) ($per_p ?? 2);

        if(isset($user)){
            $where[] = " user = ?";
            $params[] = $user;
        }
        if(isset($start_date)){
            $where = " op_date >= ?";
            $params[] = $start_date;
        }
        if(isset($end_date)){
            $where = " op_date <= ?";
            $params[] = $end_date;
        }

        $sql = "SELECT bil.*, CONCAT(us.fname, ' ', us.lname) as fullname, us.email, us.registration_date, gr.gr_name
                FROM billing bil
                INNER JOIN users us ON bil.user = us.idu
                INNER JOIN user_groups gr ON us.user_group = gr.idgroup
                LEFT JOIN ugroup_packages ugp ON bil.package = ugp. idpack
                LEFT JOIN uright_packages urp ON bil.package = urp. idrpack
                ";

        if(count($where))
        	$sql .= " WHERE " . implode(" AND", $where);

        if(!isset($count))
        	$count = $this->countOperation($conditions);


        /* block for count pagination */
        $pages = ceil($count/$per_p);
        if($function_type == 'pages')
            return $pages;

        if(isset($order)){
            $str = explode("_",$order);
            $ord = $str[0];
            $asc_desc = $str[1];
            switch($ord){
                case 'init': $order_by = " init_date";
                case 'paid': $order_by = " paid_date";
                case 'until': $order_by = " paid_until";
            }
            $order_by .= " $asc_desc";
        }
        $sql .= " ORDER BY ".$order_by;

        if($page > $pages)
            $page = $pages;
        $start = ($page-1)*$per_p;
        if($start < 0)
            $start = 0;

        $limit =  " LIMIT " . $start . "," . $per_p;
        $sql .= $limit;

        return $this->db->query_all($sql, $params);
    }

    public function countOperation($conditions){
		$where = array();
		$params = array();

        extract($conditions);

        if(isset($user)){
            $where[] = " user = ?";
            $params[] = $user;
        }
        if(isset($start_date)){
            $where = " op_date >= ?";
            $params[] = $start_date;
        }

        if(isset($end_date)){
            $where = " op_date <= ?";
            $params[] = $end_date;
        }

        $sql = "SELECT COUNT(*) as counter
                FROM billing ";

        if(count($where))
        	$sql .= " WHERE " . implode(" AND", $where);

		$rez = $this->db->query_one($sql, $params);

        return $rez['counter'];
	}

    public function updateOperation($idop, $data) {
        $this->db->where('idop = ?', array($idop));
        return $this->db->update('billing', $data);
    }


	// methods for external bills

	function get_external_bills($conditions){
		$where = array();
        $params = array();
		$order_by = '';
        $limit = false;
		extract($conditions);

        $start = (int) ($start ?? 0);
        $per_p = (int) ($per_p ?? 0);

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}
			$order_by = implode(',', $multi_order_by);
		}
		if(isset($id_user)){
            $where[] = ' u.idu = ?';
            $params[] = $id_user;
        }

		if(isset($request_list)){
            $request_list = getArrayFromString($request_list);
            $where[] = ' eb.id IN (' . implode(',', array_fill(0, count($request_list), '?')) . ') ';
            array_push($params, ...$request_list);
        }

		if(isset($type)){
            $where[] = ' eb.type = ? ';
            $params[] = $type;
        }

		if(isset($start_date)){
            $where[] = ' eb.date_time >= ?';
            $params[] = $start_date;
        }

		if(isset($finish_date)){
            $where[] = '  eb.date_time <= ?';
            $params[] = $finish_date;
        }

		if(isset($status)){
            $where[] = ' eb.status = ? ';
            $params[] = $status;
        }

		if(isset($search)){
            $where[] = " MATCH (eb.comment,eb.notice) AGAINST (?)";
			$params[] = $search;
		}

        if (isset($realUsers)) {
            if ($realUsers) {
                $where[] = " u.fake_user = 0 ";
                $where[] = " u.is_model = 0 ";
            } else {
                $where[] = " (u.fake_user = 1 OR u.is_model = 1) ";
            }
        }

		$sql = "SELECT CONCAT(u.fname, ' ', u.lname) as user_name, u.idu as user_id , eb.*
				FROM ".$this->external_bills_table." eb
				LEFT JOIN users u ON eb.to_user=u.idu ";

		if(count($where))
		    $sql .= " WHERE " . implode(" AND ", $where);

		if($order_by)
			$sql .= " ORDER BY " . $order_by;

        if ($limit) {
            $sql .= " LIMIT " . $start  . ', ' . $per_p;
        }

        return $this->db->query_all($sql, $params);
    }

	function get_external_bills_count($conditions){
		$where = array();
        $params = array();
		$order_by = '';
		extract($conditions);

		if(isset($id_user)){
            $where[] = ' u.idu = ?';
            $params[] = $id_user;
        }

		if(isset($status)){
            $where[] = ' eb.status = ? ';
            $params[] = $status;
        }

		if(isset($type)){
            $where[] = ' eb.type = ? ';
            $params[] = $type;
        }

		if(isset($start_date)){
            $where[] = ' eb.date_time >= ?';
            $params[] = $start_date;
        }

		if(isset($finish_date)){
            $where[] = '  eb.date_time <= ?';
            $params[] = $finish_date;
        }

		if(isset($search)){
            $where[] = " MATCH (eb.comment,eb.notice) AGAINST (?)";
			$params[] = $search;
		}

        if (isset($realUsers)) {
            if ($realUsers) {
                $where[] = " u.fake_user = 0 ";
                $where[] = " u.is_model = 0 ";
            } else {
                $where[] = " (u.fake_user = 1 OR u.is_model = 1) ";
            }
        }

		$sql = "SELECT COUNT(*) as counter
				FROM ".$this->external_bills_table." eb
				LEFT JOIN users u ON eb.to_user=u.idu ";

		if(count($where))
		    $sql .= " WHERE " . implode(" AND ", $where);

        $temp = $this->db->query_one($sql, $params);
		return $temp['counter'];
    }

	function create_ext_bill($insert){
		$this->db->insert($this->external_bills_table, $insert);
        return $this->db->last_insert_id();
	}

	function get_ext_bill($id, $select = '*'){
		$sql = "SELECT ".$select."
				FROM ".$this->external_bills_table." eb
				WHERE id=?";
		return $this->db->query_one($sql, array($id));
	}

	function delete_external_bill_request($id_request){
		$this->db->where('id', $id_request);
		$this->db->delete($this->external_bills_table);
		return $this->db->numRows();
	}

	function append_notice($id, $json){
		$sql = "UPDATE ".$this->external_bills_table." SET notice = CONCAT_WS(',',?,notice) WHERE id = ?";

        return $this->db->query($sql,array($json,$id));
	}

	function update_ext_bill($id_ext_bill, $update){
		$this->db->where('id', $id_ext_bill);
		return $this->db->update($this->external_bills_table, $update);
	}

    // EXTERNAL BILLS ON ADMIN
	function get_users($ids){
		$data = array();
		if(!empty($ids['buyer'])){
            $buyersIds = getArrayFromString($ids['buyer']);

			$sql = "SELECT CONCAT(u.fname, ' ', u.lname) as buyer_name, u.user_photo as buyer_logo, u.idu
                    FROM users u
                    WHERE u.idu IN (" . implode(',', array_fill(0, count($buyersIds), '?')) . ")";

			$temp = $this->db->query_all($sql, $buyersIds);

			foreach($temp as $one){
				$data['buyer'][$one['idu']] = $one;
			}
		}

		if(!empty($ids['seller'])){
            $sellerIds = getArrayFromString($ids['seller']);

			$sql = "SELECT CONCAT(u.fname, ' ', u.lname) as seller_name, u.user_photo as seller_logo, u.idu
                    FROM users u
                    WHERE u.idu IN (" . implode(',', array_fill(0, count($sellerIds), '?')) . ")";

			$temp = $this->db->query_all($sql, $sellerIds);

			foreach($temp as $one){
				$data['seller'][$one['idu']] = $one;
			}
		}
		if(!empty($ids['shipper'])){
            $shipperIds = getArrayFromString($ids['shipper']);

			$sql = "SELECT *
					FROM orders_shippers
					WHERE id_user IN (" . implode(',', array_fill(0, count($shipperIds), '?')) . ")";

			$temp = $this->db->query_all($sql, $shipperIds);
			foreach($temp as $one){
				$data['shipper'][$one['id_user']] = $one;
			}
		}
		return $data;
	}
}
