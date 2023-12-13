<?php

declare(strict_types=1);

use App\Common\Database\BaseModel;

/**
 *Po_model.php.
 *
 *Po system model
 *
 *@author
 *
 *@deprecated in favor of \Pos_Model
 */
class Po_Model extends BaseModel
{
    private $po_table = 'item_po';
    private $order_table = 'item_orders';
    private $prototype_table = 'item_prototype';
    private $items_table = 'items';
    private $users_table = 'users';

    public function set_po($data)
    {
        return $this->db->insert($this->po_table, $data);
    }

    public function update_po($id_po, $data)
    {
        $this->db->where('id_po', $id_po);

        return $this->db->update($this->po_table, $data);
    }

    public function get_po_last_id()
    {
        $sql = 'SELECT id_po
				FROM ' . $this->po_table . '
				ORDER BY id_po DESC
				LIMIT 0,1';

        return $this->db->query_one($sql)['id_po'] ?: 0;
    }

    public function get_count_new_po($id_po)
    {
        $sql = 'SELECT COUNT(*) as counter
				FROM ' . $this->po_table . '
				WHERE id_po > ? ';

        return $this->db->query_one($sql, array($id_po))['counter'];
    }

    public function get_po_one($id_po, $conditions = array())
    {
        extract($conditions);

        $where = ['id_po = ? '];
        $params = [$id_po];

        if (isset($seller)) {
            $where[] = ' po.id_seller = ? ';
            $params[] = $seller;
        }

        if (isset($buyer)) {
            $where[] = ' po.id_buyer = ? ';
            $params[] = $buyer;
        }

        $sql = 'SELECT po.*, o.state_seller as order_seller_achive, o.state_buyer as order_buyer_achive, it.title, os.status as order_status, os.icon as order_icon
				FROM ' . $this->po_table . ' po
				INNER JOIN ' . $this->items_table . ' it ON po.id_item = it.id
				LEFT JOIN ' . $this->order_table . ' o ON po.id_order = o.id
				LEFT JOIN orders_status os ON o.status = os.id ';

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        return $this->db->query_one($sql, $params);
    }

