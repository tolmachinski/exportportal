<?php
/**
 *offers_model.php.
 *
 *offers system model
 *
 *@author Litra Andrei
 */
class Offers_Model extends TinyMVC_Model
{
    private $offers_table = 'item_offers';
    private $items_table = 'items';

    // INSERT NEW OFFER
    public function set_offer($data)
    {
        $this->db->insert($this->offers_table, $data);

        return $this->db->last_insert_id();
    }

    // GET OFFER INFO
    public function get_offer($id_offer, array $conditions = [])
    {
        $where = [];
        $params = [];

        $where[] = " io.id_offer = ? ";
        $params[] = $id_offer;

        extract($conditions);

        if (isset($id_seller)) {
			$where[] = " io.id_seller = ? ";
			$params[] = $id_seller;
		}

		if (isset($id_buyer)) {
			$where[] = " io.id_buyer = ? ";
			$params[] = $id_buyer;
		}

        $sql = 'SELECT io.*,
                        it.title
				FROM ' . $this->offers_table . ' io
				INNER JOIN ' . $this->items_table . ' it ON io.id_item = it.id';

        if (count($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        return $this->db->query_one($sql, $params);
    }

    // UPDATE OFFERS DATA
    public function update_offer($id_offer, $data)
    {
        $this->db->where('id_offer', $id_offer);

        return $this->db->update($this->offers_table, $data);
    }

    // CHANGE OFFERS STATUS
    public function change_offers_status($id_offer, $status)
    {
        return $this->update_offer($id_offer, array('status' => $status));
    }

    // UPDATE OFFERS LOG
    public function change_offers_comments($id_offer, $comment)
    {
        $sql = "UPDATE {$this->offers_table} SET comments = IF('' != comments, CONCAT_WS(',', comments, ?), ?) WHERE id_offer = ?";

        return $this->db->query($sql, array(
            $comment,
            $comment,
            $id_offer,
        ));
    }

    // GET OFFERS BY CONDITIONS
    public function get_offers($conditions)
    {
        $page = 0;
        $per_p = 20;
        $order_by = 'update_op DESC';
        $check_state = true;
        $rel = '';

        extract($conditions);

        $where = $params = [];

        if (isset($sort_by)) {
            foreach ($sort_by as $sort_item) {
                $sort_item = explode('-', $sort_item);
                $multi_order_by[] = $sort_item[0] . ' ' . $sort_item[1];
            }

            $order_by = implode(',', $multi_order_by);
        }

        if (isset($seller)) {
            $where[] = ' io.id_seller = ? ';
            $params[] = $seller;

            if ($check_state) {
                $where[] = ' io.state_seller = ? ';
                $params[] = $state_seller ?? 0;
            }
        }

        if (isset($buyer)) {
            $where[] = ' io.id_buyer = ? ';
            $params[] = $buyer;

            if ($check_state) {
                $where[] = ' io.state_buyer = ? ';
                $params[] = $state_buyer ?? 0;
            }
        }

        if (isset($status) && 'all' != $status) {
            $where[] = ' io.status = ? ';
            $params[] = $status;
        } elseif (isset($expire_soon)) {
            $where[] = " io.status IN ('new', 'wait_buyer', 'wait_seller') ";
        }

        if (isset($item)) {
            $where[] = ' io.id_item = ? ';
            $params[] = $item;
        }

        if (isset($price)) {
            $where[] = ' io.new_price = ? ';
            $params[] = $price;
        }

        if (isset($to_user)) {
            $where[] = ' io.to_user = ? ';
            $params[] = $to_user;
        }

        if (isset($offer_number)) {
            $where[] = ' io.id_offer = ? ';
            $params[] = $offer_number;
        }

        if (isset($expire_soon)) {
            $where[] = ' (io.date_offer+io.days*86400) - UNIX_TIMESTAMP() <= ?*86400 AND (io.date_offer+io.days*86400) - UNIX_TIMESTAMP() > 0 ';
            $params[] = $expire_soon;
        }

        if (isset($update_from)) {
            $where[] = ' DATE(io.update_op) >= ?';
            $params[] = $update_from;
        }
        if (isset($update_to)) {
            $where[] = ' DATE(io.update_op) <= ?';
            $params[] = $update_to;
        }

        if (isset($keywords)) {
            if (str_word_count_utf8($keywords) > 1) {
                $order_by = $order_by . ', REL DESC';
                $where[] = ' MATCH (io.for_search) AGAINST (?)';
                $params[] = $keywords;
                $rel = " , MATCH (io.for_search) AGAINST (?) as REL";
                array_unshift($params, $keywords);
            } else {
                $where[] = " io.for_search LIKE ? ";
                $params[] = '%' . $keywords . '%';
            }
        }

        $sql = 'SELECT io.*, DATE_ADD(update_op, INTERVAL days DAY) as expired_date,
					it.title ' . $rel . '
                FROM ' . $this->offers_table . ' io
				INNER JOIN ' . $this->items_table . ' it ON io.id_item = it.id';

        if (count($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' GROUP BY id_offer ';

        $sql .= ' ORDER BY ' . $order_by;

        if (isset($limit)) {
            $sql .= ' LIMIT ' . $limit;
        } else {
            if (!isset($count)) {
                $count = $this->counter_by_conditions($conditions);
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

    // COUNT OFFERS BY CONDITIONS
    public function counter_by_conditions($conditions)
    {
        $all_offers = 0;
        $check_state = true;

        extract($conditions);

        $where = $params = [];

        if (isset($seller)) {
            $where[] = ' io.id_seller = ? ';
            $params[] = $seller;

            if ($check_state) {
                $where[] = ' io.state_seller = ? ';
                $params[] = $state_seller ?? 0;
            }
        }

        if (isset($buyer)) {
            $where[] = ' io.id_buyer = ? ';
            $params[] = $buyer;

            if ($check_state) {
                $where[] = ' io.state_buyer = ? ';
                $params[] = $state_buyer ?? 0;
            }
        }

        if (isset($status) && 'all' != $status) {
            $where[] = ' io.status = ? ';
            $params[] = $status;
        } elseif (isset($expire_soon)) {
            $where[] = " io.status IN ('new', 'wait_buyer', 'wait_seller') ";
        }

        if (isset($item)) {
            $where[] = ' io.id_item = ? ';
            $params[] = $item;
        }

        if (isset($offer_number)) {
            $where[] = ' io.id_offer = ? ';
            $params[] = $offer_number;
        }

        if (isset($expire_soon)) {
            $where[] = ' (io.date_offer+io.days*86400) - UNIX_TIMESTAMP() <= ?*86400 AND (io.date_offer+io.days*86400) - UNIX_TIMESTAMP() > 0 ';
            $params[] = $expire_soon;
        }

        if (isset($price)) {
            $where[] = ' io.new_price = ? ';
            $params[] = $price;
        }

        if (isset($to_user)) {
            $where[] = ' io.to_user = ? ';
            $params[] = $to_user;
        }

        if (isset($status_full)) {
            $select = ',io.* ';
            $group_by = 'io.status';
            $all_offers = 1;
        }

        if (isset($status_count)) {
            $select = ',io.status ';
            $group_by = 'io.status';
            $all_offers = 1;
        }

        if (isset($update_from)) {
            $where[] = ' DATE(io.update_op) >= ?';
            $params[] = $update_from;
        }
        if (isset($update_to)) {
            $where[] = ' DATE(io.update_op) <= ?';
            $params[] = $update_to;
        }

        if (isset($keywords)) {
            $words = explode(' ', $keywords);
            if (count($words) > 1) {
                $where[] = ' MATCH (io.for_search) AGAINST (?)';
                $params[] = $keywords;
            } else {
                $where[] = " io.for_search LIKE ? ";
                $params[] = '%' . $keywords . '%';
            }
        }

        $sql = 'SELECT COUNT(*)	as counter
				' . $select . '
                FROM ' . $this->offers_table . ' io ';

        if (count($where)) {
            $sql .= ' WHERE ' . implode(' AND', $where);
        }

        if (isset($group_by)) {
            $sql .= ' GROUP BY ' . $group_by;
        }

        return $all_offers ? $this->db->query_all($sql, $params) : $this->db->query_one($sql, $params)['counter'];
    }

    // OFFERS COUNTERS BY STATUSES
    public function count_offers_by_statuses($conditions = array())
    {
        $check_state = true;

        extract($conditions);

        $where = $params = [];

        if (isset($id_seller)) {
            $where[] = ' id_seller = ? ';
            $params[] = $id_seller;

            if ($check_state) {
                $where[] = ' state_seller = ? ';
                $params[] = $state_seller ?? 0;
            }
        }

        if (isset($id_buyer)) {
            $where[] = ' id_buyer = ? ';
            $params[] = $id_buyer;

            if ($check_state) {
                $where[] = ' state_buyer = ? ';
                $params[] = $state_buyer ?? 0;
            }
        }

        if (isset($keywords)) {
            $where[] = ' MATCH (for_search) AGAINST (?)';
            $params[] = $keywords;
        }

        $sql = 'SELECT status, COUNT(*)	as counter
                FROM ' . $this->offers_table;

        if (count($where)) {
            $sql .= ' WHERE ' . implode(' AND', $where);
        }

        $sql .= ' GROUP BY status ';

        return $this->db->query_all($sql, $params);
    }

    // GET LAST OFFER ID FOR ADMIN PAGE
    public function get_offers_last_id()
    {
        $sql = 'SELECT id_offer
				FROM ' . $this->offers_table . '
				ORDER BY id_offer DESC
				LIMIT 0,1';

        return $this->db->query_one($sql)['id_offer'] ?? 0;
    }

    // GET COUNT OF NEW OFFERS FOR ADMIN PAGE
    public function get_count_new_offers($id_offer)
    {
        $sql = "SELECT COUNT(*) as counter
				FROM $this->offers_table
				WHERE id_offer > ? ";

        return $this->db->query_one($sql, array($id_offer))['counter'];
    }

    public function get_soon_expire_offers($days)
    {
        $sql = "SELECT *
                FROM $this->offers_table
                WHERE (date_offer+days*86400) <= (UNIX_TIMESTAMP() + ?*86400) AND status IN ('new', 'wait_buyer', 'wait_seller', 'accepted')";

        return $this->db->query_all($sql, array($days));
    }

    //	CHANGE OFFERS STATUS TO EXPIRED
    public function set_offers_expired_by_list($list)
    {
        $this->db->in('id_offer', $list);

        return $this->db->update($this->offers_table, array('status'=>'expired'));
    }

    //	CHANGE OFFERS STATUS TO ARCHIVED FOR SELLER AND BUYER
    public function set_offers_archived()
    {
		$this->db->where('status = ?', 'initiated');
        $this->db->where_raw("NOW() > DATE_ADD(update_op, INTERVAL 1 WEEK)");

        return $this->db->update($this->offers_table, array(
            'state_seller' => 1,
            'state_buyer'  => 1,
		));
    }

    public function clear_offers()
    {
        $sql = "DELETE
				FROM $this->offers_table
				WHERE state_buyer = 2 AND state_seller = 2 AND status IN ('expired', 'declined', 'new')";

        return  $this->db->query($sql);
    }
}
