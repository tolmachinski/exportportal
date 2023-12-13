<?php
/**
 * User_Bills_model.php
 *
 * User_Bills
 *
 * @author
 * @deprecated in favor of \Bills_Model
 */
class User_Bills_Model extends TinyMVC_Model
{
	private $key = '7f427ed3959b5040bc6e6d22eba67adb';
    private $item_orders_table = "item_orders";
    private $users_table = "users";
    private $user_bills_table = "users_bills";
    private $user_bills_types_table = "users_bills_types";
    private $user_bills_types_table_primary_key = "id_type";
    private $payment_methods_table = "payment_methods";
	public  $expire_soon_days = 2;

	/**
	 * Returns the name of bill types table.
	 *
	 * @return string
	 */
	public function get_bills_types_table(): string
	{
		return $this->user_bills_types_table;
	}

	/**
	 * Returns the primary key name of bill types table.
	 *
	 * @return string
	 */
	public function get_bills_types_table_primary_key(): string
	{
		return $this->user_bills_types_table_primary_key;
	}

	function get_bills_statuses(){
		return array(
			'init' => array(
				'icon' => 'file txt-orange',
				'icon_new' => 'file',
				'class' => 'txt-orange',
				'title' => translate('billing_documents_waiting_payment'),
				'description' => translate('billing_documents_waiting_payment'),
				'date' => 'create_date',
				'text_date' => 'Created on: '
			),
			'paid' => array(
				'icon' => 'dollar-circle txt-blue',
				'icon_new' => 'dollar-circle',
				'class' => 'txt-green',
				'title' => translate('billing_documents_paid'),
				'description' => translate('billing_documents_paid'),
				'date' => 'pay_date',
				'text_date' => 'Paid on: '
			),
			'confirmed' => array(
				'icon' => 'ok-circle txt-green',
				'icon_new' => 'ok-circle',
				'class' => 'txt-green',
				'title' => translate('billing_documents_status_confirmed'),
				'description' => translate('billing_documents_status_confirmed'),
				'date' => 'confirmed_date',
				'text_date' => 'Confirmed on: '
			),
			'unvalidated' => array(
				'icon' => 'remove-circle txt-red',
				'icon_new' => 'remove-circle',
				'class' => 'txt-red',
				'title' => translate('billing_documents_status_unvalidated'),
				'description' => translate('billing_documents_status_unvalidated'),
				'date' => 'declined_date',
				'text_date' => 'Declined on: '
			),
		);
	}

	function get_bills_types_array(){
		return array(
			'order' => array(
				'icon' => 'orders txt-blue',
				'icon_new' => 'box fs-20',
				'title' => 'Orders',
				'description' => 'Order bill'
			),
			'sample_order' => array(
				'icon' => 'orders txt-blue',
				'icon_new' => 'box fs-20',
				'title' => 'Sample Order',
				'description' => 'Sample Order bill'
			),
			'ship' => array(
				'icon' => 'truck-move txt-green',
				'icon_new' => 'truck',
				'title' => 'Shipping',
				'description' => 'Shipping bill'
			),
			'feature_item' => array(
				'icon' => 'featured txt-orange',
				'icon_new' => 'arrow-line-up',
				'title' => 'Feature items',
				'description' => 'Feature items bill'
			),
			'highlight_item' => array(
				'icon' => 'highlight txt-blue',
				'icon_new' => 'highlight',
				'title' => 'Highlight items',
				'description' => 'Highlight items bill'
			),
			'group' => array(
				'icon' => 'item txt-red',
				'icon_new' => 'rights2 fs-21',
				'title' => 'Account upgrade',
				'description' => 'Account upgrade'
			),
			'right' => array(
				'icon' => 'services txt-red',
				'icon_new' => 'rights',
				'title' => 'Rights package',
				'description' => 'Right package'
			),
		);
	}

	function get_bill_search_info($id_user){
		$sql_user = "SELECT CONCAT(u.fname, ' ', u.lname) as user_name, u.email, ug.gr_name, ug.gr_type
					FROM users u
					INNER JOIN user_groups ug ON u.user_group = ug.idgroup
					WHERE u.idu = ?";
		$user = $this->db->query_one($sql_user, array($id_user));
		$search_info = $user['user_name'].' '.$user['email'];
		switch($user['gr_type']){
			case 'Seller' :
				$sql_company = "SELECT name_company, email_company
								FROM company_base
								WHERE id_user = ?";
				$company = $this->db->query_one($sql_company, array($id_user));
				$search_info .= ' '.$company['name_company'].' '.$company['email_company'];
			break;
			case 'Shipper' :
				$sql_company = "SELECT co_name, email
								FROM orders_shippers
								WHERE id_user = ?";
				$company = $this->db->query_one($sql_company, array($id_user));
				$search_info .= ' '.$company['co_name'].' '.$company['email'];
			break;
		}

		return $search_info;
	}

	function update_bills_search_info(){
		$sql = "SELECT *
				FROM users_bills";
		$bills = $this->db->query_all($sql);
		foreach($bills as $bill){
			$search_info = $this->get_bill_search_info($bill['id_user']);
			$update_bill = array(
				'search_info' => $search_info
			);
			$this->update_user_bill($bill['id_bill'], $update_bill);
		}

	}

	public function set_user_bill($data){
        /** @var User_Statistic_Model $userStatisticModel */
        $userStatisticModel = model(User_Statistic_Model::class);

        $userStatisticModel->set_users_statistic(array($data['id_user'] => array ('bills_total' => 1)));
        $data['note'] = json_encode(array('date_note'=>date('Y-m-d H:i:s'), 'note'=>'The bill has been created.'));
        $data['create_date'] = date('Y-m-d H:i:s');
		$data['search_info'] = $this->get_bill_search_info($data['id_user']);
        return $this->db->insert($this->user_bills_table, $data);
    }

