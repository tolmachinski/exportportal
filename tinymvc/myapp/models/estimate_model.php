<?php
/**
 *Estimate_model.php.
 *
 *Inquiry system model
 *
 * @deprecated in favor of \Product_Estimate_Requests_Model
 */
class Estimate_Model extends TinyMVC_Model
{
    private $items_table = 'items';
    private $users_table = 'users';
    private $request_estimate_table = 'item_request_estimate';

    public function set_request_estimate($data)
    {
        return $this->db->insert($this->request_estimate_table, $data);
    }

    public function delete_request_estimate($id_estimate)
    {
        $this->db->where('id_request_estimate', $id_estimate);

        return $this->db->delete($this->request_estimate_table);
    }

    public function update_request_estimate($id_estimate, $data)
    {
        $this->db->where('id_request_estimate', $id_estimate);

        return $this->db->update($this->request_estimate_table, $data);
    }

    public function change_request_estimate_log($id_estimate, $log_string)
    {
        $sql = "UPDATE {$this->request_estimate_table} SET log = IF('' != log, CONCAT_WS(',', log, ?), ?) WHERE id_request_estimate = ?";

        return $this->db->query($sql, array(
            $log_string,
            $log_string,
            $id_estimate,
        ));
    }

    public function get_request_estimate($id_estimate, array $conditions = [])
    {
        $where = [];
        $params = [];

        $where[] = " e.id_request_estimate = ? ";
        $params[] = $id_estimate;

        extract($conditions);

        if (isset($id_seller)) {
			$where[] = " e.id_seller = ? ";
			$params[] = $id_seller;
		}

		if (isset($id_buyer)) {
			$where[] = " e.id_buyer = ? ";
			$params[] = $id_buyer;
		}

        $sql = "SELECT e.*, e.detail_item as detail_item_params,
                    it.title, it.description as detail_item,
                    ic.name, ic.p_or_m,
                    iph.photo_name
				FROM {$this->request_estimate_table} e
				INNER JOIN {$this->items_table} it ON e.id_item = it.id
				INNER JOIN item_category ic ON it.id_cat = ic.category_id
				LEFT JOIN item_photo iph ON e.id_item = iph.sale_id AND iph.main_photo = 1 ";

        if (count($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        return $this->db->query_one($sql, $params);
    }

    public function get_request_estimates(array $conditions = array())
    {
        $page = 0;
        $per_p = 20;
        $where = array();
        $params = array();
        $order_by = 'update_date DESC';
        $check_state = true;

        extract($conditions);

        if (isset($status) && 'all' != $status) {
            $where[] = ' e.status = ?';
            $params[] = $status;
        }

        if (isset($seller)) {
            $where[] = ' e.id_seller = ? ';
            $params[] = $seller;

            if ($check_state) {
                $where[] = ' e.state_seller = ? ';
                if (isset($state_seller)) {
                    $params[] = $state_seller;
                } else {
                    $params[] = 0;
                }
            }
        }

        if (isset($buyer)) {
            $where[] = ' e.id_buyer = ? ';
            $params[] = $buyer;

            if ($check_state) {
                $where[] = ' e.state_buyer = ? ';
                if (isset($state_buyer)) {
                    $params[] = $state_buyer;
                } else {
                    $params[] = 0;
                }
            }
        }

        if (isset($item)) {
            $where[] = ' e.id_item = ?';
            $params[] = $item;
        }

        if (isset($estimate_number)) {
            $where[] = ' e.id_request_estimate = ? ';
            $params[] = $estimate_number;
        }

        if (isset($expire_soon)) {
            $where[] = ' DATEDIFF(e.expire_date, NOW()) <= ? AND DATEDIFF(e.expire_date, NOW()) >= 0 ';
            $params[] = $expire_soon;
            if (!isset($status) || 'all' == $status) {
                $where[] = " e.status IN ('new', 'wait_buyer', 'wait_seller') ";
            }
        }

        if (isset($start_from)) {
            $where[] = ' DATE(e.create_date) >= ?';
            $params[] = $start_from;
        }

        if (isset($start_to)) {
            $where[] = ' DATE(e.create_date) <= ?';
            $params[] = $start_to;
        }

        if (isset($update_from)) {
            $where[] = ' DATE(e.update_date) >= ?';
            $params[] = $update_from;
        }

        if (isset($update_to)) {
            $where[] = ' DATE(e.update_date) <= ?';
            $params[] = $update_to;
        }

        if (isset($sort_by)) {
            foreach ($sort_by as $sort_item) {
                $sort_item = explode('-', $sort_item);
                $multi_order_by[] = $sort_item[0] . ' ' . $sort_item[1];
            }

            $order_by = implode(',', $multi_order_by);
        }

        if (isset($keywords)) {
            if (str_word_count_utf8($keywords) > 1) {
                $order_by = $order_by . ', REL DESC';
                $where[] = ' MATCH (e.for_search) AGAINST (?)';
                $params[] = $keywords;
                $rel = " , MATCH (e.for_search) AGAINST (?) as REL";
                array_unshift($params, $keywords);
            } else {
                $where[] = " e.for_search LIKE ? ";
                $params[] = '%' . $keywords . '%';
            }
        }

        $sql = 'SELECT e.*, DATE_ADD(e.create_date, INTERVAL days DAY) as expired_date,
					it.title, ic.name, ic.p_or_m, iph.photo_name ' . $rel . '
                FROM ' . $this->request_estimate_table . ' e
				INNER JOIN ' . $this->items_table . ' it ON e.id_item = it.id
				INNER JOIN item_category ic ON it.id_cat = ic.category_id
				LEFT JOIN item_photo iph ON e.id_item = iph.sale_id AND iph.main_photo = 1';

        if (count($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' GROUP BY e.id_request_estimate ';
        $sql .= ' ORDER BY ' . $order_by;

        if (isset($limit)) {
            $sql .= ' LIMIT ' . $limit;
        } else {
            if (!isset($count)) {
                $count = $this->counter_by_conditions_request($conditions);
            }

            $pages = ceil($count / $per_p);

            if (!isset($start)) {
                if ($page > $pages) {
                    $page = $pages;
                }
                $start = ($page - 1) * $per_p;

                if ($start < 0) {
                    $start = 0;
                }
            }

            $sql .= ' LIMIT ' . $start;

            if ($per_p > 0) {
                $sql .= ',' . $per_p;
            }
        }

        return $this->db->query_all($sql, $params);
    }

    public function counter_by_conditions_request(array $conditions = array())
    {
        $page = 1;
        $per_p = 20;
        $where = array();
        $params = array();
        $all_estimates = 0;
        $check_state = true;

        extract($conditions);

        if (isset($status) && 'all' != $status) {
            $where[] = ' e.status = ?';
            $params[] = $status;
        }

        if (isset($seller)) {
            $where[] = ' e.id_seller = ? ';
            $params[] = $seller;

            if ($check_state) {
                $where[] = ' e.state_seller = ? ';
                if (isset($state_seller)) {
                    $params[] = $state_seller;
                } else {
                    $params[] = 0;
                }
            }
        }

        if (isset($buyer)) {
            $where[] = ' e.id_buyer = ? ';
            $params[] = $buyer;

            if ($check_state) {
                $where[] = ' e.state_buyer = ? ';
                if (isset($state_buyer)) {
                    $params[] = $state_buyer;
                } else {
                    $params[] = 0;
                }
            }
        }

        if (isset($item)) {
            $where[] = ' e.id_item = ?';
            $params[] = $item;
        }

        if (isset($estimate_number)) {
            $where[] = ' e.id_request_estimate = ?';
            $params[] = $estimate_number;
        }

        if (isset($expire_soon)) {
            $where[] = ' DATEDIFF(e.expire_date, NOW()) <= ? AND DATEDIFF(e.expire_date, NOW()) >= 0 ';
            $params[] = $expire_soon;
            if (!isset($status) || 'all' == $status) {
                $where[] = " e.status IN ('new', 'wait_buyer', 'wait_seller') ";
            }
        }

        if (isset($start_from)) {
            $where[] = ' DATE(e.create_date) >= ?';
            $params[] = $start_from;
        }

        if (isset($start_to)) {
            $where[] = ' DATE(e.create_date) <= ?';
            $params[] = $start_to;
        }

        if (isset($update_from)) {
            $where[] = ' DATE(e.update_date) >= ?';
            $params[] = $update_from;
        }

        if (isset($update_to)) {
            $where[] = ' DATE(e.update_date) <= ?';
            $params[] = $update_to;
        }

        if (isset($status_full)) {
            $select = ',e.* ';
            $group_by = 'e.status';
            $all_estimates = 1;
        }

        if (isset($keywords)) {
            $words = explode(' ', $keywords);
            if (count($words) > 1) {
                $where[] = ' MATCH (e.for_search) AGAINST (?)';
                $params[] = $keywords;
            } else {
                $where[] = " e.for_search LIKE ? ";
                $params[] = '%' . $keywords . '%';
            }
        }

        $sql = 'SELECT COUNT(*) as counter
				' . $select . '
                FROM ' . $this->request_estimate_table . ' e ';

        if (count($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        if (isset($group_by)) {
            $sql .= ' GROUP BY ' . $group_by;
        }

        if ($all_estimates) {
            return $this->db->query_all($sql, $params);
        }
        $rez = $this->db->query_one($sql, $params);

        return $rez['counter'];
    }

    public function count_estimates_request_by_statuses(array $conditions = array())
    {
        $where = array();
        $params = array();
        $check_state = true;

        extract($conditions);

        if (isset($id_seller)) {
            $where[] = ' id_seller = ? ';
            $params[] = $id_seller;

            if ($check_state) {
                $where[] = ' state_seller = ? ';
                if (isset($state_seller)) {
                    $params[] = $state_seller;
                } else {
                    $params[] = 0;
                }
            }
        }

        if (isset($id_buyer)) {
            $where[] = ' id_buyer = ? ';
            $params[] = $id_buyer;

            if ($check_state) {
                $where[] = ' state_buyer = ? ';
                if (isset($state_buyer)) {
                    $params[] = $state_buyer;
                } else {
                    $params[] = 0;
                }
            }
        }

        if (isset($keywords)) {
            $where[] = ' MATCH (for_search) AGAINST (?)';
            $params[] = $keywords;
        }

        $sql = "SELECT status, COUNT(*)	as counter FROM {$this->request_estimate_table}";
        if (count($where)) {
            $sql .= ' WHERE ' . implode(' AND', $where);
        }

        $sql .= ' GROUP BY status ';

        return $this->db->query_all($sql, $params);
    }

    public function get_estimates_request_last_id()
    {
        $sql = "SELECT id_request_estimate
				FROM {$this->request_estimate_table}
				ORDER BY id_request_estimate DESC
				LIMIT 0, 1";

        $rez = $this->db->query_one($sql);

        if (!empty($rez['id_request_estimate'])) {
            return $rez['id_request_estimate'];
        }

        return 0;
    }

    public function get_soon_expire_estimates($days = 0)
    {
        $sql = "SELECT id_request_estimate, status, id_seller, id_buyer, expire_date
				FROM {$this->request_estimate_table}
                WHERE DATEDIFF(expire_date, NOW()) <= ? AND status IN ('new', 'wait_buyer', 'wait_seller')";

        return $this->db->query_all($sql, array($days));
    }

    public function set_estimates_expire_by_list($list)
    {
        $this->db->in('id_request_estimate', $list);

        return $this->db->update($this->request_estimate_table, array('status'=>'expired'));
    }

    //	CHANGE ESTIMATES STATUS TO ARCHIVED FOR SELLER AND BUYER
    public function set_estimates_archived()
    {
        $this->db->where('status = ?', 'initiated');
        $this->db->where_raw("NOW() > DATE_ADD(update_date, INTERVAL 1 WEEK)");

        return $this->db->update($this->request_estimate_table, array(
            'state_seller' => 1,
            'state_buyer'  => 1,
        ));
    }

    public function clear_estimates()
    {
        $sql = "DELETE
				FROM {$this->request_estimate_table}
				WHERE state_buyer = 2 AND state_seller = 2 AND status IN ('expired', 'declined', 'new')";

        return  $this->db->query($sql);
    }
}
