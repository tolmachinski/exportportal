<?php

use App\Common\Database\BaseModel;
use App\Common\Database\PortableModel;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Exceptions\NotFoundException;
use Doctrine\DBAL\ParameterType;

/**
 * orders_model.php
 *
 * orders model
 *
 * @author Andrei Litra
 * @deprecated in favor of \Product_Orders_Model
 */
class Orders_Model extends BaseModel
{
	// hold the current controller instance
	var $obj;
	private $auto_extends_table = "auto_extends";
	private $user_bills_table = "users_bills";
	private $item_orders_table = "item_orders";
	private $orders_status_table = "orders_status";
	private $orders_reason_table = "orders_reason";
	private $order_statuses_reasons_table = "order_statuses_reasons";
	private $ordered_items_table = "item_ordered";
	private $items_table = "items";
	private $orders_shippers_table = "orders_shippers";
	private $orders_shippers_quotes_table = "orders_shippers_quotes";
	private $users_bills_table = "users_bills";
	private $item_snapshots_table = "item_snapshots";
	private $user_feedbacks_table = "user_feedbacks";
	private $item_reviews_table = "item_reviews";
	private $payment_methods_table = "payment_methods";
	private $payment_methods_i18n_table = "payment_methods_i18n";
	private $company_base_table = "company_base";
	private $item_order_invoices_table = "item_order_invoices";
	private $languages_table = "translations_languages";
	private $users_table = "users";

	private $item_orders_cancel_requests_table = 'item_orders_cancel_requests';
    public  $expire_soon_days = 2;

    /**
     * Create the model instance
     */
    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

    /**
     * Find order by provided.
     *
     * @throws NotFoundException if order is not found.
     */
    public function find(?int $orderId): array
    {
        if (
            null === $orderId
            || null === $order = $this->findRecord(
                null,
                $this->item_orders_table,
                null,
                'id',
                $orderId
            )
        ) {
            throw new NotFoundException(sprintf("The order with ID \"%s\" is not found", varToString($orderId)));
        }

        return $order;
    }

    /**
     * Find order by provided.
     *
     * @throws NotFoundException if order is not found.
     */
    public function findWithAssignees(?int $orderId): array
    {
        if (
            null === $orderId
            || null === $order = $this->findRecord(
                null,
                $this->item_orders_table,
                null,
                'id',
                $orderId,
                [
                    'with' => ['buyer', 'seller', 'shipper', 'international_shippers']
                ],
            )
        ) {
            throw new NotFoundException(sprintf("The order with ID %s is not found", $orderId));
        }

        if ('ishipper' === $order['shipper_type']) {
            $order['shipper'] = $order['international_shippers'];
        }
        unset($order['international_shippers']);

        return $order;
    }

	public function insert_order($info) {
        /** @var User_Statistic_Model $userStatisticModel */
        $userStatisticModel = model(User_Statistic_Model::class);

		$userStatisticModel->set_users_statistic(array(
			$info['id_seller'] => array('orders_total' => 1, 'orders_active' => 1),
			$info['id_buyer'] => array('orders_total' => 1, 'orders_active' => 1)
		));

        $info['order_date'] = date("Y-m-d H:i:s");

		return $this->db->insert($this->item_orders_table, $info);
	}

	public function add_cancel_order_requests($data = array()) {
        return empty($data) ? false : $this->db->insert($this->item_orders_cancel_requests_table, $data);
	}

	public function update_cancel_order_request($id_request, $data = array()) {
        $this->db->where('id_request', $id_request);
        return empty($data) ? false : $this->db->update($this->item_orders_cancel_requests_table, $data);
	}

	public function get_cancel_order_requests($conditions = array()) {
		$order_by = " create_date DESC ";

		extract($conditions);

        $where = $params = [];

		if(isset($id_request)){
			$where[] = " id_request = ? ";
			$params[] = $id_request;
		}

		if(isset($id_user)){
			$where[] = " id_user = ? ";
			$params[] = $id_user;
		}

		if(isset($id_order)){
			$where[] = " id_order = ? ";
			$params[] = $id_order;
		}

		if(isset($status)){
            $status = getArrayFromString($status);
			$where[] = " status IN ("  . implode(',', array_fill(0, count($status), '?')) . ") ";
            array_push($params, ...$status);
		}

		$sql = "SELECT * FROM $this->item_orders_cancel_requests_table ";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
        }