	public function update_user_bill($id_bill, $data){
        $this->db->where('id_bill', $id_bill);
        return $this->db->update($this->user_bills_table, $data);
    }

	public function set_user_bills($data){
        if(empty($data)){
            return false;
        }

        /** @var User_Statistic_Model $userStatisticModel */
        $userStatisticModel = model(User_Statistic_Model::class);

        $userStatisticModel->set_users_statistic(array($data[0]['id_user'] => array ('bills_total' => count($data))));
		$search_info = $this->get_bill_search_info($data[0]['id_user']);
		foreach($data as $key=>$item){
			$data[$key]['search_info'] = $search_info;
		}
		$this->db->insert_batch($this->user_bills_table, $data);
		return $this->db->getAffectableRowsAmount();
	}

	public function set_free_user_bill($data){
        /** @var User_Statistic_Model $userStatisticModel */
        $userStatisticModel = model(User_Statistic_Model::class);

        $userStatisticModel->set_users_statistic(array($data['id_user'] => array ('bills_total' => 1, 'bills_payment_confirmed' => 1)));
        $data['note'] = json_encode(array('date_note'=>date('Y-m-d H:i:s'), 'note'=>'The free bill has been created and confirmed.'));
        $data['status'] = 'confirmed';
        $data['pay_date'] = $data['create_date'] = $data['confirmed_date'] = date('Y-m-d H:i:s');
		$data['search_info'] = $this->get_bill_search_info($data['id_user']);
        return $this->db->insert($this->user_bills_table, $data);
    }

	function get_bills_last_id(){
        $this->db->select('id_bill');
        $this->db->orderby('id_bill DESC');
        $this->db->limit(1);

        return $this->db->get_one($this->user_bills_table)['id_bill'] ?: 0;
	}

	function get_count_new_bills($id_bill){
        $this->db->select('COUNT(*) as counter');
        $this->db->where('id_bill >', $id_bill);
        return $this->db->get_one($this->user_bills_table)['counter'];
	}

	public function get_user_bills($conditions){
        $order_by = "b.create_date DESC";
        $page = 1;
        $per_p = 100;
        $pagination = true;
		extract($conditions);

		$where = $params = [];

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		if(isset($hash_code)){
			$where[] = ' b.hash_code = ?';
			$params[] = $hash_code;
		}

		if (isset($bills_type)) {
            $bills_type = getArrayFromString($bills_type);
			$where[] = " b.id_type_bill IN (" . implode(',', array_fill(0, count($bills_type), '?')) . ") ";
            array_push($params, ...$bills_type);
        }

		if(isset($id_order)){
			$where[] = ' b.id_item = ?';
			$params[] = $id_order;
		}

		if(isset($id_item)){
			$where[] = ' b.id_item = ?';
			$params[] = $id_item;
        }

        if(isset($id_bill)){
			$where[] = ' b.id_bill = ?';
			$params[] = $id_bill;
		}

		if(isset($status) && $status != 'all'){
            $status = getArrayFromString($status);
			$where[] = " b.status IN (" . implode(',', array_fill(0, count($status), '?')) . ")";
            array_push($params, ...$status);
		}

		if(isset($expire_status)){
			switch ($expire_status) {
				case 'expired':
					$where[] = " (UNIX_TIMESTAMP(b.due_date) <= UNIX_TIMESTAMP()) AND b.status NOT IN ('confirmed','unvalidated') ";
				break;
				case 'expire_soon':
					$where[] = " (UNIX_TIMESTAMP(b.due_date) <= (UNIX_TIMESTAMP() + ?*86400)) AND (UNIX_TIMESTAMP(b.due_date) - (UNIX_TIMESTAMP())) > 0 AND b.status IN ('init','paid') ";
					$params[] = $this->expire_soon_days;
				break;
			}
		}

		if (!empty($date_column)) {
            if(isset($date_from)){
                $where[] = ' DATE(b.`'.$date_column.'`) >= ?';
                $params[] = $date_from;
            }
            if(isset($date_to)){
                $where[] = ' DATE(b.`'.$date_column.'`) <= ?';
                $params[] = $date_to;
            }
		}


        if(isset($amount_from)){
            $where[] = ' b.balance >= ?';
            $params[] = $amount_from;
        }
        if(isset($amount_to)){
            $where[] = ' b.balance <= ?';
            $params[] = $amount_to;
        }

		if(isset($bills_list)){
            $bills_list = getArrayFromString($bills_list);
			$where[] = " b.id_bill IN (" . implode(',', array_fill(0, count($bills_list), '?')) . ") ";
            array_push($params, ...$bills_list);
		}

		if(isset($bill_number)){
			$where[] = " b.id_bill = ? ";
            $params[] = $bill_number;
		}

		if(isset($type) && $type != 'all'){
			$where[] = " bt.name_type = ? ";
			$params[] = $type;
		}

		if(isset($pay_method)){
			$where[] = ' b.pay_method = ?';
			$params[] = $pay_method;
		}

		if(isset($id_user)){
			$where[] = ' b.id_user = ?';
			$params[] = $id_user;
		}

		if(isset($is_refunded)){
			$where[] = ' b.refund_bill_request > 0 ';
		}

		if(isset($search)){
			if(str_word_count_utf8($search) > 1){
				$order_by = $order_by . ", REL DESC";
				$where[] = " MATCH (b.bill_description, b.search_info) AGAINST (?)";
				$params[] = $search;
				$rel = " , MATCH (b.bill_description, b.search_info) AGAINST (?) as REL";
                array_unshift($params, $search);
			} else{
				$where[] = " (b.bill_description LIKE ? OR b.search_info LIKE ?)";
                array_push($params, ...['%' . $search . '%', '%' . $search . '%']);
			}
		}

		if(isset($encript_detail)){
			$select = ' ,'.$this->dec_string(array('pay_detail'), $this->key);
		}

        if (isset($realUsers)) {
            if ($realUsers) {
                $where[] = " u.fake_user = 0 ";
                $where[] = " u.is_model = 0 ";
            } else {
                $where[] = " (u.fake_user = 1 OR u.is_model = 1) ";
            }
        }

        $joins = empty($joins) ? ' ' : ' ' . implode(' ', $joins) . ' ';

        $sql = "SELECT b.*, bt.name_type, bt.show_name, pm.method, CONCAT( u.fname, ' ', u.lname ) as user_name, u.email ".$select."
				$rel
        		FROM ".$this->user_bills_table." b
				LEFT JOIN ".$this->users_table." u ON b.id_user = u.idu
				LEFT JOIN ".$this->user_bills_types_table." bt ON b.id_type_bill = bt.id_type
				LEFT JOIN ".$this->payment_methods_table." pm ON b.pay_method = pm.id";

		if(!empty($where)){
			$sql .= " WHERE " . implode(" AND", $where);
        }

		$sql .= " ORDER BY " .$order_by;

		if(isset($limit)){
			$sql .= " LIMIT " . $limit ;
		} elseif($pagination && isset($per_p) && isset($page)){
			if(!isset($count))
				$count = $this->get_bills_count($conditions);

			$pages = ceil($count/$per_p);

			if(!isset($start)){
				if ($page > $pages) $page = $pages;
				$start = ($page-1)*$per_p;

				if($start < 0) $start = 0;
			}

			$sql .= " LIMIT " . $start ;

			if($per_p > 0)
				$sql .= "," . $per_p;
        }

        return $this->db->query_all($sql, $params);
    }