    public function get_po($conditions)
    {
        $page = 0;
        $per_p = 20;
        $order_by = 'po.change_date DESC';
        $rel = '';
        $check_state = true;

        extract($conditions);

        $where = $params = [];

        if (isset($sort_by)) {
            foreach ($sort_by as $sort_item) {
                $sort_item = explode('-', $sort_item);
                $multi_order_by[] = $sort_item[0] . ' ' . $sort_item[1];
            }

            $order_by = implode(',', $multi_order_by);
        }

        if (isset($status) && 'all' != $status) {
            $where[] = ' po.status = ? ';
            $params[] = $status;
        }

        if (isset($seller)) {
            $where[] = ' po.id_seller = ? ';
            $params[] = $seller;

            if ($check_state) {
                $where[] = ' po.state_seller = ? ';
                $params[] = $state_seller ?? 0;
            }
        }

        if (isset($buyer)) {
            $where[] = ' po.id_buyer = ? ';
            $params[] = $buyer;

            if ($check_state) {
                $where[] = ' po.state_buyer = ? ';
                $params[] = $state_buyer ?? 0;
            }
        }

        if (isset($item)) {
            $where[] = ' po.id_item = ? ';
            $params[] = $item;
        }

        if (isset($po_number)) {
            $where[] = ' po.id_po = ? ';
            $params[] = $po_number;
        }

        if (isset($price)) {
            $where[] = ' po.price = ? ';
            $params[] = $price;
        }

        if (isset($status_prototype)) {
            $where[] = ' ip.status_prototype = ? ';
            $params[] = $status_prototype;
        }

        if (isset($start_from)) {
            $where[] = ' DATE(po.date) >= ?';
            $params[] = $start_from;
        }
        if (isset($start_to)) {
            $where[] = ' DATE(po.date) <= ?';
            $params[] = $start_to;
        }

        if (isset($update_from)) {
            $where[] = ' DATE(po.change_date) >= ?';
            $params[] = $update_from;
        }
        if (isset($update_to)) {
            $where[] = ' DATE(po.change_date) <= ?';
            $params[] = $update_to;
        }

        if (isset($keywords)) {
            if (str_word_count_utf8($keywords) > 1) {
                $order_by = $order_by . ', REL DESC';
                $where[] = ' MATCH (po.for_search) AGAINST (?)';
                $params[] = $keywords;
                $rel = " , MATCH (po.for_search) AGAINST (?) as REL";
                array_unshift($params, $keywords);
            } else {
                $where[] = " po.for_search LIKE ? ";
                $params[] = '%' . $keywords . '%';
            }
        }

        $sql = 'SELECT po.*,
				ip.title, ip.image, ip.status_prototype, ip.changes as changes_prototype ' . $rel . '
                FROM ' . $this->po_table . ' po
				LEFT JOIN ' . $this->prototype_table . ' ip ON po.id_prototype = ip.id_prototype';

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= " GROUP BY id_po ORDER BY {$order_by}";

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

    public function counter_by_conditions(array $conditions = array())
    {
        $all_po = 0;
        $check_state = true;

        extract($conditions);

        $where = $params = [];

        if (isset($status) && 'all' != $status) {
            $where[] = ' po.status = ? ';
            $params[] = $status;
        }

        if (isset($seller)) {
            $where[] = ' po.id_seller = ? ';
            $params[] = $seller;

            if ($check_state) {
                $where[] = ' po.state_seller = ? ';
                $params[] = $state_seller ?? 0;
            }
        }

        if (isset($buyer)) {
            $where[] = ' po.id_buyer = ? ';
            $params[] = $buyer;

            if ($check_state) {
                $where[] = ' po.state_buyer = ? ';
                $params[] = $state_buyer ?? 0;
            }
        }

        if (isset($item)) {
            $where[] = ' po.id_item = ? ';
            $params[] = $item;
        }

        if (isset($po_number)) {
            $where[] = ' po.id_po = ? ';
            $params[] = $po_number;
        }

        if (isset($price)) {
            $where[] = ' po.price = ? ';
            $params[] = $price;
        }

        if (isset($start_from)) {
            $where[] = ' DATE(po.date) >= ?';
            $params[] = $start_from;
        }

        if (isset($start_to)) {
            $where[] = ' DATE(po.date) <= ?';
            $params[] = $start_to;
        }

        if (isset($update_from)) {
            $where[] = ' DATE(po.change_date) >= ?';
            $params[] = $update_from;
        }

        if (isset($update_to)) {
            $where[] = ' DATE(po.change_date) <= ?';
            $params[] = $update_to;
        }

        if (isset($status_full)) {
            $select = ',po.* ';
            $group_by = 'po.status';
            $all_po = 1;
        }

        if (isset($status_count)) {
            $select = ',po.status ';
            $group_by = 'po.status';
            $all_po = 1;
        }

        if (isset($keywords)) {
            $words = explode(' ', $keywords);
            if (count($words) > 1) {
                $where[] = ' MATCH (po.for_search) AGAINST (?)';
                $params[] = $keywords;
            } else {
                $where[] = " po.for_search LIKE ? ";
                $params[] = '%' . $keywords . '%';
            }
        }

        $sql = 'SELECT COUNT(*) as counter
				' . $select . '
                FROM ' . $this->po_table . ' po ';

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND', $where);
        }

        if (isset($group_by)) {
            $sql .= ' GROUP BY ' . $group_by;
        }

        return $all_po ? $this->db->query_all($sql, $params) : $this->db->query_one($sql, $params)['counter'];
    }

    public function count_po_by_statuses(array $conditions = array())
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
                FROM ' . $this->po_table;

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND', $where);
        }

        $sql .= ' GROUP BY status ';

        return $this->db->query_all($sql, $params);
    }

    public function change_po_log($id_po, $log_string)
    {
        $sql = "UPDATE {$this->po_table} SET log = IF('' != log, CONCAT_WS(',', log, ?), ?) WHERE id_po = ?";

        return $this->db->query($sql, array(
            $log_string,
            $log_string,
            $id_po,
        ));
    }

    // CHANGE PO STATUS TO ARCHIVED FOR SELLER AND BUYER
    public function set_po_archived()
    {
        $this->db->where('status = ?', 'order_initiated');
        $this->db->where_raw("NOW() > DATE_ADD(change_date, INTERVAL 1 WEEK)");

        return $this->db->update($this->po_table, array(
            'state_seller' => 1,
            'state_buyer'  => 1,
        ));
    }

    public function get_old_po($days)
    {
        $sql = "SELECT id_po, id_prototype
				FROM $this->po_table
				WHERE
					(`status` IN ('declined') OR (state_seller = 2 AND state_buyer = 2))
					AND DATEDIFF(NOW(), change_date) >= ?";

        return $this->db->query_all($sql, array($days));
    }

    public function delete_po($po_list)
    {
        $this->db->in('id_po', $po_list);

        return $this->db->delete($this->po_table);
    }

    public function delete_prototypes($prototype_list)
    {
        $this->db->in('id_prototype', $prototype_list);

        return $this->db->delete($this->prototype_table);
    }
}