		$sql .= " ORDER BY " . $order_by;
		return $this->db->query_all($sql, $params);
	}

	public function get_cancel_order_request($id_request) {
		$sql = "SELECT *
				FROM $this->item_orders_cancel_requests_table
				WHERE id_request=?";
		return $this->db->query_one($sql, array($id_request));
	}

	public function get_cancel_order_request_by_status($id_order, $status = NULL) {
		if($status === NULL){
			return;
		}

		$sql = "SELECT *
				FROM $this->item_orders_cancel_requests_table
				WHERE id_order = ? AND status = ?";
		return $this->db->query_one($sql, array($id_order, $status));
	}

	public function count_cancel_order_requests($conditions = array()) {
        extract($conditions);

		$where = $params = [];

		if(isset($id_user)){
			$where[] = " id_user = ? ";
			$params[] = $id_user;
		}

		if(isset($id_order)){
			$where[] = " id_order = ? ";
			$params[] = $id_order;
		}

		if(isset($status)){
            $status = getArrayFromString($status);
			$where[] = " status IN (" . implode(',', array_fill(0, count($status), '?')) . ") ";
            array_push($params, ...$status);
		}

		$sql = "SELECT COUNT(*) as counter
				FROM $this->item_orders_cancel_requests_table ";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
        }

		return $this->db->query_one($sql, $params)['counter'];
	}

	public function get_order_notassigned_last_id() {
		$sql = "SELECT id
				FROM $this->item_orders_table
				WHERE ep_manager = 0
				ORDER BY id DESC
				LIMIT 0,1";

		return (int) $this->db->query_one($sql)['id'];
	}

	public function get_count_new_order_notassigned($id_order) {
		$sql = "SELECT COUNT(*) as counter
				FROM $this->item_orders_table
				WHERE ep_manager = 0 AND id > ? ";

		return $this->db->query_one($sql, array($id_order))['counter'];
	}

	public function get_order_assigned_last_id($id_user) {
		$sql = "SELECT id
				FROM $this->item_orders_table
				WHERE ep_manager = ?
				ORDER BY id DESC
				LIMIT 0,1";

		return (int) $this->db->query_one($sql, array($id_user))['id'];
	}

	public function get_count_new_order_assigned($id_order, $id_user) {
		$sql = "SELECT COUNT(*) as counter
				FROM $this->item_orders_table
				WHERE ep_manager = ? AND id > ? ";

		return $this->db->query_one($sql, array($id_user, $id_order))['counter'];
	}

	public function get_orders($conditions) {
        extract($conditions);

		$where = $params = [];

		if (isset($order_list)) {
            $order_list = getArrayFromString($order_list);
			$where[] = " io.id IN (" . implode(',', array_fill(0, count($order_list), '?')) . ")";
            array_push($params, ...$order_list);
        }

		$sql = "SELECT io.*, os.notify_users
				FROM $this->item_orders_table io
				LEFT JOIN orders_status os ON io.status = os.id";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
        }

		$sql .= " ORDER BY io.order_date";

		return $this->db->query_all($sql, $params);
	}

	public function isMyOrderedItem($id_item, $id_user) {
		$sql = "SELECT COUNT(*) as counter
				FROM $this->ordered_items_table iod
				LEFT JOIN $this->item_orders_table o ON iod.id_order = o.id
				WHERE iod.id_ordered_item = ? AND (o.id_buyer = ? OR o.id_seller = ?)";

		return $this->db->query_one($sql, array($id_item, $id_user, $id_user))['counter'];
	}

	public function get_my_ordered_item($id_item, $id_user) {
		$sql = "SELECT iod.*, o.id_seller
				FROM $this->ordered_items_table iod
				INNER JOIN $this->item_orders_table o ON iod.id_order = o.id
				WHERE iod.id_ordered_item = ? AND (o.id_buyer = ? OR o.id_seller = ?)";

		return $this->db->query_one($sql, array($id_item, $id_user, $id_user));
	}

	public function isMyOrder($id_order, $id_user) {
		$sql = "SELECT COUNT(*) as counter
				FROM $this->item_orders_table io
				WHERE io.id = ? AND (io.id_buyer = ? OR io.id_seller = ? OR io.id_shipper = ?)";

        return $this->db->query_one($sql, array($id_order, $id_user, $id_user, $id_user))['counter'];
	}

	public function get_user_orders_by_seller($conditions) {
        $with_no_feedback = $with_no_seller_feedback = $with_no_reviews = false;
		$feedback = $review = "";
		extract($conditions);

		$where = $params = [];

		if (isset($id_seller)) {
			$where[] = ' o.id_seller = ?';
			$params[] = $id_seller;
		}
		if (isset($id_user)) {
			$where[] = ' o.id_buyer = ?';
			$params[] = $id_user;
		}
		if (isset($id_item)) {
			$where[] = ' iod.id_item = ?';
			$params[] = $id_item;
		}
		if ($with_no_feedback) {
			$where[] = ' uf.id_feedback IS null';
			$feedback = ", uf.id_feedback";
		} elseif ($with_no_seller_feedback) {
			$where[] = ' uf.id_feedback IS null';
			$feedback = ", uf.id_feedback";
		}
		if ($with_no_reviews) {
			$where[] = ' ir.id_item IS null';
			$review = ", ir.id_review";
		}
		if (isset($status)) {
			$where[] = ' o.status = ?';
			$params[] = $status;
		}

		if (isset($id_ordered)) {
			$where[] = ' iod.id_ordered_item = ? ';
			$params[] = $id_ordered;
		}

		if (isset($list_ordered)) {
            $list_ordered = getArrayFromString($list_ordered);
			$where[] = ' iod.id_ordered_item IN ( ' . implode(',', array_fill(0, count($list_ordered), '?')) . ' ) ';
            array_push($params, ...$list_ordered);
        }

		$sql = "SELECT iod.id_ordered_item, isp.id_snapshot, isp.id_item, isp.title, isp.main_image " . $feedback . " " . $review . "
				FROM $this->ordered_items_table iod
				LEFT JOIN $this->item_orders_table o ON o.id = iod.id_order
				LEFT JOIN $this->item_snapshots_table isp ON iod.id_snapshot = isp.id_snapshot";

		if ($with_no_feedback) {
			$sql .= " LEFT OUTER JOIN $this->user_feedbacks_table uf ON o.id_buyer = uf.id_poster";
        } elseif ($with_no_seller_feedback) {
			$sql .= " LEFT OUTER JOIN $this->user_feedbacks_table uf ON o.id_seller = uf.id_poster";
        }

		if ($with_no_reviews) {
			$sql .= " LEFT OUTER JOIN $this->item_reviews_table ir ON iod.id_ordered_item = ir.id_ordered_item";
        }

		if (!empty($where)) {
			$sql .= " WHERE " . implode(" AND", $where);
        }

		return $this->db->query_all($sql, $params);
	}

    public function has_order($id) {
        $this->db->select("COUNT(*) AS AGGREGATE");
        $this->db->from($this->item_orders_table);
        $this->db->where('id = ?', (int) $id);
        return (bool) (int) $this->db->query_one()['AGGREGATE'];
    }

	public function get_order($id, $column = array(), $conditions = array()) {
		extract($conditions);

        $where = [' o.id = ? '];
		$params = [$id];

		if (isset($id_seller)) {
			$where[] = " o.id_seller = ? ";
			$params[] = $id_seller;
		}

		if (isset($id_buyer)) {
			$where[] = " o.id_buyer = ? ";
			$params[] = $id_buyer;
		}

		if (isset($id_shipper)) {
			$where[] = " o.id_shipper = ? ";
			$params[] = $id_shipper;
		}

		if (!empty($column)) {
			$columns = implode(", o.", $column);
			$columns = "o." . $columns . " , reas.reason, os.status as order_status, os.alias as status_alias, os.icon as status_icon, os.description as status_description ";
		} else {
			$columns = "o.*, reas.reason, os.status as order_status, os.alias as status_alias, os.icon as status_icon, os.description as status_description ";
		}

		$sql = "SELECT $columns
				FROM $this->item_orders_table o
				LEFT JOIN $this->orders_status_table os ON o.status = os.id
				LEFT JOIN $this->orders_reason_table reas ON o.reason = reas.id";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(' AND ', $where);
        }

		return $this->db->query_one($sql, $params);
	}

	public function set_reason($data) {
        return empty($data) ? false : $this->db->insert($this->orders_reason_table, $data);
	}

	public function update_reason($id_reason, $data) {
        $this->db->where('id', $id_reason);
        return empty($data) ? false : $this->db->update($this->orders_reason_table, $data);
	}

	public function delete_reason($id_reason) {
		$this->db->where('id', $id_reason);
		return $this->db->delete($this->orders_reason_table);
	}

	public function set_reason_statuses_relation($data) {
		if (empty($data)) {
			return false;
        }

		$this->db->insert_batch($this->order_statuses_reasons_table, $data);
		return $this->db->getAffectableRowsAmount();
	}

	public function get_reason($id_reason) {
		$sql = "SELECT *
				FROM $this->orders_reason_table
				WHERE id = ?";
		return $this->db->query_one($sql, array($id_reason));
	}

	public function get_reason_statuses_relation($id_reason) {
		$sql = "SELECT *
				FROM $this->order_statuses_reasons_table
				WHERE id_reason = ?";
		return $this->db->query_all($sql, array($id_reason));
	}

	public function delete_reason_statuses_relation($id_reason) {
		$this->db->where('id_reason', $id_reason);
		return $this->db->delete($this->order_statuses_reasons_table);
	}

	public function get_reasons($conditions = array()) {
		$order_by = "r.id ASC";
		$group_by = "";
		$rel = "";

		extract($conditions);

        $where = $params = [];

		if (isset($sort_by)) {
			foreach ($sort_by as $sort_item) {
			$sort_item = explode('-', $sort_item);
			$multi_order_by[] = $sort_item[0] . ' ' . $sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		if (isset($keywords)) {
			$order_by = $order_by . ", REL DESC";
			$where[] = " MATCH (r.reason) AGAINST (?)";
			$params[] = $keywords;
			$rel = " , MATCH (r.reason) AGAINST (?) as REL";
            array_unshift($params, $keywords);
		}

		$sql = "SELECT r.* $rel
				FROM $this->orders_reason_table r";

		if (isset($order_status)) {
			$sql .= " INNER JOIN $this->order_statuses_reasons_table osr ON r.id = osr.id_reason";
			$where[] = " osr.id_status = ? ";
			$params[] = $order_status;
			$group_by = " GROUP BY osr.id_reason ";
		}

		if (!empty($where)) {
			$sql .= " WHERE " . implode(' AND ', $where);
        }

		$sql .= $group_by;
		$sql .= " ORDER BY " . $order_by;

		return $this->db->query_all($sql, $params);
	}

	private function __remove_auto_extends($id_order) {
		$this->db->where('id_item', $id_order);
		$this->db->delete($this->auto_extends_table);
	}

	public function change_order($order, $data) {

		if(isset($data['status'])){
			$this->__remove_auto_extends($order);
			$data['auto_extend'] = 0;
			$data['request_auto_extend'] = 0;
			$data['reminder_sent'] = 0;
		}

		if(isset($data['status_countdown'])){
			$data['status_countdown_updated'] = date('Y-m-d H:i:s');
		}

		$this->db->where('id', $order);
		return $this->db->update($this->item_orders_table, $data);
	}

	public function change_order_log($id_order, $data) {
		$sql = "UPDATE item_orders
				SET order_summary = CONCAT_WS(',', order_summary, ?)
				WHERE id = ?";

		return $this->db->query($sql, [$data, $id_order]);
	}

	public function is_enable_pay_metod($id_method = 0) {
		$this->db->select("COUNT(*) as total_rows");
		$this->db->from($this->payment_methods_table);
		$this->db->where("`enable` = ?", 1);
		$this->db->where("id = ?", $id_method);
		$result = $this->db->get_one();
		return (bool) (int) $result['total_rows'];
	}

    public function get_pay_methods($conditions = array())
    {
        extract($conditions);

		$where = $params = [];

		if (isset($enable)) {
			$where[] = " enable = ? ";
			$params[] = $enable;
        }

        if (isset($i18n_with_lang)) {
            $where[] = " id IN (SELECT id_method FROM {$this->payment_methods_i18n_table} WHERE id_lang = ? ) ";
			$params[] = $i18n_with_lang;
        }

        if (isset($i18n_without_lang)) {
            $where[] = " id NOT IN (SELECT id_method FROM {$this->payment_methods_i18n_table} WHERE id_lang = ? ) ";
			$params[] = $i18n_without_lang;
        }

        $order_by = '';
        if(!empty($sort_by)) {
            if(is_array($sort_by)) {
                $order_by = "ORDER BY " . implode(', ', $sort_by);
            } else {
                $order_by = "ORDER BY {$sort_by}";
            }
        }

		$sql = "SELECT * FROM $this->payment_methods_table";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(' AND ', $where);
        }

        if(!empty($order_by)) {
            $sql .= " {$order_by}";
        }

		return $this->db->query_all($sql, $params);
    }

    public function get_pay_methods_with_i18n($enabled = null)
    {
        $this->db->select("*");
        $this->db->from($this->payment_methods_table);
        if(null !== $enabled) {
            $this->db->where("enable = ? ", $enabled ? 1 : 0);
        }
        // Fetch data
        if (!$this->db->query()) {
            return array();
        }
        $methods = $this->db->getQueryResult()->fetchAllAssociative();
        $methods = $methods ? $methods : array();
        if(empty($methods)) {
            return array();
        }

        $keys = array_column($methods, 'id');
        $this->db->select(implode(", ", array(
            '`i18n`.`id_method_i18n` as `id`',
            '`i18n`.`id_method` as `method_id`',
            '`i18n`.`id_lang` as `lang_id`',
            '`i18n`.`method_i18n` as `method`',
            '`i18n`.`instructions_i18n` as `instructions`',
            '`l`.`lang_iso2` as `lang_code`',
        )));
        $this->db->from("{$this->payment_methods_i18n_table} i18n");
        $this->db->join("{$this->languages_table} l", "i18n.id_lang = l.id_lang", "left");
        $this->db->in('id_method', $keys);
        // Fetch data
        if (!$this->db->query()) {
            foreach ($methods as &$method) {
                $method['i18n'] = array();
                $method['enable'] = filter_var((int) $method['enable'], FILTER_VALIDATE_BOOLEAN);
            }

            return $methods;
        }

        $i18n = $this->db->getQueryResult()->fetchAllAssociative();
        $i18n = $i18n ? $i18n : array();
        if(!empty($i18n)) {
            $i18n = arrayByKey($i18n, 'method_id', true);
        }

        foreach ($methods as &$method) {
            $method_id = $method['id'];
            if(isset($i18n[$method_id])) {
                $method['i18n'] = arrayByKey($i18n[$method_id], 'lang_code');
                $method['enable'] = filter_var((int) $method['enable'], FILTER_VALIDATE_BOOLEAN);
            }
        }

        return $methods;
    }

    public function get_pay_method_with_i18n($method_id)
    {
        if(empty($method_id)) {
            return null;
        }

        $this->db->select("*");
        $this->db->from($this->payment_methods_table);
        $this->db->where("id = ? ", $method_id);

        // Fetch data
        if (!$this->db->query()) {
            return null;
        }
        $method = $this->db->getQueryResult()->fetchAssociative();
        $method = $method ? $method : null;
        if(null === $method) {
            return null;
        }

        $method['enable'] = filter_var((int) $method['enable'], FILTER_VALIDATE_BOOLEAN);
        $method['i18n'] = array();

        $this->db->select(implode(", ", array(
            '`i18n`.`id_method_i18n` as `id`',
            '`i18n`.`id_method` as `method_id`',
            '`i18n`.`id_lang` as `lang_id`',
            '`i18n`.`method_i18n` as `method`',
            '`i18n`.`instructions_i18n` as `instructions`',
            '`l`.`lang_iso2` as `lang_code`',
        )));
        $this->db->from("{$this->payment_methods_i18n_table} i18n");
        $this->db->join("{$this->languages_table} l", "i18n.id_lang = l.id_lang", "left");
        $this->db->where('id_method = ? ', $method_id);

        // Fetch data
        if ($this->db->query()) {
            $i18n = $this->db->getQueryResult()->fetchAllAssociative();
            $i18n = $i18n ? $i18n : array();
            $method['i18n'] = arrayByKey($i18n, 'lang_code');
        }

        return $method;
    }

    public function get_pay_methods_i18n_list(array $methods, array $languages = null)
    {
        $methods = array_filter(array_map(function($id) { return (int) $id; }, $methods));
        if(empty($methods)) {
            return array();
        }
        if(null !== $languages) {
            $languages = array_filter(array_map(function($id) { return (int) $id; }, $languages));
        }

        $this->db->select("*");
        $this->db->from($this->payment_methods_i18n_table);
        $this->db->in("{$this->payment_methods_i18n_table}.id_method", $methods);
        if(!empty($languages)) {
            $this->db->in("{$this->payment_methods_i18n_table}.id_lang", $languages);
        }

        // Fetch data
        if (!$this->db->query()) {
            return array();
        }
        $data = $this->db->getQueryResult()->fetchAllAssociative();

        return $data ? $data : array();
    }

    public function get_pay_method_i18n($method_i18n_id)
    {
        $this->db->select('*');
        $this->db->from("{$this->payment_methods_i18n_table} i18n");
        $this->db->where('id_method_i18n = ?', (int) $method_i18n_id);
        $this->db->join("{$this->payment_methods_table} m", "m.id = i18n.id_method", 'left');

        // Fetch data
        if (!$this->db->query()) {
            return null;
        }
        $data = $this->db->getQueryResult()->fetchAssociative();

        return $data ? $data : null;
    }

    public function has_pay_method_i18n($method_id, $lang_id)
    {
        if(empty($method_id) || empty($lang_id)) {
            return false;
        }

        $this->db->select("COUNT(*) AS AGGREGATE");
        $this->db->from($this->payment_methods_i18n_table);
        $this->db->where('id_method = ?', $method_id);
        $this->db->where('id_lang = ?', $lang_id);
        if (!$this->db->query()) {
            return false;
        }
        $data = $this->db->getQueryResult()->fetchAssociative();

        return isset($data['AGGREGATE']) ? filter_var($data['AGGREGATE'], FILTER_VALIDATE_BOOLEAN) : false;
    }

    public function is_pay_method_i18n($method_id)
    {
        if(empty($method_id)) {
            return false;
        }

        $this->db->select("COUNT(*) AS AGGREGATE");
        $this->db->from($this->payment_methods_i18n_table);
        $this->db->where('id_method_i18n = ?', $method_id);
        if (!$this->db->query()) {
            return false;
        }
        $data = $this->db->getQueryResult()->fetchAssociative();

        return isset($data['AGGREGATE']) ? filter_var($data['AGGREGATE'], FILTER_VALIDATE_BOOLEAN) : false;
    }

    public function create_pay_method_i18n($method_id, array $method_i18n)
    {
        if(empty($method_id) || empty($method_i18n)) {
            return false;
        }
        $this->db->where('id_method = ?', $method_id);

        return $this->db->insert($this->payment_methods_i18n_table, $method_i18n);
    }

    public function update_pay_method_i18n($method_id, array $method_i18n)
    {
        if(empty($method_id) || empty($method_i18n)) {
            return false;
        }
        $this->db->where('id_method_i18n = ?', $method_id);

        return $this->db->update($this->payment_methods_i18n_table, $method_i18n);
    }

    public function remove_pay_method_i18n($method_id)
    {
        if(empty($method_id)) {
            return false;
        }
        $this->db->where('id_method_i18n = ?', $method_id);

        return $this->db->delete($this->payment_methods_i18n_table);
    }

    public function count_pay_methods($conditions = array())
    {
        extract($conditions);
		$where = $params = [];

		if (isset($enable)) {
			$where[] = " enable = ? ";
			$params[] = $enable;
        }

        $clauses = "";
		if (!empty($where)) {
            $clauses = " WHERE " . implode(' AND ', $where);
        }
        $sql = "SELECT COUNT(*) as AGGREGATE FROM {$this->payment_methods_table} {$clauses}";
        return (int) $this->db->query_one($sql, $params)['AGGREGATE'];
	}

	public function get_pay_method($id_method) {
		$sql = "SELECT *
				FROM $this->payment_methods_table
				WHERE id = ?";
		return $this->db->query_one($sql, array($id_method));
	}

	public function change_pay_method($id_method, $data) {
		$this->db->where('id', $id_method);
		return $this->db->update($this->payment_methods_table, $data);
	}

	public function set_pay_method($data) {
		return $this->db->insert($this->payment_methods_table, $data);
	}

	public function iBuyIt($id_item, $id_user) {
		$sql = "SELECT COUNT(*) as ibought
				FROM $this->item_orders_table
				WHERE id_buyer = ?";
		return $this->db->query_one($sql, array($id_user))['ibought'];
	}

	public function set_ordered_item($data) {
		return $this->db->insert($this->ordered_items_table, $data);
	}

	public function get_ordered_item($id_ordered_item) {
		$sql = "SELECT io.*, isn.*, ios.id_shipper, ios.id_buyer, ios.id_seller, ios.ep_manager, ios.shipper_type
				FROM $this->ordered_items_table io
				LEFT JOIN $this->item_snapshots_table isn ON io.id_snapshot = isn.id_snapshot
				LEFT JOIN $this->item_orders_table ios ON io.id_order = ios.id
				WHERE io.id_ordered_item = ?";
		return $this->db->query_one($sql, array($id_ordered_item));
	}

	public function get_ordered_items($conditions) {
        extract($conditions);

		$where = $params = [];

		if (isset($items_list)) {
            $items_list = getArrayFromString($items_list);
			$where[] = " io.id_ordered_item IN (" . implode(',', array_fill(0, count($items_list), '?')) . ")";
            array_push($params, ...$items_list);
        }

		if (isset($id_order)) {
			$where[] = " io.id_order = ? ";
			$params[] = $id_order;
		}

		$sql = "SELECT io.*, isn.*
				FROM $this->ordered_items_table as io
				LEFT JOIN $this->item_snapshots_table isn ON io.id_snapshot = isn.id_snapshot ";

		if (count($where))
			$sql .= " WHERE " . implode(' AND ', $where);

		return $this->db->query_all($sql, $params);
	}

	public function get_ordered_items_for_ishipping($conditions) {
        extract($conditions);

		$where = $params = [];

		if (isset($id_order)) {
			$where[] = " io.id_order = ? ";
			$params[] = $id_order;
		}

		$sql = "SELECT io.*, isn.*
				FROM $this->ordered_items_table as io
				LEFT JOIN $this->item_snapshots_table isn ON io.id_snapshot = isn.id_snapshot
				LEFT JOIN $this->items_table it ON io.id_item = it.id ";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(' AND ', $where);
        }

		return $this->db->query_all($sql, $params);
	}

	public function get_users_orders($conditions) {
		$page = 0;
		$per_p = 20;
		$order_by = "io.update_date DESC";
		$joins = array();
		$check_state = true;
		$rel = "";

		extract($conditions);

		$where = $params = [];

        if (isset($sort_by)) {
			foreach ($sort_by as $sort_item) {
			$sort_item = explode('-', $sort_item);
			$multi_order_by[] = $sort_item[0] . ' ' . $sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		if (isset($id_user)) {
			$where[] = " io.id_buyer = ? ";
			$params[] = $id_user;

			if ($check_state) {
				$where[] = " io.state_buyer = ? ";
                $params[] = $state_buyer ?? 0;
			}
		}

		if (isset($id_seller)) {
			$where[] = " io.id_seller = ? ";
			$params[] = $id_seller;

			if ($check_state) {
				$where[] = " io.state_seller = ? ";
                $params[] = $state_seller ?? 0;
			}
		}

		if (isset($id_shipper)) {
			$where[] = " io.id_shipper = ? ";
			$params[] = $id_shipper;

			if(isset($shipper_type)){
				$where[] = " io.shipper_type = ? ";
				$params[] = $shipper_type;
			}
		}

        if (isset($realUsers)) {
            $joins[] = " LEFT JOIN {$this->users_table} buyers ON buyers.idu = io.id_buyer ";
            $joins[] = " LEFT JOIN {$this->users_table} sellers ON sellers.idu = io.id_seller ";

            if ($realUsers) {
                $where[] = " buyers.fake_user = 0 ";
                $where[] = " buyers.is_model = 0 ";
                $where[] = " sellers.fake_user = 0 ";
                $where[] = " sellers.is_model = 0 ";
            } else {
                $where[] = " (buyers.fake_user = 1 OR buyers.is_model = 1 OR sellers.fake_user = 1 OR sellers.is_model = 1) ";
            }
        }

		if (isset($ep_manager)) {
			$where[] = " io.ep_manager = ? ";
			$params[] = $ep_manager;
		}

        if (isset($assigned_manager_email)) {
            $joins[] = " LEFT JOIN {$this->users_table} u ON io.ep_manager = u.idu ";
            $where[] = " u.email = ? ";
			$params[] = $assigned_manager_email;
        }

		if (isset($status)) {
			$where[] = " io.status = ? ";
			$params[] = $status;
		}

		if (isset($statuses)) {
            $statuses = getArrayFromString($statuses);
			$where[] = " io.status IN (" . implode(',', array_fill(0, count($statuses), '?')) . ") ";
            array_push($params, ...$statuses);
		}

		if(isset($expire_status)){
			switch ($expire_status) {
				case 'expired':
					$where[] = " (UNIX_TIMESTAMP(io.status_countdown) <= UNIX_TIMESTAMP()) ";
				break;
				case 'expire_soon':
					$where[] = " (UNIX_TIMESTAMP(io.status_countdown) <= (UNIX_TIMESTAMP() + ?*86400)) AND (UNIX_TIMESTAMP(io.status_countdown) - (UNIX_TIMESTAMP())) > 0 ";
					$params[] = $this->expire_soon_days;
				break;
			}
		}

		if (isset($id_order)) {
			$where[] = " io.id = ? ";
			$params[] = $id_order;
		}

		if (isset($id_orders)) {
            $id_orders = getArrayFromString($id_orders);
			$where[] = " io.id IN (" . implode(',', array_fill(0, count($id_orders), '?')) . ") ";
            array_push($params, ...$id_orders);
		}

		if (isset($price_from)) {
            $where[] = " io.price >= ?";
            $params[] = $price_from;
        }

		if (isset($price_to)) {
            $where[] = " io.price <= ?";
            $params[] = $price_to;
        }

		if (isset($ship_to_country)) {
			$where[] = " io.ship_to_country = ?";
            $params[] = $ship_to_country;
        }

		if (isset($ship_to_city)) {
			$where[] = " io.ship_to_city = ?";
            $params[] = $ship_to_city;
        }

		if (isset($ship_from_country)) {
			$where[] = " io.ship_from_country = ?";
            $params[] = $ship_from_country;
        }

		if (isset($ship_from_city)) {
			$where[] = " io.ship_from_city = ?";
            $params[] = $ship_from_city;
        }

		if (isset($cancel_request)) {
			$where[] = " io.cancel_request = ?";
            $params[] = $cancel_request;
        }

		if (isset($dispute_opened)) {
			$where[] = " io.dispute_opened = ?";
            $params[] = $dispute_opened;
        }

		if (isset($date_val) && $date_val == 'create') {
			if (isset($date_from)) {
                $where[] = " DATE(io.order_date) >= ? ";
                $params[] = $date_from;
			}

			if (isset($date_to)) {
                $where[] = " DATE(io.order_date) <= ? ";
                $params[] = $date_to;
			}
		}

		if ('update' === ($date_val ?? null)) {
			if (isset($date_from)) {
                $where[] = " DATE(io.order_date) >= ? ";
                $params[] = $date_from;
			}

			if (isset($date_to)) {
                $where[] = " DATE(io.order_date) <= ? ";
                $params[] = $date_to;
			}
		}

		if (isset($keywords)) {
			if(str_word_count_utf8($keywords) > 1){
				$order_by = $order_by . ", REL DESC";
				$where[] = " MATCH (io.search_info) AGAINST (?)";
				$params[] = $keywords;
				$rel = " , MATCH (io.search_info) AGAINST (?) as REL";
                array_unshift($params, $keywords);
			} else{
				$where[] = " io.search_info LIKE ? ";
                $params[] = '%' . $keywords . '%';
			}
		}

		if (isset($id_item)) {
			$joins[] = " INNER JOIN item_ordered iod ON iod.id_order = io.id ";
			$where[] = " iod.id_item = ? ";
			$params[] = $id_item;
		}

        $joins = empty($joins) ? '' : ' ' . implode(' ', $joins) . ' ';

		$sql = "SELECT io.*, io.status as id_status, (io.price + io.ship_price) as total_price,
						cb.id_company, cb.name_company, cb.index_name, cb.logo_company,
						os.status, os.alias as status_alias, os.icon, os.icon_new
						$rel
				FROM $this->item_orders_table io
				$joins
				LEFT JOIN $this->company_base_table cb ON cb.id_user = io.id_seller
				LEFT JOIN $this->orders_status_table os ON io.status = os.id";

		if (!empty($where)) {
			$sql .= " WHERE cb.parent_company = 0 AND " . implode(' AND ', $where);
        } else {
			$sql .= " WHERE cb.parent_company = 0 ";
        }

		$sql .= " GROUP BY io.id ";
		$sql .= " ORDER BY " . $order_by;

		if (isset($limit)) {
			$sql .= " LIMIT " . $limit;
		} else {
			if (!isset($count)) {
				$count = $this->get_orders_count($conditions);
			}

			$pages = ceil($count / $per_p);

			if (!isset($start)) {
				if ($page > $pages) {
					$page = $pages;
				}

				$start = ($page - 1) * $per_p;
				if (0 < $start) {
					$start = 0;
				}
			}

			$sql .= " LIMIT " . $start;

			if (0 < $per_p) {
				$sql .= "," . $per_p;
			}
		}

		return $this->db->query_all($sql, $params);
	}

	public function get_full_order($id_order, $conditions = array()) {
		extract($conditions);

        $where = [' io.id = ? '];
		$params = [$id_order];

		if (isset($id_buyers)) {
            $id_buyers = getArrayFromString($id_buyers);
			$where[] = " io.id_buyer IN (" . implode(',', array_fill(0, count($id_buyers), '?')) . ") ";
            array_push($params, ...$id_buyers);
		}

		if (isset($id_sellers)) {
            $id_sellers = getArrayFromString($id_sellers);
			$where[] = " io.id_seller IN (" . implode(',', array_fill(0, count($id_sellers), '?')) . ") ";
			array_push($params, ...$id_sellers);
		}

		$sql = "SELECT io.*, ioi.products, ioi.amount as final_amount, ioi.discount as final_discount,
				os.status, os.id as id_status, os.icon as status_icon, os.alias as status_alias, os.description as status_description
				FROM $this->item_orders_table io
				LEFT JOIN $this->orders_status_table os ON os.id = io.status
				LEFT JOIN $this->item_order_invoices_table ioi ON ioi.id_invoice = io.id_invoice";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(' AND ', $where);
        }

		$order = $this->db->query_one($sql, $params);

		if (empty($order)) {
			return $order;
		}

		$sql2 = "SELECT io.*, isn.*
				FROM $this->ordered_items_table io
				LEFT JOIN $this->item_snapshots_table isn ON io.id_snapshot = isn.id_snapshot
				WHERE io.id_order = ?";

		$order['ordered'] = $this->db->query_all($sql2, array($id_order));
		return $order;
	}

	public function get_orders_count($conditions) {
		$select = $joins = [];
		$order_by = "io.id DESC";

		extract($conditions);

		$where = $params = [];

		if (isset($status_full)) {
			$select[] = " os.id as id_status, os.status, os.position ";
			$right_join = " RIGHT OUTER JOIN $this->orders_status_table os ON io.status = os.id ";

			if (isset($id_buyer_status)) {
				$joins[] = " $right_join AND io.id_buyer = $id_buyer_status ";
			} else {
				$joins[] = " $right_join AND io.id_seller = $id_seller_status ";
			}

			$group_by = " os.id ";
			$order_by = " os.position ";
			$having = " os.position IS NOT NULL ";
		}

        if (isset($realUsers)) {
            $joins[] = " LEFT JOIN {$this->users_table} buyers ON buyers.idu = io.id_buyer ";
            $joins[] = " LEFT JOIN {$this->users_table} sellers ON sellers.idu = io.id_seller ";

            if ($realUsers) {
                $where[] = " buyers.fake_user = 0 ";
                $where[] = " buyers.is_model = 0 ";
                $where[] = " sellers.fake_user = 0 ";
                $where[] = " sellers.is_model = 0 ";
            } else {
                $where[] = " (buyers.fake_user = 1 OR buyers.is_model = 1 OR sellers.fake_user = 1 OR sellers.is_model = 1) ";
            }
        }

		if (isset($ep_manager)) {
			$where[] = " io.ep_manager = ? ";
			$params[] = $ep_manager;
		}

        if (isset($assigned_manager_email)) {
            $joins[] = " LEFT JOIN {$this->users_table} u ON io.ep_manager = u.idu ";
            $where[] = " u.email = ? ";
			$params[] = $assigned_manager_email;
        }

		if (isset($status)) {
			$where[] = " io.status = ? ";
			$params[] = $status;
		}

		if (isset($statuses)) {
			$statuses = getArrayFromString($statuses);
			$where[] = " io.status IN (" . implode(',', array_fill(0, count($statuses), '?')) . ") ";
			array_push($params, ...$statuses);
		}

		if(isset($expire_status)){
			switch ($expire_status) {
				case 'expired':
					$where[] = " (UNIX_TIMESTAMP(io.status_countdown) <= UNIX_TIMESTAMP()) ";
				break;
				case 'expire_soon':
					$where[] = " (UNIX_TIMESTAMP(io.status_countdown) <= (UNIX_TIMESTAMP() + ?*86400)) AND (UNIX_TIMESTAMP(io.status_countdown) - (UNIX_TIMESTAMP())) > 0 ";
					$params[] = $this->expire_soon_days;
				break;
			}
		}

		if (isset($id_order)) {
			$where[] = " io.id = ? ";
			$params[] = $id_order;
		}

		if (isset($id_orders)) {
			$id_orders = getArrayFromString($id_orders);
			$where[] = " io.id IN (" . implode(',', array_fill(0, count($id_orders), '?')) . ") ";
			array_push($params, ...$id_orders);
		}

		if (isset($id_user)) {
			$where[] = " io.id_buyer = ? ";
			$params[] = $id_user;
		}

		if (isset($id_seller)) {
			$where[] = " io.id_seller = ? ";
			$params[] = $id_seller;
		}

		if (isset($id_shipper)) {
			$where[] = " io.id_shipper = ? ";
			$params[] = $id_shipper;
		}

		if (isset($price_from)) {
			$where[] = " io.price >= ?";
            $params[] = $price_from;
        }

		if (isset($price_to)) {
			$where[] = " io.price <= ?";
            $params[] = $price_to;
        }

        if (isset($cancel_request)) {
			$where[] = " io.cancel_request = ?";
            $params[] = $cancel_request;
        }

        if (isset($dispute_opened)) {
			$where[] = " io.dispute_opened = ?";
            $params[] = $dispute_opened;
        }

		if (isset($ship_to_country)) {
			$where[] = " io.ship_to_country = ?";
			$params[] = $ship_to_country;
		}

		if (isset($ship_to_city)) {
			$where[] = " io.ship_to_city = ?";
			$params[] = $ship_to_city;
		}

		if (isset($ship_from_country)) {
			$where[] = " io.ship_from_country = ?";
			$params[] = $ship_from_country;
		}

		if (isset($ship_from_city)) {
			$where[] = " io.ship_from_city = ?";
			$params[] = $ship_from_city;
		}

		if (isset($date_val) && $date_val == 'create') {
			if (isset($date_from)) {
				$where[] = " DATE(io.order_date) >= ? ";
				$params[] = $date_from;
			}

			if (isset($date_to)) {
				$where[] = " DATE(io.order_date) <= ? ";
				$params[] = $date_to;
			}
		}

		if (isset($date_val) && $date_val == 'update') {
			if (isset($date_from)) {
				$where[] = " DATE(io.order_date) >= ? ";
				$params[] = $date_from;
			}

			if (isset($date_to)) {
				$where[] = " DATE(io.order_date) <= ? ";
				$params[] = $date_to;
			}
		}

		if (isset($keywords)) {
			$words = explode(' ', $keywords);
			if(count($words) > 1){
				$where[] = " MATCH (io.search_info) AGAINST (?) ";
				$params[] = $keywords;
			} else{
				$where[] = " io.search_info LIKE ? ";
				$params[] = '%' . $keywords . '%';
			}
		}

		if (isset($id_item)) {
			$joins[] = " INNER JOIN $this->ordered_items_table iod ON iod.id_order = io.id ";
			$where[] = " iod.id_item = ? ";
			$params[] = $id_item;
		}

		$select = empty($select) ? ' ' :  ' , ' . implode(', ', $select) . ' ';
		$joins = empty($joins) ? ' ' : ' ' . implode(' ', $joins) . ' ';

		$sql = "SELECT COUNT(io.status) as counter
					$select
				FROM $this->item_orders_table io
				$joins";

		if (count($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
		}

		if (isset($group_by)) {
			$sql .= " GROUP BY " . $group_by;
		}

		if (isset($having)) {
			$sql .= " HAVING " . $having;
		}

        $sql .= " ORDER BY " . $order_by;

		return isset($status_full) ? $this->db->query_all($sql, $params) : $this->db->query_one($sql, $params)['counter'];
	}

	public function count_orders_by_statuses($conditions) {
		$check_state = true;

		extract($conditions);

		$where_join = "";
		$params_join = [];
		$where = [' os.position IS NOT NULL '];
		$params = [];

		if (isset($ep_manager)) {
			$where[] = " io.ep_manager = ? ";
			$params[] = $ep_manager;
		}

		if (isset($shipper_status)) {
			$where[] = " os.shipper_status = ? ";
			$params[] = $shipper_status;
		}

		if (isset($id_buyer)) {
			$where_join = " AND io.id_buyer = ?";
			$params_join[] = $id_buyer;

			if ($check_state) {
				$where_join .= " AND io.state_buyer = ?";
				$params_join[] = $state_buyer ?? 0;
			}
		}

		if (isset($id_seller)) {
			$where_join = " AND io.id_seller = ?";
			$params_join[] = $id_seller;

			if ($check_state) {
				$where_join .= " AND io.state_seller = ?";
				$params_join[] = $state_seller ?? 0;
			}
		}

		if (isset($id_shipper)) {
			$where_join = " AND io.id_shipper = ?";
			$params_join[] = $id_shipper;
		}

		if (isset($price_from)) {
			$where[] = " io.price >= ?";
			$params[] = $price_from;
		}

		if (isset($price_to)) {
			$where[] = " io.price <= ?";
			$params[] = $price_to;
		}

		if (isset($ship_to_country)) {
			$where[] = " io.ship_to_country = ?";
			$params[] = $ship_to_country;
		}

		if (isset($ship_to_city)) {
			$where[] = " io.ship_to_city = ?";
			$params[] = $ship_to_city;
		}

		if (isset($ship_from_country)) {
			$where[] = " io.ship_from_country = ?";
			$params[] = $ship_from_country;
		}

		if (isset($ship_from_city)) {
			$where[] = " io.ship_from_city = ?";
			$params[] = $ship_from_city;
		}

		if (isset($date_val) && $date_val == 'create') {
			if (isset($date_from)) {
				$where[] = " DATE(io.order_date) >= ? ";
				$params[] = formatDate($date_from, 'Y-m-d');
			}

			if (isset($date_to)) {
				$where[] = " DATE(io.order_date) <= ? ";
				$params[] = formatDate($date_to, 'Y-m-d');
			}
		}

		if (isset($date_val) && $date_val == 'update') {
			if (isset($date_from)) {
				$where[] = " DATE(io.order_date) >= ? ";
				$params[] = formatDate($date_from, 'Y-m-d');
			}

			if (isset($date_to)) {
                $where[] = " DATE(io.order_date) <= ? ";
                $params[] = formatDate($date_to, 'Y-m-d');
			}
		}

		if(isset($expire_status)){
			switch ($expire_status) {
				case 'expired':
					$where[] = " (UNIX_TIMESTAMP(io.status_countdown) <= UNIX_TIMESTAMP()) ";
				break;
				case 'expire_soon':
					$where[] = " (UNIX_TIMESTAMP(io.status_countdown) <= (UNIX_TIMESTAMP() + ?*86400)) AND (UNIX_TIMESTAMP(io.status_countdown) - (UNIX_TIMESTAMP())) > 0 ";
					$params[] = $this->expire_soon_days;
				break;
			}
		}

		if (isset($keywords)) {
			$where[] = " MATCH (io.search_info) AGAINST (?)";
			$params[] = $keywords;
		}

		$sql = "SELECT os.*, COUNT(io.id) as counter
				FROM $this->orders_status_table os
				LEFT JOIN $this->item_orders_table io on os.`id` = io.`status`" . $where_join;

		if (count($where))
			$sql .= " WHERE " . implode(' AND ', $where);

		$sql .= " GROUP BY os.id ORDER BY os.position";

		return $this->db->query_all($sql, array_merge($params_join, $params));
	}

	public function simple_count_orders($conditions) {
        $check_state = true;
		extract($conditions);

		$where = $params = [];

		if (isset($id_buyer)) {
			$where[] = " id_buyer = ? ";
			$params[] = $id_buyer;

			if ($check_state) {
				$where[] = " state_buyer = ? ";
                $params[] = $state_buyer ?? 0;
			}
		}

		if (isset($id_seller)) {
			$where[] = " id_seller = ? ";
			$params[] = $id_seller;

			if ($check_state) {
				$where[] = " state_seller = ? ";
                $params[] = $state_seller ?? 0;
			}
		}

		if (isset($id_shipper)) {
			$where[] = " id_shipper = ? ";
			$params[] = $id_shipper;
		}

		if (isset($status)) {
			$where[] = " status = ? ";
			$params[] = $status;
		}

		if (isset($statuses)) {
            $statuses = getArrayFromString($statuses);
			$where[] = " status IN (" . implode(',', array_fill(0, count($statuses), '?')) . ") ";
            array_push($params, ...$statuses);
		}

		if (isset($id_order)) {
			$where[] = " id = ? ";
			$params[] = $id_order;
		}

		if (isset($ep_manager)) {
			$where[] = " ep_manager = ? ";
			$params[] = $ep_manager;
		}

		if (isset($keywords)) {
			$where[] = " MATCH (search_info) AGAINST (?)";
			$params[] = $keywords;
		}

		$sql = "SELECT COUNT(*) as counter
				FROM $this->item_orders_table";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(' AND ', $where);
        }

		return $this->db->query_one($sql, $params)['counter'];
	}

	public function order_manager_exist($id_order) {
		$sql = "SELECT *
				FROM $this->item_orders_table
				WHERE id = ?";

        return $this->db->query_one($sql, [$id_order])['ep_manager'];
	}

	public function isOrderManager($id_order, $id_manager) {
		$sql = "SELECT COUNT(*) as counter
				FROM $this->item_orders_table
				WHERE id = ? AND ep_manager = ?";

		return $this->db->query_one($sql, [$id_order, $id_manager])['counter'];
	}

	public function isOrderShipper($id_order, $id_shipper) {
		$sql = "SELECT COUNT(*) as counter
				FROM $this->item_orders_table
				WHERE id = ? AND id_shipper = ?";
		return $this->db->query_one($sql, [$id_order, $id_shipper])['counter'];
	}

	public function get_orders_status() {
		$sql = "SELECT *
				FROM $this->orders_status_table
				WHERE position IS NOT NULL
				ORDER BY position";

		return $this->db->query_all($sql);
	}

	public function get_status_name($id_status) {
		$sql = "SELECT status
				FROM $this->orders_status_table
				WHERE id = ?";

		return $this->db->query_one($sql, [$id_status])['status'];
	}

	public function get_status_detail($id_status, $columns = '*') {
		$sql = "SELECT $columns
				FROM $this->orders_status_table
				WHERE id = ?";

		return $this->db->query_one($sql, [$id_status]);
	}

	public function get_status_by_alias($status_alias = null) {
		$this->db->from($this->orders_status_table);
		$this->db->where('alias', $status_alias);

		return $this->db->get_one();
	}

	public function get_user_by_order($columns = "*", $id_order, $user_type) {
		$sql = "SELECT $columns
				FROM $this->users_table u
				LEFT JOIN $this->item_orders_table io ON io." . $user_type . " = u.idu
				WHERE io.id = ? ";

		return $this->db->query_one($sql, array($id_order));
	}

	public function get_order_details($id_order, $id_buyer, $statuses = array(12, 13)) {
        $sql = "SELECT isn.title as item_title, io.id_ordered_item, io.price_ordered as price
				FROM $this->ordered_items_table as io
				LEFT JOIN $this->item_orders_table as ios ON ios.id=io.id_order
				LEFT JOIN $this->item_snapshots_table isn ON io.id_snapshot = isn.id_snapshot
				LEFT JOIN $this->items_table it ON io.id_item = it.id
				WHERE ios.id=? AND ios.id_buyer=? AND  ios.status IN (" . implode(',', array_fill(0, count($statuses), '?')) . ")";
		return $this->db->query_all($sql, array_merge([$id_order, $id_buyer], $statuses));
	}

	public function get_buyers_of_seller($conditions) {
		extract($conditions);

		$where = array("io.id_seller = ?");
		$params = array($user_id);

		if (!empty($country)) {
			$where[] = ' u.country=? ';
			$params[] = $country;

			if (!empty($state)) {
				$where[] = ' u.state=? ';
				$params[] = $state;
			}

			if (!empty($city)) {
				$where[] = ' u.city=? ';
				$params[] = $city;
			}
		}

		if (!empty($not_user_id)) {
            $not_user_id = getArrayFromString($not_user_id);
			$where[] = " u.idu NOT IN (" . implode(',', array_fill(0, count($not_user_id), '?')) . ") ";
            array_push($params, ...$not_user_id);
		}

		$rel = "";
		if(!empty($keywords)){
			$order_by = " REL_tags DESC ";
			$where[] = " MATCH (u.fname, u.lname, u.email) AGAINST (?)";
			$params[] = $keywords;
			$rel = " , MATCH (u.fname, u.lname, u.email) AGAINST (?) as REL_tags";
            array_unshift($params, $keywords);
		}

		$sql = "SELECT u.idu as user_id, CONCAT(u.fname,' ',u.lname) as user_name, u.user_group, u.user_photo, u.logged $rel
				FROM $this->item_orders_table io
				LEFT JOIN $this->users_table u ON io.id_buyer = u.idu";

		$sql .= " WHERE " . implode(" AND ", $where) . " GROUP BY u.idu";

		if ($order_by) {
			$sql .= " ORDER BY " . $order_by;
        }

		return $this->db->query_all($sql, $params);
	}

	public function exist_quote_for_shipper($id_shipper, $id_order){
		$sql = "SELECT COUNT(*) as counter
				FROM $this->orders_shippers_quotes_table
				WHERE id_shipper = ? AND id_order = ?";

		return $this->db->query_one($sql, array($id_shipper, $id_order))['counter'];
	}

	public function get_order_users($order_id)
	{
		$this->db->select('`id_buyer`, `id_seller`, `id_shipper`, `ep_manager`');
		$this->db->from($this->item_orders_table);
		$this->db->where("id = ?", (int) $order_id);
		if (empty($order = $this->db->query_one())) {
			return [];
		}

		return array_filter(array(
            'buyer'   => (int) ($order['id_buyer'] ?? 0),
            'seller'  => (int) ($order['id_seller'] ?? 0),
            'shipper' => (int) ($order['id_shipper'] ?? 0),
            'manager' => (int) ($order['ep_manager'] ?? 0),
        ));
	}

	public function get_order_users_by_user($user){
		$sql = <<<QUERY
            SELECT id_buyer, id_seller, id_shipper, ep_manager, id
            FROM item_orders
            WHERE (id_buyer = ? OR id_seller = ? OR id_shipper = ?) AND status NOT IN (11, 12, 13, 14, 15)
        QUERY;

        return $this->db->query_all($sql, [$user, $user, $user]);
	}

    public function get_soon_expire_orders_count($conditions){
        extract($conditions);

        $joins = [];
        $params = [$this->expire_soon_days];
		$where = [
			" (UNIX_TIMESTAMP(io.status_countdown) <= (UNIX_TIMESTAMP() + ?*86400)) ",
			" (UNIX_TIMESTAMP(io.status_countdown) - (UNIX_TIMESTAMP())) > 0 "
        ];

		if (isset($ep_manager)) {
			$where[] = " io.ep_manager = ? ";
			$params[] = $ep_manager;
		}

        if (isset($assigned_manager_email)) {
            $joins[] = " LEFT JOIN {$this->users_table} u ON io.ep_manager = u.idu ";
            $where[] = " u.email = ? ";
			$params[] = $assigned_manager_email;
        }

		if (isset($status)) {
			$where[] = " io.status = ? ";
			$params[] = $status;
		}

		if (isset($id_order)) {
			$where[] = " io.id = ? ";
			$params[] = $id_order;
		}

		if (isset($id_orders)) {
            $id_orders = getArrayFromString($id_orders);
			$where[] = " io.id IN (" . implode(',', array_fill(0, count($id_orders), '?')) . ") ";
			array_push($params, ...$id_orders);
		}

		if (isset($id_user)) {
			$where[] = " io.id_buyer = ? ";
			$params[] = $id_user;
		}

		if (isset($id_seller)) {
			$where[] = " io.id_seller = ? ";
			$params[] = $id_seller;
		}

		if (isset($price_from)) {
			$where[] = " io.price >= ?";
            $params[] = $price_from;
        }

		if (isset($price_to)) {
			$where[] = " io.price <= ?";
            $params[] = $price_to;
        }

        if (isset($cancel_request)) {
			$where[] = " io.cancel_request = ?";
            $params[] = $cancel_request;
        }

        if (isset($dispute_opened)) {
			$where[] = " io.dispute_opened = ?";
            $params[] = $dispute_opened;
        }

		if (isset($ship_to_country)) {
			$where[] = " io.ship_to_country = ?";
            $params[] = $ship_to_country;
        }

		if (isset($ship_to_city)) {
			$where[] = " io.ship_to_city = ?";
            $params[] = $ship_to_city;
        }

		if (isset($ship_from_country)) {
			$where[] = " io.ship_from_country = ?";
            $params[] = $ship_from_country;
        }

		if (isset($ship_from_city)) {
			$where[] = " io.ship_from_city = ?";
            $params[] = $ship_from_city;
        }

		if (isset($date_val) && $date_val == 'create') {
			if (isset($date_from)) {
                $where[] = " DATE(io.order_date) >= ? ";
                $params[] = $date_from;
			}

			if (isset($date_to)) {
                $where[] = " DATE(io.order_date) <= ? ";
                $params[] = $date_to;
			}
		}

        if (isset($realUsers)) {
            $joins[] = " LEFT JOIN {$this->users_table} buyers ON buyers.idu = io.id_buyer ";
            $joins[] = " LEFT JOIN {$this->users_table} sellers ON sellers.idu = io.id_seller ";

            if ($realUsers) {
                $where[] = " buyers.fake_user = 0 ";
                $where[] = " buyers.is_model = 0 ";
                $where[] = " sellers.fake_user = 0 ";
                $where[] = " sellers.is_model = 0 ";
            } else {
                $where[] = " (buyers.fake_user = 1 OR buyers.is_model = 1 OR sellers.fake_user = 1 OR sellers.is_model = 1) ";
            }
        }

		if (isset($date_val) && $date_val == 'update') {
			if (isset($date_from)) {
				$where[] = " DATE(io.order_date) >= ? ";
				$params[] = $date_from;
			}

			if (isset($date_to)) {
				$where[] = " DATE(io.order_date) <= ? ";
				$params[] = $date_to;
			}
		}

		if (isset($keywords)) {
			$words = explode(' ', $keywords);
			if(count($words) > 1){
				$where[] = " MATCH (io.search_info) AGAINST (?) ";
				$params[] = $keywords;
			} else{
				$where[] = " io.search_info LIKE ? ";
                $params[] = '%' . $keywords . '%';
			}
		}

		if (isset($id_item)) {
			$joins[] = " INNER JOIN $this->ordered_items_table iod ON iod.id_order = io.id ";
			$where[] = " iod.id_item = ? ";
			$params[] = $id_item;
		}

        $joins = empty($joins) ? ' ' : ' ' . implode(' ', $joins) . ' ';

        $sql = "SELECT COUNT(*) as count_expire_soon
                FROM $this->item_orders_table io
				$joins";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
        }

		return $this->db->query_one($sql, $params)['count_expire_soon'];
    }

    public function get_expired_orders_count($conditions){
        extract($conditions);

        $params = $joins = [];
		$where = [
			" (UNIX_TIMESTAMP(io.status_countdown) <= (UNIX_TIMESTAMP())) "
        ];

		if (isset($ep_manager)) {
			$where[] = " io.ep_manager = ? ";
			$params[] = $ep_manager;
		}

        if (isset($assigned_manager_email)) {
            $joins[] = " LEFT JOIN {$this->users_table} u ON io.ep_manager = u.idu ";
            $where[] = " u.email = ? ";
			$params[] = $assigned_manager_email;
        }

		if (isset($status)) {
			$where[] = " io.status = ? ";
			$params[] = $status;
		}

		if (isset($id_order)) {
			$where[] = " io.id = ? ";
			$params[] = $id_order;
		}

        if (isset($realUsers)) {
            $joins[] = " LEFT JOIN {$this->users_table} buyers ON buyers.idu = io.id_buyer ";
            $joins[] = " LEFT JOIN {$this->users_table} sellers ON sellers.idu = io.id_seller ";

            if ($realUsers) {
                $where[] = " buyers.fake_user = 0 ";
                $where[] = " buyers.is_model = 0 ";
                $where[] = " sellers.fake_user = 0 ";
                $where[] = " sellers.is_model = 0 ";
            } else {
                $where[] = " (buyers.fake_user = 1 OR buyers.is_model = 1 OR sellers.fake_user = 1 OR sellers.is_model = 1) ";
            }
        }

		if (isset($id_orders)) {
            $id_orders = getArrayFromString($id_orders);
			$where[] = " io.id IN (" . implode(',', array_fill(0, count($id_orders), '?')) . ") ";
			array_push($params, $id_orders);
		}

		if (isset($id_user)) {
			$where[] = " io.id_buyer = ? ";
			$params[] = $id_user;
		}

		if (isset($id_seller)) {
			$where[] = " io.id_seller = ? ";
			$params[] = $id_seller;
		}

		if (isset($price_from)) {
			$where[] = " io.price >= ?";
            $params[] = $price_from;
        }

		if (isset($price_to)) {
			$where[] = " io.price <= ?";
            $params[] = $price_to;
        }

        if (isset($cancel_request)) {
			$where[] = " io.cancel_request = ?";
            $params[] = $cancel_request;
        }

        if (isset($dispute_opened)) {
			$where[] = " io.dispute_opened = ?";
            $params[] = $dispute_opened;
        }

		if (isset($ship_to_country)) {
			$where[] = " io.ship_to_country = ?";
            $params[] = $ship_to_country;
        }

		if (isset($ship_to_city)) {
			$where[] = " io.ship_to_city = ?";
            $params[] = $ship_to_city;
        }

		if (isset($ship_from_country)) {
			$where[] = " io.ship_from_country = ?";
            $params[] = $ship_from_country;
        }

		if (isset($ship_from_city)) {
			$where[] = " io.ship_from_city = ?";
            $params[] = $ship_from_city;
        }

		if (isset($date_val) && $date_val == 'create') {
			if (isset($date_from)) {
                $where[] = " DATE(io.order_date) >= ? ";
                $params[] = $date_from;
			}

			if (isset($date_to)) {
                $where[] = " DATE(io.order_date) <= ? ";
                $params[] = $date_to;
			}
		}

		if (isset($date_val) && $date_val == 'update') {
			if (isset($date_from)) {
				$where[] = " DATE(io.order_date) >= ? ";
				$params[] = $date_from;
			}

			if (isset($date_to)) {
				$where[] = " DATE(io.order_date) <= ? ";
				$params[] = $date_to;
			}
		}

		if (!empty($keywords)) {
			$words = explode(' ', $keywords);
			if(count($words) > 1){
				$where[] = " MATCH (io.search_info) AGAINST (?) ";
				$params[] = $keywords;
			} else{
				$where[] = " io.search_info LIKE ? ";
                $params[] = '%' . $keywords . '%';
			}
		}

		if (isset($id_item)) {
			$joins[] = " INNER JOIN $this->ordered_items_table iod ON iod.id_order = io.id ";
			$where[] = " iod.id_item = ? ";
			$params[] = $id_item;
		}

		$joins = empty($joins) ? ' ' : ' ' . implode(' ', $joins) . ' ';

        $sql = "SELECT COUNT(*) as count_expired
                FROM $this->item_orders_table io
				$joins";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
        }

		return $this->db->query_one($sql, $params)['count_expired'];
    }

    public function get_statuses_counters($conditions){
        extract($conditions);
		$where = $params = $joins = [];

		if (isset($ep_manager)) {
			$where[] = " io.ep_manager = ? ";
			$params[] = $ep_manager;
		}

        if (isset($assigned_manager_email)) {
            $joins[] = " LEFT JOIN {$this->users_table} u ON io.ep_manager = u.idu ";
            $where[] = " u.email = ? ";
			$params[] = $assigned_manager_email;
        }

		if(isset($expire_status)){
			switch ($expire_status) {
				case 'expired':
					$where[] = " (UNIX_TIMESTAMP(io.status_countdown) <= UNIX_TIMESTAMP()) ";
				break;
				case 'expire_soon':
					$where[] = " (UNIX_TIMESTAMP(io.status_countdown) <= (UNIX_TIMESTAMP() + ?*86400)) AND (UNIX_TIMESTAMP(io.status_countdown) - (UNIX_TIMESTAMP())) > 0 ";
					$params[] = $this->expire_soon_days;
				break;
			}
		}

		if (isset($id_order)) {
			$where[] = " io.id = ? ";
			$params[] = $id_order;
		}

		if (isset($id_orders)) {
            $id_orders = getArrayFromString($id_orders);
			$where[] = " io.id IN (" . implode(',', array_fill(0, count($id_orders), '?')) . ") ";
			array_push($params, ...$id_orders);
		}

		if (isset($id_user)) {
			$where[] = " io.id_buyer = ? ";
			$params[] = $id_user;
		}

		if (isset($id_seller)) {
			$where[] = " io.id_seller = ? ";
			$params[] = $id_seller;
		}

		if (isset($price_from)) {
			$where[] = " io.price >= ?";
            $params[] = $price_from;
        }

		if (isset($price_to)) {
			$where[] = " io.price <= ?";
            $params[] = $price_to;
        }

        if (isset($cancel_request)) {
			$where[] = " io.cancel_request = ?";
            $params[] = $cancel_request;
        }

        if (isset($dispute_opened)) {
			$where[] = " io.dispute_opened = ?";
            $params[] = $dispute_opened;
        }

		if (isset($ship_to_country)) {
			$where[] = " io.ship_to_country = ?";
            $params[] = $ship_to_country;
        }

		if (isset($ship_to_city)) {
			$where[] = " io.ship_to_city = ?";
            $params[] = $ship_to_city;
        }

		if (isset($ship_from_country)) {
			$where[] = " io.ship_from_country = ?";
            $params[] = $ship_from_country;
        }

		if (isset($ship_from_city)) {
			$where[] = " io.ship_from_city = ?";
            $params[] = $ship_from_city;
        }

		if (isset($date_val) && $date_val == 'create') {
			if (isset($date_from)) {
                $where[] = " DATE(io.order_date) >= ? ";
                $params[] = $date_from;
			}

			if (isset($date_to)) {
                $where[] = " DATE(io.order_date) <= ? ";
                $params[] = $date_to;
			}
		}

		if (isset($date_val) && $date_val == 'update') {
			if (isset($date_from)) {
				$where[] = " DATE(io.order_date) >= ? ";
				$params[] = $date_from;
			}

			if (isset($date_to)) {
				$where[] = " DATE(io.order_date) <= ? ";
				$params[] = $date_to;
			}
		}

		if (isset($keywords)) {
			$words = explode(' ', $keywords);
			if(count($words) > 1){
				$where[] = " MATCH (io.search_info) AGAINST (?) ";
				$params[] = $keywords;
			} else{
				$where[] = " io.search_info LIKE ? ";
                $params[] = '%' . $keywords . '%';
			}
		}

        if (isset($realUsers)) {
            $joins[] = " LEFT JOIN {$this->users_table} buyers ON buyers.idu = io.id_buyer ";
            $joins[] = " LEFT JOIN {$this->users_table} sellers ON sellers.idu = io.id_seller ";

            if ($realUsers) {
                $where[] = " buyers.fake_user = 0 ";
                $where[] = " buyers.is_model = 0 ";
                $where[] = " sellers.fake_user = 0 ";
                $where[] = " sellers.is_model = 0 ";
            } else {
                $where[] = " (buyers.fake_user = 1 OR buyers.is_model = 1 OR sellers.fake_user = 1 OR sellers.is_model = 1) ";
            }
        }

		if (isset($id_item)) {
			$joins[] = " INNER JOIN $this->ordered_items_table iod ON iod.id_order = io.id ";
			$where[] = " iod.id_item = ? ";
			$params[] = $id_item;
		}

		$joins = empty($joins) ? ' ' : ' ' . implode(' ', $joins) . ' ';

        $sql = "SELECT ios.alias as `status`, COUNT(io.`status`) as status_counter
                FROM $this->orders_status_table ios
                LEFT JOIN $this->item_orders_table io ON ios.id = io.`status`
				$joins";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
        }

		$sql .= " GROUP BY ios.alias ORDER BY ios.position ASC";

		return $this->db->query_all($sql, $params);
	}

	public function get_orders_by_expires_days($conditions = array()){
        $order_by = 'io.status_countdown';
		$limit = 50;
		$state = 'active';

		extract($conditions);

        $params = [];
        $where = [
            " io.status NOT IN (11,12,13,14,15) ", //exclude finished status
            "(
                (DATE_ADD(CURRENT_DATE(), INTERVAL 1 DAY) = DATE_FORMAT(io.status_countdown, '%Y-%m-%d') AND io.reminder_sent != 1)
                OR (DATE_ADD(CURRENT_DATE(), INTERVAL 3 DAY) = DATE_FORMAT(io.status_countdown, '%Y-%m-%d') AND io.reminder_sent != 3 AND DATE_FORMAT(io.status_countdown_updated, '%Y-%m-%d') != CURRENT_DATE())
                OR (DATE_ADD(CURRENT_DATE(), INTERVAL 5 DAY) = DATE_FORMAT(io.status_countdown, '%Y-%m-%d') AND io.reminder_sent != 5 AND DATE_FORMAT(io.status_countdown_updated, '%Y-%m-%d') != CURRENT_DATE())
            )",
        ];

        if (isset($state)){
			$where[] = " io.state = ?";
			$params[] = $state;
		}

		$sql = "SELECT io.*
					, os.status as order_status
					, os.notify_users
				, ((UNIX_TIMESTAMP(DATE_FORMAT(io.status_countdown, '%Y-%m-%d')) - (UNIX_TIMESTAMP(CURRENT_DATE()))) div 86400) as expire_days
			FROM item_orders io
			LEFT JOIN orders_status os ON io.status = os.id";

        $sql .= " WHERE " . implode(" AND ", $where);
		$sql .= " ORDER BY " . $order_by;
		$sql .= " LIMIT " . $limit;

		return $this->db->query_all($sql, $params);
	}

	public function get_orders_expires_soon($conditions = array()){
        $order_by = 'io.status_countdown';
		$limit = 50;
		$auto_extend = 0;
		$state = 'active';

		extract($conditions);

		$params = [];
        $where = [
            " ((UNIX_TIMESTAMP(io.status_countdown) < UNIX_TIMESTAMP()) AND io.reminder_sent = 1) ",
            " io.status NOT IN (11,12,13,14,15) ",
        ];

		if (isset($auto_extend)){
			$where[] = " io.auto_extend = ?";
            $params[] = $auto_extend;
        }

		if (isset($request_auto_extend)) {
			$where[] = " io.request_auto_extend = ?";
            $params[] = $request_auto_extend;
		}

		if (isset($state)){
			$where[] = " io.state = ?";
			$params[] = $state;
		}

		$sql = "SELECT io.*
				, os.status as order_status
				, os.notify_users
			FROM item_orders io
			LEFT JOIN orders_status os ON io.status = os.id";

        $sql .= " WHERE " . implode(" AND ", $where);
		$sql .= " ORDER BY " . $order_by;
		$sql .= " LIMIT " . $limit;

		return $this->db->query_all($sql, $params);
	}

	public function update_order_status_by_alias($alias, $data) {
        $this->db->where('alias', $alias);

        return empty($data) ? false : $this->db->update($this->orders_status_table, $data);
	}

	/**
     * @deprecated
     *
     * @param mixed $id_shipper
     * @param mixed $conditions
     */
    public function _get_orders_for_bidding_params($id_shipper = 0, $conditions = array())
    {
        $this->db->from("{$this->item_orders_table} io");
        $this->db->join("{$this->orders_status_table} os", 'os.id = io.status', 'inner');
        $this->db->join("{$this->orders_shippers_quotes_table} sb", "io.id = sb.id_order AND sb.id_shipper = {$id_shipper}", 'left outer');
        $this->db->join("{$this->item_order_invoices_table} ioi", 'io.id_invoice = ioi.id_invoice', 'inner');

        $this->db->where_raw("os.alias = 'invoice_confirmed'");
        $this->db->where_raw('sb.id_quote IS NULL');

        $this->db->where_raw('EXISTS (SELECT users.idu FROM users WHERE users.idu = io.id_buyer AND users.fake_user = 0)');
        $this->db->where_raw('EXISTS (SELECT users.idu FROM users WHERE users.idu = io.id_seller AND users.fake_user = 0)');

        extract($conditions);

        if (isset($order) && !empty($order)) {
            $this->db->where('io.id = ?', (int) $order);
        }
        if (isset($departure_country) && !empty($departure_country)) {
            $this->db->where('io.ship_from_country = ?', (int) $departure_country);
        }
        if (isset($departure_region) && !empty($departure_region)) {
            $this->db->where('io.ship_from_state = ?', (int) $departure_region);
        }
        if (isset($departure_city) && !empty($departure_city)) {
            $this->db->where('io.ship_from_city = ?', (int) $departure_city);
        }
        if (isset($destination_country) && !empty($destination_country)) {
            $this->db->where('io.ship_to_country = ?', (int) $destination_country);
        }
        if (isset($destination_region) && !empty($destination_region)) {
            $this->db->where('io.ship_to_state = ?', (int) $destination_region);
        }
        if (isset($destination_city) && !empty($destination_city)) {
            $this->db->where('io.ship_to_city = ?', (int) $destination_city);
        }
        if (isset($created_from) && !empty($created_from)) {
            $this->db->where('io.order_date >=', $created_from);
        }
        if (isset($created_to) && !empty($created_to)) {
            $this->db->where('io.order_date <=', $created_to);
        }
        if (isset($updated_from) && !empty($updated_from)) {
            $this->db->where('io.update_date >=', $updated_from);
        }
        if (isset($updated_to) && !empty($updated_to)) {
            $this->db->where('io.update_date <=', $updated_to);
        }
        if (isset($expires_from) && !empty($expires_from)) {
            $this->db->where('io.status_countdown >=', $expires_from);
        }
        if (isset($expires_to) && !empty($expires_to)) {
            $this->db->where('io.status_countdown <=', $expires_to);
		}
		if (isset($not_expired) && $not_expired) {
			$this->db->where_raw('io.status_countdown > ?', date('Y-m-d H:i:s'));
		}
        if (isset($package) && !empty($package) && is_array($package)) {
            foreach ($package as $type => $range) {
                if (isset($range['min'])) {
                    $this->db->where_raw("io.package_detail->>'$.{$type}' >= CAST(? as DECIMAL)", $range['min']);
                }
                if (isset($range['max'])) {
                    $this->db->where_raw("io.package_detail->>'$.{$type}' <= CAST(? as DECIMAL)", $range['max']);
                }
            }
        }
        if (isset($id_country)) {
            if (!is_array($id_country)) {
                $id_country = explode(',', $id_country);
            }

            if (!empty($id_country)) {
                $this->db->in('io.ship_from_country', $id_country);
                $this->db->in('io.ship_to_country', $id_country);
            }
        }
        if (isset($id_shipment_type)) {
            if (is_array($id_shipment_type)) {
                $this->db->in('io.shipment_type', $id_shipment_type);
            } else {
                $this->db->where('io.shipment_type = ?', $id_shipment_type);
            }
        }
        if (isset($min_weight)) {
            $this->db->where("io.package_detail->'$.weight' >=", $min_weight);
        }
        if (isset($max_weight)) {
            $this->db->where("io.package_detail->'$.weight' <=", $max_weight);
        }
        if (isset($quote_status) && is_string($quote_status)) {
            $this->db->where("sb.quote_status =", $quote_status);
        }
    }

    /**
     * @deprecated
     *
     * @param mixed $id_shipper
     * @param mixed $conditions
     */
    public function get_orders_for_bidding($id_shipper = 0, $conditions = array())
    {
        $order_by = 'io.order_date DESC';

        $this->db->select('io.*, os.alias as order_status_alias, os.status as order_status_title, os.description as order_status_description, ioi.products');
        $this->_get_orders_for_bidding_params($id_shipper, $conditions);

        if (isset($conditions['sort_by'])) {
            $multi_order_by = array();
            foreach ($conditions['sort_by'] as $sort_item) {
                $sort_item = explode('-', $sort_item);
                $multi_order_by[] = $sort_item[0] . ' ' . $sort_item[1];
            }

            if (!empty($multi_order_by)) {
                $order_by = implode(',', $multi_order_by);
            }
        }

        $this->db->orderby($order_by);
        $this->db->limit((int) $conditions['per_p'], (int) $conditions['start']);

        return $this->db->query_all() ?: [];
    }

    /**
     * @deprecated
     *
     * @param mixed $id_shipper
     * @param mixed $conditions
     */
    public function count_orders_for_bidding($id_shipper = 0, $conditions = array())
    {
        $this->db->select('COUNT(io.id) as total_rows');
        $this->_get_orders_for_bidding_params($id_shipper, $conditions);

        return (int) $this->db->query_one()['total_rows'];
    }

    /**
     * Resolves static relationships with buyer.
     */
    protected function buyer(): RelationInterface
    {
        /** @var User_Model $users */
        $users = model(User_Model::class);
        $relation = $this->belongsTo(
            new PortableModel($this->getHandler(), $table = $users->get_users_table(), $primaryKey = $users->get_users_table_primary_key()),
            'id_buyer'
        );
        $relation->disableNativeCast();
        /** @var User_Groups_Model $userGroups */
        $userGroups = model(User_Groups_Model::class);
        $userGroupsTable = $userGroups->getTable();
        $userGroupsPK = $userGroups->getPrimaryKey();

        $builder = $relation->getQuery();
        $builder
            ->leftJoin($table, $userGroupsTable, $userGroupsTable, "`{$table}`.`user_group` = `{$userGroupsTable}`.{$userGroupsPK}")
            ->select(
                $primaryKey,
                "{$primaryKey} as `id`",
                "`{$table}`.`user_photo` AS `photo`",
                "`{$table}`.`fname` AS firstname",
                "`{$table}`.`lname` AS lastname",
                "TRIM(CONCAT(`{$table}`.`fname`, ' ', `{$table}`.`lname`)) as `fullname`",
                "`{$table}`.`user_group` AS `group`",
                "`{$userGroupsTable}`.`gr_name` AS `group_name`",
            )
        ;

        return $relation;
    }

    /**
     * Resolves static relationships with seller.
     */
    protected function seller(): RelationInterface
    {
        /** @var User_Model $users */
        $users = model(User_Model::class);
        /** @var Company_Model $companies */
        $companies = model(Company_Model::class);
        $companiesTable = $companies->get_company_table();
        $companiesPrimaryKey = $companies->get_company_table_primary_key();
        $relation = $this->belongsTo(
            new PortableModel($this->getHandler(), $table = $users->get_users_table(), $primaryKey = $users->get_users_table_primary_key()),
            'id_seller'
        );
        $relation->disableNativeCast();
        /** @var User_Groups_Model $userGroups */
        $userGroups = model(User_Groups_Model::class);
        $userGroupsTable = $userGroups->getTable();
        $userGroupsPK = $userGroups->getPrimaryKey();

        $builder = $relation->getQuery();
        $builder
            ->leftJoin($table, $companiesTable, $companiesTable, "`{$companiesTable}`.`id_user` = {$primaryKey}")
            ->leftJoin($table, $userGroupsTable, $userGroupsTable, "`{$table}`.`user_group` = `{$userGroupsTable}`.{$userGroupsPK}")
            ->select(
                $primaryKey,
                "{$primaryKey} as `id`",
                "`{$table}`.`user_photo` AS `photo`",
                "`{$table}`.`fname` AS firstname",
                "`{$table}`.`lname` AS lastname",
                "TRIM(CONCAT(`{$table}`.`fname`, ' ', `{$table}`.`lname`)) as `fullname`",
                "`{$companiesTable}`.`{$companiesPrimaryKey}` as `company_id`",
                "`{$companiesTable}`.`name_company` as `company_name`",
                "`{$companiesTable}`.`legal_name_company` as `legal_company_name`",
                "`{$companiesTable}`.`logo_company` as `logo`",
                "`{$companiesTable}`.`index_name` as `slug`",
                "`{$companiesTable}`.`type_company` as `type`",
                "`{$table}`.`user_group` AS `group`",
                "`{$userGroupsTable}`.`gr_name` AS `group_name`",
            )
            ->andWhere(
                $builder->expr()->eq(
                    "`{$companiesTable}`.`type_company`",
                    $builder->createNamedParameter('company', ParameterType::STRING, ':companyType')
                )
            )
        ;

        return $relation;
    }

    /**
     * Resolves static relationships with shipper.
     */
    protected function shipper(): RelationInterface
    {
        /** @var User_Model $users */
        $users = model(User_Model::class);
        /** @var Shippers_Model $shippers */
        $shippers = model(Shippers_Model::class);
        $shippersTable = $shippers->getShippersTable();
        $shippersPrimaryKey = $shippers->getShippersTablePrimaryKey();
        $relation = $this->belongsTo(
            new PortableModel($this->getHandler(), $table = $users->get_users_table(), $primaryKey = $users->get_users_table_primary_key()),
            'id_shipper'
        );
        $relation->disableNativeCast();
        /** @var User_Groups_Model $userGroups */
        $userGroups = model(User_Groups_Model::class);
        $userGroupsTable = $userGroups->getTable();
        $userGroupsPK = $userGroups->getPrimaryKey();

        $builder = $relation->getQuery();
        $builder
            ->leftJoin($table, $shippersTable, $shippersTable, "`{$shippersTable}`.`id_user` = {$primaryKey}")
            ->leftJoin($table, $userGroupsTable, $userGroupsTable, "`{$table}`.`user_group` = `{$userGroupsTable}`.{$userGroupsPK}")
            ->select(
                $primaryKey,
                "{$primaryKey} as `id`",
                "`{$table}`.`user_photo` AS `photo`",
                "`{$table}`.`fname` AS firstname",
                "`{$table}`.`lname` AS lastname",
                "TRIM(CONCAT(`{$table}`.`fname`, ' ', `{$table}`.`lname`)) as `fullname`",
                "`{$shippersTable}`.`{$shippersPrimaryKey}` as `company_id`",
                "`{$shippersTable}`.`co_name` AS company_name",
                "`{$shippersTable}`.`legal_co_name` AS legal_company_name",
                "`{$shippersTable}`.`logo`",
                "`{$table}`.`user_group` AS `group`",
                "`{$userGroupsTable}`.`gr_name` AS `group_name`",
            )
        ;

        return $relation;
    }

    /**
     * Resolves static relationships with shipper.
     */
    protected function internationalShippers(): RelationInterface
    {
        /** @var Ishippers_Model $shippers */
        $shippers = model(Ishippers_Model::class);
        $relation = $this->belongsTo(
            new PortableModel($this->getHandler(), $table = $shippers->get_shippers_table(), $primaryKey = $shippers->get_shippers_table_primary_key()),
            'id_shipper'
        );
        $relation->disableNativeCast();
        $builder = $relation->getQuery();
        $builder
            ->select(
                $primaryKey,
                "{$primaryKey} as `id`",
                "`{$table}`.`shipper_name` AS company_name",
                "`{$table}`.`shipper_original_name` AS legal_company_name",
                "`{$table}`.`shipper_logo` AS `logo`",
            )
        ;

        return $relation;
    }
}