	public function get_bills_count($conditions){
        extract($conditions);

		$where = $params = $joins = [];

		if(isset($hash_code)){
			$where[] = ' b.hash_code = ?';
			$params[] = $hash_code;
		}

		if(isset($bills_type)){
            $bills_type = getArrayFromString($bills_type);
			$where[] = " b.id_type_bill IN (" . implode(',', array_fill(0, count($bills_type), '?')) . ") ";
            array_push($params, ...$bills_type);
		}

		if(isset($id_order)){
			$where[] = ' b.id_item = ?';
			$params[] = $id_order;
		}

		if(isset($id_item)){
			$where[] = ' b.id_item = ?';
			$params[] = $id_item;
        }

		if(isset($id_bill)){
			$where[] = ' b.id_bill = ?';
			$params[] = $id_bill;
		}

		if(isset($status)){
            $status = getArrayFromString($status);
			$where[] = " b.status IN (" . implode(',', array_fill(0, count($status), '?')) . ")";
            array_push($params, ...$status);
		}

		if(isset($expire_status)){
			switch ($expire_status) {
				case 'expired':
					$where[] = " (UNIX_TIMESTAMP(b.due_date) <= UNIX_TIMESTAMP()) AND b.status NOT IN ('confirmed','unvalidated') ";
				break;
				case 'expire_soon':
					$where[] = " (UNIX_TIMESTAMP(b.due_date) <= (UNIX_TIMESTAMP() + ?*86400)) AND (UNIX_TIMESTAMP(b.due_date) - (UNIX_TIMESTAMP())) > 0 AND b.status IN ('init','paid') ";
					$params[] = $this->expire_soon_days;
				break;
			}
		}

        if (!empty($date_column)) {
            if(isset($date_from)){
                $where[] = ' DATE(b.`'.$date_column.'`) >= ?';
                $params[] = $date_from;
            }
            if(isset($date_to)){
                $where[] = ' DATE(b.`'.$date_column.'`) <= ?';
                $params[] = $date_to;
            }
		}

        if(isset($amount_from)){
            $where[] = ' b.balance >= ?';
            $params[] = $amount_from;
        }

        if(isset($amount_to)){
            $where[] = ' b.balance <= ?';
            $params[] = $amount_to;
        }

		if(isset($bills_list)){
            $bills_list = getArrayFromString($bills_list);
			$where[] = " b.id_bill IN (" . implode(',', array_fill(0, count($bills_list), '?')) . ") ";
            array_push($params, ...$bills_list);
		}

		if(isset($bill_number)){
			$where[] = " b.id_bill = ? ";
            $params[] = $bill_number;
		}

		if(isset($type)){
			$where[] = " bt.name_type = ? ";
			$params[] = $type;
		}

		if(isset($pay_method)){
			$where[] = ' b.pay_method = ?';
			$params[] = $pay_method;
		}

		if(isset($id_user)){
			$where[] = ' b.id_user = ?';
			$params[] = $id_user;
		}

		if(isset($is_refunded)){
			if($is_refunded == true){
				$where[] = ' b.refund_bill_request > 0 ';
			} else{
				$where[] = ' b.refund_bill_request = 0 ';
			}
		}

		if(isset($search)){
			$words = explode(' ', $search);
			if(count($words) > 1){
				$where[] = " MATCH (b.bill_description, b.search_info) AGAINST (?)";
				$params[] = $search;
			} else{
				$where[] = " b.bill_description LIKE ? OR b.search_info LIKE ? ";
                array_push($params, ...['%' . $search . '%', '%' . $search . '%']);
			}
		}

        if (isset($realUsers)) {
            $joins[] = "LEFT JOIN users u ON u.idu = b.id_user";

            if ($realUsers) {
                $where[] = " u.fake_user = 0 ";
                $where[] = " u.is_model = 0 ";
            } else {
                $where[] = " (u.fake_user = 1 OR u.is_model = 1) ";
            }
        }

        $joins = empty($joins) ? ' ' : ' ' . implode(' ', $joins) . ' ';

		$sql = "SELECT COUNT(*) as counter
        		FROM $this->user_bills_table b
				LEFT JOIN $this->user_bills_types_table bt ON b.id_type_bill = bt.id_type {$joins}";

		if(!empty($where)){
			$sql .= " WHERE " . implode(' AND ', $where);
        }

        return $this->db->query_one($sql, $params)['counter'];
	}

	public function count_bills_by_status_type($conditions){
        extract($conditions);

		$where = $params = [];

		if(isset($type) && $type != 'all'){
			$where[] = " bt.name_type = ? ";
			$params[] = $type;
		}

		if(isset($status) && $status != 'all'){
			$where[] = ' b.status = ?';
			$params[] = $status;
		}

		if(isset($id_user)){
			$where[] = ' b.id_user = ?';
			$params[] = $id_user;
		}

		if(isset($bill_number)){
			$where[] = ' b.id_bill = ?';
			$params[] = $bill_number;
		}

		if(isset($search)){
			$words = explode(' ', $search);
			if(count($words) > 1){
				$where[] = " MATCH (b.search_info) AGAINST (?)";
				$params[] = $search;
			} else{
				$where[] = " b.search_info LIKE ? ";
                $params[] = '%' . $search . '%';
			}
		}

        $sql = "SELECT COUNT(*) as counter
        		FROM ".$this->user_bills_table." b
				LEFT JOIN ".$this->user_bills_types_table." bt ON b.id_type_bill = bt.id_type";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(" AND", $where);
        }

		return $this->db->query_one($sql, $params)['counter'];
    }

	public function count_user_bills_by_status($conditions = array()){
        extract($conditions);

        $where = $params = [];

		if(isset($id_user)){
			$where[] = ' id_user = ?';
			$params[] = $id_user;
		}

		if(isset($exclude_statuses)){
            $exclude_statuses = getArrayFromString($exclude_statuses);
			$where[] = " id_type_bill NOT IN (" . implode(',', array_fill(0, count($exclude_statuses), '?')) . ")";
            array_push($params, ...$exclude_statuses);
		}

		$sql = "SELECT status, COUNT(*) as counter FROM {$this->user_bills_table}";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " GROUP BY status ";

        return $this->db->query_all($sql, $params);
    }

	public function count_user_bills_by_status_type($conditions = array()){
        extract($conditions);

        $where = $params = [];

		if(isset($id_user)){
			$where[] = ' ub.id_user = ?';
			$params[] = $id_user;
		}

        if(isset($exclude_statuses)){
            $exclude_statuses = getArrayFromString($exclude_statuses);
			$where[] = " ub.id_type_bill NOT IN (" . implode(',', array_fill(0, count($exclude_statuses), '?')) . ")";
            array_push($params, ...$exclude_statuses);
		}

		$sql = "SELECT ub.status, ub.id_type_bill, COUNT(*) as counter, ubt.name_type
        		FROM ".$this->user_bills_table." ub
				LEFT JOIN ".$this->user_bills_types_table." ubt
				ON ub.id_type_bill = ubt.id_type";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " GROUP BY ub.status, ub.id_type_bill ";

        return $this->db->query_all($sql, $params);
    }

	public function get_simple_bill($conditions = array()){
		$columns = "*";

		extract($conditions);

		$this->db->select($columns);
		$this->db->from($this->user_bills_table);

		if(isset($id_bill)){
			$this->db->where('id_bill = ?', $id_bill);
		}

		if(isset($id_item)){
			$this->db->where('id_item = ?', $id_item);
		}

		if(isset($status)){
			if(is_array($status)){
				$this->db->in('status', $status);
			} else{
				$this->db->where('status = ?', $status);
			}
		}

		if(isset($pay_method)){
			$this->db->where('pay_method = ?', $pay_method);
		}

		if(isset($create_date)){
			$this->db->where('create_date = ?', $create_date);
		}

		if(isset($id_user)){
			$this->db->where('id_user = ?', $id_user);
		}

		if(isset($id_type_bill)){
			$this->db->where('id_type_bill = ?', $id_type_bill);
        }

        $this->db->orderby('id_bill DESC');

		return $this->db->get_one() ?: [];
    }

	public function get_simple_bills($conditions){
        $columns = "*";
		extract($conditions);

		$where = $params = [];

		if(isset($id_order)){
			$where[] = " id_item = ? ";
			$params[] = $id_order;
		}

		if(isset($status)){
            $status = getArrayFromString($status);
			$where[] = " status IN (" . implode(',', array_fill(0, count($status), '?')) . ") ";
            array_push($params, ...$status);
		}

        if(isset($bills_list)){
            $bills_list = getArrayFromString($bills_list);
			$where[] = " id_bill IN (" . implode(',', array_fill(0, count($bills_list), '?')) . ") ";
            array_push($params, ...$bills_list);
		}

		if(isset($pay_method)){
			$where[] = ' pay_method = ?';
			$params[] = $pay_method;
		}

		if(isset($create_date)){
			$where[] = ' DATE(create_date) = ?';
			$params[] = $create_date;
		}

		if(isset($id_user)){
			$where[] = ' id_user = ?';
			$params[] = $id_user;
		}

		if(isset($id_type_bill)){
			$where[] = ' id_type_bill = ?';
			$params[] = $id_type_bill;
		}

		if(isset($search)){
			$where[] = " MATCH (search_info) AGAINST (?)";
			$params[] = $search;
		}

		$sql = "SELECT $columns FROM {$this->user_bills_table}";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(' AND ', $where);
        }

		return $this->db->query_all($sql, $params);
	}

	public function get_bills_counts_by_order($conditions){
        extract($conditions);

		$where = [' ubill.id_type_bill IN (1,2)'];
        $params = [];

		if(isset($id_orders)){
            $id_orders = getArrayFromString($id_orders);
			$where[] = ' io.id IN (' . implode(',', array_fill(0, count($id_orders), '?')) . ')';
            array_push($params, ...$id_orders);
		}

		$sql = 'SELECT io.id,
				COUNT( ubill.id_bill) as counter_all,
				SUM( if(ubill.status = "init", 1, 0)) as counter_init,
				SUM( if(ubill.status = "paid", 1, 0)) as counter_paid,
				SUM( if(ubill.status = "confirmed", 1, 0)) as counter_confirmed,
				SUM( if(ubill.status = "unvalidated", 1, 0)) as counter_unvalidated
        		FROM '.$this->item_orders_table.' io
				INNER JOIN users_bills ubill ON ubill.id_item = io.id ';

		if (!empty($where)) {
			$sql .= " WHERE " . implode(' AND ', $where);
        }

		$sql .= " GROUP BY io.id ";

		$rez = $this->db->query_all($sql, $params);
		return arrayByKey($rez, 'id');
	}

	public function get_bills_counts_by_user($conditions){
        extract($conditions);

		$where = [' id_type_bill = 5'];
        $params = [];

		if(isset($id_users)){
            $id_users = getArrayFromString($id_users);
			$where[] = ' id_user IN (' . implode(',', array_fill(0, count($id_users), '?')) . ')';
            array_push($params, ...$id_users);
		}

		$sql = "SELECT id_user,
					COUNT( id_bill) as counter_all,
					SUM( if(`status` = 'init', 1, 0)) as counter_init,
					SUM( if(`status` = 'paid', 1, 0)) as counter_paid,
					SUM( if(`status` = 'confirmed', 1, 0)) as counter_confirmed,
					SUM( if(`status` = 'unvalidated', 1, 0)) as counter_unvalidated
				FROM users_bills";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(' AND ', $where);
        }

		$sql .= " GROUP BY id_user ";

		$rez = $this->db->query_all($sql, $params);
		return arrayByKey($rez, 'id_user');
	}

	public function get_bills_counts_by_status($conditions){
        extract($conditions);

		$where = $params = [];

		if(isset($hash_code)){
			$where[] = ' b.hash_code = ?';
			$params[] = $hash_code;
		}

        if(isset($bills_type)){
            $bills_type = getArrayFromString($bills_type);
			$where[] = " b.id_type_bill IN (" . implode(',', array_fill(0, count($bills_type), '?')) . ") ";
            array_push($params, ...$bills_type);
		}

		if(isset($id_order)){
			$where[] = ' b.id_item = ?';
			$params[] = $id_order;
		}

		if(isset($id_item)){
			$where[] = ' b.id_item = ?';
			$params[] = $id_item;
        }

        if(isset($id_bill)){
			$where[] = ' b.id_bill = ?';
			$params[] = $id_bill;
		}

		if(isset($expire_status)){
			switch ($expire_status) {
				case 'expired':
					$where[] = " (UNIX_TIMESTAMP(b.due_date) <= UNIX_TIMESTAMP()) ";
				break;
				case 'expire_soon':
					$where[] = " (UNIX_TIMESTAMP(b.due_date) <= (UNIX_TIMESTAMP() + ?*86400)) AND (UNIX_TIMESTAMP(b.due_date) - (UNIX_TIMESTAMP())) > 0 ";
					$params[] = $this->expire_soon_days;
				break;
			}
		}

        if (!empty($date_column)) {
            if(isset($date_from)){
                $where[] = ' DATE(b.`'.$date_column.'`) >= ?';
                $params[] = $date_from;
            }
            if(isset($date_to)){
                $where[] = ' DATE(b.`'.$date_column.'`) <= ?';
                $params[] = $date_to;
            }
		}

        if(isset($amount_from)){
            $where[] = ' b.amount >= ?';
            $params[] = $amount_from;
        }

        if(isset($amount_to)){
            $where[] = ' b.amount <= ?';
            $params[] = $amount_to;
        }

        if(isset($bills_list)){
            $bills_list = getArrayFromString($bills_list);
			$where[] = " b.id_bill IN (" . implode(',', array_fill(0, count($bills_list), '?')) . ") ";
            array_push($params, ...$bills_list);
		}

		if(isset($bill_number)){
			$where[] = " b.id_bill = ? ";
            $params[] = $bill_number;
		}

		if(isset($type)){
			$where[] = " bt.name_type = ? ";
			$params[] = $type;
		}

		if(isset($pay_method)){
			$where[] = ' b.pay_method = ?';
			$params[] = $pay_method;
		}

		if(isset($id_user)){
			$where[] = ' b.id_user = ?';
			$params[] = $id_user;
		}

		if(isset($search)){
			$words = explode(' ', $search);
			if(count($words) > 1){
				$where[] = " MATCH (b.bill_description, b.search_info) AGAINST (?)";
				$params[] = $search;
			} else{
				$where[] = " b.bill_description LIKE ? OR b.search_info LIKE ? ";
                array_push($params, ...['%' . $params . '%', '%' . $params . '%']);
			}
		}

        if (isset($realUsers)) {
            $joins[] = "LEFT JOIN users u ON u.idu = b.id_user";

            if ($realUsers) {
                $where[] = " u.fake_user = 0 ";
                $where[] = " u.is_model = 0 ";
            } else {
                $where[] = " (u.fake_user = 1 OR u.is_model = 1) ";
            }
        }

        $joins = empty($joins) ? ' ' : ' ' . implode(' ', $joins) . ' ';

		$sql = "SELECT b.status, COUNT(*) as counter
        		FROM $this->user_bills_table b
				LEFT JOIN $this->user_bills_types_table bt ON b.id_type_bill = bt.id_type {$joins}";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(' AND ', $where);
        }

		$sql .= " GROUP BY status ";

		return $this->db->query_all($sql, $params);
	}

	public function get_user_bill($id_bill, $conditions = array()){
        extract($conditions);

		$where = ['id_bill = ?'];
		$params = [$id_bill];

		if(isset($id_user)){
			$where[] = " b.id_user = ? ";
			$params[] = $id_user;
		}
		if(isset($status)){
			$where[] = " b.status = ? ";
			$params[] = $status;
		}
		if(isset($name_type)){
			$where[] = " bt.name_type = ? ";
			$params[] = $name_type;
		}

        if(isset($bills_type)){
            $bills_type = getArrayFromString($bills_type);
			$where[] = " b.id_type_bill IN (" . implode(',', array_fill(0, count($bills_type), '?')) . ") ";
            array_push($params, ...$bills_type);
		}

		if(isset($hash_code)){
			$where[] = " hash_code = ? ";
			$params[] = $hash_code;
		}

        $sql = "SELECT b.*, bt.name_type , bt.show_name
        		FROM ".$this->user_bills_table." b
				LEFT JOIN ".$this->user_bills_types_table." bt ON b.id_type_bill = bt.id_type";

		if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
		}

        return $this->db->query_one($sql, $params);
	}

	public function get_bill_list(array $bill_ids = array(), $user_id = null)
	{
		if (empty($bill_ids)) {
			return array();
		}

		$this->db->select("BILLS.*, BILL_TYPES.name_type , BILL_TYPES.show_name");
		$this->db->from("{$this->user_bills_table} AS `BILLS`");
		$this->db->join("{$this->user_bills_types_table} AS `BILL_TYPES`", "BILLS.id_type_bill = BILL_TYPES.id_type");
		$this->db->in("id_bill", $bill_ids);
		if (null !== $user_id) {
			$this->db->where('id_user = ?', (int) $user_id);
		}

		return $this->db->query_all();
	}

	public function exist_user_bill($id_bill, $id_user, $conditions = array()){
        extract($conditions);
		$where = array();
		$params = array($id_bill, $id_user);

        if(isset($status)){
            $status = getArrayFromString($status);
			$where[] = " status IN (" . implode(',', array_fill(0, count($status), '?')) . ") ";
            array_push($params, ...$status);
		}

        $sql = "SELECT COUNT(*) as counter
        		FROM ".$this->user_bills_table."
        		WHERE id_bill = ? AND id_user = ?";

		if (!empty($where)) {
			$sql .= " AND " . implode(" AND ", $where);
        }

        return $this->db->query_one($sql, $params)['counter'];
    }

    public function change_user_bill($id_bill, $data)
    {
        $set = array();
        $params = array();
		foreach ($data as $key => $item) {
            $params[] = $item;
			if ('note' === $key) {
                $set[] = "{$key} = CONCAT_WS(',', note, ?) ";
            } else {
                $set[] = "{$key} = ?";
            }
        }
        $params[] = $id_bill;
        $set = implode(', ', $set);

        return $this->db->query("UPDATE {$this->user_bills_table} SET $set WHERE id_bill = ?", $params);
    }

    public function change_user_bills($id_bills, $data)
    {
        $set = array();
        $params = array();
		foreach ($data as $key => $item) {
            $params[] = $item;
			if ('note' === $key) {
                $set[] = "{$key} = CONCAT_WS(',', note, ?) ";
            } else {
                $set[] = "{$key} = ?";
            }
        }
        $set = implode(', ', $set);

        $id_bills = getArrayFromString($id_bills);
        array_push($params, ...$id_bills);

        return $this->db->query("UPDATE {$this->user_bills_table} SET $set WHERE id_bill IN (" . implode(',', array_fill(0, count($id_bills), '?')) . ")", $params);
    }

	public function set_encrypt_data($id_bill, $data = null){
        $str = $this->enc_string($data);
        $sql = "UPDATE ".$this->user_bills_table." SET $str WHERE id_bill = ?";
        return $this->db->query($sql, [$id_bill]);
    }

    public function get_encrypt_data($id_bill, $data){
        $str = $this->dec_string($data);
        $sql = "SELECT $str FROM {$this->user_bills_table} WHERE id_bill = ?";

        return $this->db->query_one($sql, array($id_bill));
    }

    public function enc_string($columns){
        $str = "";
        if(is_array($columns)){
            foreach($columns as $field => $value){
                $arr[] = " $field = AES_ENCRYPT('$value', '".$this->key."')";
            }
            $str = implode(", ", $arr);
        }
        return $str;
    }

    public function dec_string($columns){
        $str = "";
        if(is_array($columns)){
            foreach($columns as $field){
                $arr[] = " AES_DECRYPT($field, '".$this->key."') as $field";
            }
            $str = implode(", ", $arr);
        }
        return $str;
    }

	function isset_bills_by_order($id_order){
		$sql = "SELECT count(*) as counter
        		FROM ".$this->user_bills_table."
				WHERE id_item = ? AND id_type_bill IN (1,2) ";
		return $this->db->query_one($sql, array($id_order))['counter'];
	}

	function summ_bills_by_order($id_order, $status = "'paid', 'confirmed'", $type = ""){
        $params = [$id_order];
        $status = getArrayFromString($id_order);

		$sql = "SELECT SUM(amount) as summ_amount
        		FROM {$this->user_bills_table}
				WHERE id_item = ? AND status IN (" . implode('?', array_fill(0, count($status), '?')) . ")";

        array_push($params, ...$status);

        if (!empty($type)) {
            $type = getArrayFromString($type);
            $sql .= " AND id_type_bill IN (" . implode(',', array_fill(0, count($type), '?')) . ")";
            array_push($params, ...$type);
        }

		return $this->db->query_one($sql, $params)['summ_amount'];
	}

	function summ_bills_by_item($id_user, $id_item, $status = "'init','paid','confirmed','unvalidated'", $type = ""){
        $params = [$id_user, $id_item];
        $status = getArrayFromString($status);
        array_push($params, ...$status);

		$sql = "SELECT SUM(amount) as summ_amount
        		FROM {$this->user_bills_table}
				WHERE id_user = ? AND id_item = ? AND status IN (" . implode(',', array_fill(0, count($status), '?')) . ")";

        if (!empty($type)) {
            $type = getArrayFromString($type);
            $sql .= " AND id_type_bill IN (" . implode(',', array_fill(0, count($type), '?')) . ")";
            array_push($params, ...$type);
        }

		return $this->db->query_one($sql, $params)['summ_amount'];
	}

	function count_bills_by_item($id_user, $id_item, $status = "'init','paid','confirmed','unvalidated'", $type = ""){
        $params = [$id_user, $id_item];
        $status = getArrayFromString($status);
        array_push($params, ...$status);

		$sql = "SELECT COUNT(*) as count_bills
        		FROM {$this->user_bills_table}
				WHERE id_user = ? AND id_item = ? AND status IN (" . implode(',', array_fill(0, count($status), '?')) . ")";

        if (!empty($type)) {
            $type = getArrayFromString($type);
            $sql .= " AND id_type_bill IN (" . implode(',', array_fill(0, count($type), '?')) . ")";
            array_push($params, ...$type);
        }

		return $this->db->query_one($sql, $params)['count_bills'];
	}

	function summ_bills_balace_by_item($id_item, $status = "'init','paid','confirmed','unvalidated'", $type = "1,2"){
        $params = [$id_item];

        $status = getArrayFromString($status);
        array_push($params, ...$status);

        $type = getArrayFromString($type);
        array_push($params, ...$type);

		$sql = "SELECT SUM(balance) as summ_balance
        		FROM ".$this->user_bills_table."
				WHERE id_item = ? AND status IN (" . implode(',', array_fill(0, count($status), '?')) . ") AND id_type_bill IN (" . implode(',', array_fill(0, count($type), '?')) . ")";

		return $this->db->query_one($sql, $params)['summ_balance'];
	}

	function get_total_balace_bills_by_item($id_user, $id_item, $status = "'init','paid','confirmed','unvalidated'", $type = ""){
        $params = [$id_user, $id_item];
        $status = getArrayFromString($status);
        array_push($params, ...$status);

		$sql = "SELECT SUM(total_balance) as total_balance
        		FROM {$this->user_bills_table}
				WHERE id_user = ? AND id_item = ? AND status IN (" . implode(',', array_fill(0, count($status), '?')) . ")";

        if (!empty($type)) {
            $type = getArrayFromString($type);
            $sql .= " AND id_type_bill IN (" . implode(',', array_fill(0, count($type), '?')) . ")";
            array_push($params, ...$type);
        }

		return $this->db->query_one($sql, $params)['summ_amount'];
	}

	function summ_bills($bills_list, $status = "'init','paid','confirmed','unvalidated'"){
        $params = $bills_list = getArrayFromString($bills_list);
        $status = getArrayFromString($status);
        array_push($params, ...$status);

        $where[] = " b.id_bill IN (" . implode(',', array_fill(0, count($bills_list), '?')) . ") ";

		$sql = "SELECT SUM(balance) as summ
        		FROM {$this->user_bills_table}
				WHERE id_bill IN (" . implode(',', array_fill(0, count($bills_list), '?')) . ") AND status IN (" . implode(',', array_fill(0, count($status), '?')) . ")";

		return $this->db->query_one($sql, $params)['summ'];
	}

    function get_bills_types(){
        return $this->db->get('users_bills_types');
    }

    public function get_soon_expire_bills_count($conditions){
		extract($conditions);

        $where = array(
			" (UNIX_TIMESTAMP(b.due_date) <= (UNIX_TIMESTAMP() + ?*86400)) ",
			" (UNIX_TIMESTAMP(b.due_date) - (UNIX_TIMESTAMP())) > 0 ",
			" b.status IN ('init','paid') "
		);
		$params = array($this->expire_soon_days);

		if(isset($hash_code)){
			$where[] = ' b.hash_code = ?';
			$params[] = $hash_code;
		}

        if(isset($bills_type)){
            $bills_type = getArrayFromString($bills_type);
			$where[] = " b.id_type_bill IN (" . implode(',', array_fill(0, count($bills_type), '?')) . ") ";
            array_push($params, ...$bills_type);
		}

		if(isset($id_order)){
			$where[] = ' b.id_item = ?';
			$params[] = $id_order;
		}

		if(isset($id_item)){
			$where[] = ' b.id_item = ?';
			$params[] = $id_item;
        }

        if(isset($id_bill)){
			$where[] = ' b.id_bill = ?';
			$params[] = $id_bill;
		}

        if (!empty($date_column)) {
            if(isset($date_from)){
                $where[] = ' DATE(b.`'.$date_column.'`) >= ?';
                $params[] = $date_from;
            }
            if(isset($date_to)){
                $where[] = ' DATE(b.`'.$date_column.'`) <= ?';
                $params[] = $date_to;
            }
		}

        if(isset($amount_from)){
            $where[] = ' b.amount >= ?';
            $params[] = $amount_from;
        }

        if(isset($amount_to)){
            $where[] = ' b.amount <= ?';
            $params[] = $amount_to;
        }

		if(isset($bills_list)){
            $bills_list = getArrayFromString($bills_list);
			$where[] = " b.id_bill IN (" . implode(',', array_fill(0, count($bills_list), '?')) . ") ";
            array_push($params, ...$bills_list);
		}

		if(isset($bill_number)){
			$where[] = " b.id_bill = ? ";
            $params[] = $bill_number;
		}

		if(isset($type)){
			$where[] = " bt.name_type = ? ";
			$params[] = $type;
		}

		if(isset($pay_method)){
			$where[] = ' b.pay_method = ?';
			$params[] = $pay_method;
		}

		if(isset($id_user)){
			$where[] = ' b.id_user = ?';
			$params[] = $id_user;
		}

		if(isset($search)){
			$words = explode(' ', $search);
			if(count($words) > 1){
				$where[] = " MATCH (b.bill_description, b.search_info) AGAINST (?)";
				$params[] = $search;
			} else{
				$where[] = " b.bill_description LIKE ? OR b.search_info LIKE ? ";
                array_push($params, ...['%' . $search . '%', '%' . $search . '%']);
			}
		}

        if (isset($realUsers)) {
            $joins[] = "LEFT JOIN users u ON u.idu = b.id_user";

            if ($realUsers) {
                $where[] = " u.fake_user = 0 ";
                $where[] = " u.is_model = 0 ";
            } else {
                $where[] = " (u.fake_user = 1 OR u.is_model = 1) ";
            }
        }

        $joins = empty($joins) ? ' ' : ' ' . implode(' ', $joins) . ' ';

        $sql = "SELECT COUNT(*) as count_expire_soon
                FROM $this->user_bills_table b
				LEFT JOIN $this->user_bills_types_table bt ON b.id_type_bill = bt.id_type {$joins}";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
        }

		return $this->db->query_one($sql, $params)['count_expire_soon'];
    }

    public function get_expired_bills_count($conditions){
		extract($conditions);

        $where = array(
			" (UNIX_TIMESTAMP(due_date) <= (UNIX_TIMESTAMP())) ",
			" b.status NOT IN ('confirmed','unvalidated') "
		);

		$params = array();

		if(isset($hash_code)){
			$where[] = ' b.hash_code = ?';
			$params[] = $hash_code;
		}

        if(isset($bills_type)){
            $bills_type = getArrayFromString($bills_type);
			$where[] = " b.id_type_bill IN (" . implode(',', array_fill(0, count($bills_type), '?')) . ") ";
            array_push($params, ...$bills_type);
		}

		if(isset($id_order)){
			$where[] = ' b.id_item = ?';
			$params[] = $id_order;
		}

		if(isset($id_item)){
			$where[] = ' b.id_item = ?';
			$params[] = $id_item;
		}

        if(isset($id_bill)){
			$where[] = ' b.id_bill = ?';
			$params[] = $id_bill;
		}

        if (!empty($date_column)) {
            if(isset($date_from)){
                $where[] = ' DATE(b.`'.$date_column.'`) >= ?';
                $params[] = $date_from;
            }
            if(isset($date_to)){
                $where[] = ' DATE(b.`'.$date_column.'`) <= ?';
                $params[] = $date_to;
            }
		}

        if(isset($amount_from)){
            $where[] = ' b.amount >= ?';
            $params[] = $amount_from;
        }

        if(isset($amount_to)){
            $where[] = ' b.amount <= ?';
            $params[] = $amount_to;
        }

		if(isset($bills_list)){
            $bills_list = getArrayFromString($bills_list);
			$where[] = " b.id_bill IN (" . implode(',', array_fill(0, count($bills_list), '?')) . ") ";
            array_push($params, ...$bills_list);
		}

		if(isset($bill_number)){
			$where[] = " b.id_bill = ? ";
            $params[] = $bill_number;
		}

		if(isset($type)){
			$where[] = " bt.name_type = ? ";
			$params[] = $type;
		}

		if(isset($pay_method)){
			$where[] = ' b.pay_method = ?';
			$params[] = $pay_method;
		}

		if(isset($id_user)){
			$where[] = ' b.id_user = ?';
			$params[] = $id_user;
		}

		if(isset($search)){
			$words = explode(' ', $search);
			if(count($words) > 1){
				$where[] = " MATCH (b.bill_description, b.search_info) AGAINST (?)";
				$params[] = $search;
			} else{
				$where[] = " b.bill_description LIKE ? OR b.search_info LIKE ? ";
                array_push($params, ...['%' . $search . '%', '%' . $search . '%']);
			}
		}

        if (isset($realUsers)) {
            $joins[] = "LEFT JOIN users u ON u.idu = b.id_user";

            if ($realUsers) {
                $where[] = " u.fake_user = 0 ";
                $where[] = " u.is_model = 0 ";
            } else {
                $where[] = " (u.fake_user = 1 OR u.is_model = 1) ";
            }
        }

        $joins = empty($joins) ? ' ' : ' ' . implode(' ', $joins) . ' ';

        $sql = "SELECT COUNT(*) as count_expired
                FROM $this->user_bills_table b
				LEFT JOIN $this->user_bills_types_table bt ON b.id_type_bill = bt.id_type {$joins}";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
        }

		return $this->db->query_one($sql, $params)['count_expired'];
    }
}

