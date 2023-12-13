<?php

declare(strict_types=1);

use App\Common\Database\BaseModel;

/**
 *Inquiry_model.php.
 *
 *Inquiry system model
 *
 *@author
 *
 *@deprecated in favor of \Inquirys_Model
 */
class Inquiry_Model extends BaseModel
{
    private $inquiry_table = 'item_inquiry';
    private $prototype_table = 'item_prototype';
    private $items_table = 'items';
    private $users_table = 'users';
    private $company_table = 'company_base';

    public function set_inquiry($data)
    {
        $this->db->insert($this->inquiry_table, $data);

        return $this->db->last_insert_id();
    }

    public function get_inquiries_last_id()
    {
        $sql = 'SELECT id_inquiry
				FROM ' . $this->inquiry_table . '
				ORDER BY id_inquiry DESC
				LIMIT 0,1';

        $rez = $this->db->query_one($sql);

        if (!empty($rez['id_inquiry'])) {
            return $rez['id_inquiry'];
        }

        return 0;
    }

    public function get_count_new_inquiries($id_inquiry)
    {
        $sql = 'SELECT COUNT(*) as counter
				FROM ' . $this->inquiry_table . '
				WHERE id_inquiry > ? ';

        $rez = $this->db->query_one($sql, array($id_inquiry));

        return $rez['counter'];
    }

    public function get_inquiry($id_inquiry, $conditions = array())
    {
        $where = array();
        $params = array();

        $where[] = 'id_inquiry = ? ';
        $params[] = $id_inquiry;

        extract($conditions);

        if (isset($seller)) {
            $where[] = ' ii.id_seller = ? ';
            $params[] = $seller;
        }

        if (isset($buyer)) {
            $where[] = ' ii.id_buyer = ? ';
            $params[] = $buyer;
        }

        $sql = 'SELECT ii.*, it.title
				FROM ' . $this->inquiry_table . ' ii
				INNER JOIN ' . $this->items_table . ' it ON ii.id_item = it.id ';

        if (count($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        return $this->db->query_one($sql, $params);
    }

    public function get_inquiries(array $conditions = array())
    {
        $page = 0;
        $per_p = 20;
        $where = array();
        $params = array();
        $order_by = 'change_date DESC';
        $check_state = true;
        $rel = '';

        extract($conditions);

        if (isset($sort_by)) {
            foreach ($sort_by as $sort_item) {
                $sort_item = explode('-', $sort_item);
                $multi_order_by[] = $sort_item[0] . ' ' . $sort_item[1];
            }

            $order_by = implode(',', $multi_order_by);
        }

        if (isset($status) && 'all' != $status) {
            $where[] = ' ii.status = ? ';
            $params[] = $status;
        }

        if (isset($seller)) {
            $where[] = ' ii.id_seller = ? ';
            $params[] = $seller;

            if ($check_state) {
                $where[] = ' ii.state_seller = ? ';
                if (isset($state_seller)) {
                    $params[] = $state_seller;
                } else {
                    $params[] = 0;
                }
            }
        }

        if (isset($buyer)) {
            $where[] = ' ii.id_buyer = ? ';
            $params[] = $buyer;

            if ($check_state) {
                $where[] = ' ii.state_buyer = ? ';
                if (isset($state_buyer)) {
                    $params[] = $state_buyer;
                } else {
                    $params[] = 0;
                }
            }
        }

        if (isset($item)) {
            $where[] = ' ii.id_item = ? ';
            $params[] = $item;
        }

        if (isset($inquiry_number)) {
            $where[] = ' ii.id_inquiry = ? ';
            $params[] = $inquiry_number;
        }

        if (isset($price)) {
            $where[] = ' ii.price = ? ';
            $params[] = $price;
        }

        if (isset($status_prototype)) {
            $where[] = ' ip.status_prototype = ? ';
            $params[] = $status_prototype;
        }

        if (isset($start_from)) {
            $where[] = ' DATE(ii.date) >= ?';
            $params[] = $start_from;
        }
        if (isset($start_to)) {
            $where[] = ' DATE(ii.date) <= ?';
            $params[] = $start_to;
        }

        if (isset($update_from)) {
            $where[] = ' DATE(ii.change_date) >= ?';
            $params[] = $update_from;
        }
        if (isset($update_to)) {
            $where[] = ' DATE(ii.change_date) <= ?';
            $params[] = $update_to;
        }

        if (isset($keywords)) {
            if (str_word_count_utf8($keywords) > 1) {
                $order_by = $order_by . ', REL DESC';
                $where[] = ' MATCH (ii.for_search) AGAINST (?)';
                $params[] = $keywords;
                $rel = " , MATCH (ii.for_search) AGAINST (?) as REL";
                array_unshift($params, $keywords);
            } else {
                $where[] = " ii.for_search LIKE ? ";
                $params[] = '%' . $keywords . '%';
            }
        }

        $sql = 'SELECT ii.*,
				    ip.title, ip.image, ip.status_prototype, ip.changes as changes_prototype ' . $rel . '
                FROM ' . $this->inquiry_table . ' ii
				LEFT JOIN ' . $this->prototype_table . ' ip ON ii.id_prototype = ip.id_prototype';

        if (count($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' GROUP BY id_inquiry ';

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

    public function get_inquiry_full_info($id_inquiry)
    {
        $sql = "SELECT ii.*,
					CONCAT(bu.fname, ' ', bu.lname) as buyername, bu.logged, ugb.gr_name as buyer_group,
					CONCAT(se.fname, ' ', se.lname) as sellername, se.logged, ugs.gr_name as seller_group,
					ip.title, ip.image , ip.price as prototype_price
				FROM " . $this->inquiry_table . ' ii
				INNER JOIN ' . $this->users_table . ' bu ON ii.id_buyer = bu.idu
				INNER JOIN user_groups ugb ON bu.user_group = ugb.idgroup
				INNER JOIN ' . $this->users_table . ' se ON ii.id_seller = se.idu
				INNER JOIN user_groups ugs ON se.user_group = ugs.idgroup
				LEFT JOIN ' . $this->prototype_table . ' ip ON ii.id_prototype = ip.id_prototype
				WHERE id_inquiry = ?
				GROUP BY id_inquiry';

        return $this->db->query_one($sql, array($id_inquiry));
    }

    public function update_inquiry($id_inquiry, $data)
    {
        $this->db->where('id_inquiry', $id_inquiry);

        return $this->db->update($this->inquiry_table, $data);
    }

    public function change_inquiry_log($id_inquiry, $log_string)
    {
        $sql = "UPDATE {$this->inquiry_table} SET log = IF('' != log, CONCAT_WS(',', log, ?), ?) WHERE id_inquiry = ?";

        return $this->db->query($sql, array(
            $log_string,
            $log_string,
            $id_inquiry,
        ));
    }

    public function counter_by_conditions(array $conditions = array())
    {
        $page = 1;
        $per_p = 20;
        $where = array();
        $params = array();
        $all_inquiry = 0;
        $check_state = true;

        extract($conditions);

        if (isset($status) && 'all' != $status) {
            $where[] = ' ii.status = ? ';
            $params[] = $status;
        }

        if (isset($seller)) {
            $where[] = ' ii.id_seller = ? ';
            $params[] = $seller;

            if ($check_state) {
                $where[] = ' ii.state_seller = ? ';
                if (isset($state_seller)) {
                    $params[] = $state_seller;
                } else {
                    $params[] = 0;
                }
            }
        }

        if (isset($buyer)) {
            $where[] = ' ii.id_buyer = ? ';
            $params[] = $buyer;

            if ($check_state) {
                $where[] = ' ii.state_buyer = ? ';
                if (isset($state_buyer)) {
                    $params[] = $state_buyer;
                } else {
                    $params[] = 0;
                }
            }
        }

        if (isset($item)) {
            $where[] = ' ii.id_item = ? ';
            $params[] = $item;
        }

        if (isset($inquiry_number)) {
            $where[] = ' ii.id_inquiry = ? ';
            $params[] = $inquiry_number;
        }

        if (isset($price)) {
            $where[] = ' ii.price = ? ';
            $params[] = $price;
        }

        if (isset($status_full)) {
            $select = ',ii.* ';
            $group_by = 'ii.status';
            $all_inquiry = 1;
        }

        if (isset($status_count)) {
            $select = ',ii.status ';
            $group_by = 'ii.status';
            $all_inquiry = 1;
        }

        if (isset($start_from)) {
            $where[] = ' DATE(ii.date) >= ?';
            $params[] = $start_from;
        }
        if (isset($start_to)) {
            $where[] = ' DATE(ii.date) <= ?';
            $params[] = $start_to;
        }

        if (isset($update_from)) {
            $where[] = ' DATE(ii.change_date) >= ?';
            $params[] = $update_from;
        }
        if (isset($update_to)) {
            $where[] = ' DATE(ii.change_date) <= ?';
            $params[] = $update_to;
        }

        if (isset($keywords)) {
            $words = explode(' ', $keywords);
            if (count($words) > 1) {
                $where[] = ' MATCH (ii.for_search) AGAINST (?)';
                $params[] = $keywords;
            } else {
                $where[] = " ii.for_search LIKE ? ";
                $params[] = '%' . $keywords . '%';
            }
        }

        $sql = 'SELECT COUNT(*) as counter
				' . $select . '
                FROM ' . $this->inquiry_table . ' ii ';

        if (count($where)) {
            $sql .= ' WHERE ' . implode(' AND', $where);
        }

        if (isset($group_by)) {
            $sql .= ' GROUP BY ' . $group_by;
        }

        if ($all_inquiry) {
            return $this->db->query_all($sql, $params);
        }
        $rez = $this->db->query_one($sql, $params);

        return $rez['counter'];
    }

    // INQUIRIES COUNTERS BY STATUSES
    public function count_inquiries_by_statuses(array $conditions = array())
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

        $sql = 'SELECT status, COUNT(*)	as counter
                FROM ' . $this->inquiry_table;

        if (count($where)) {
            $sql .= ' WHERE ' . implode(' AND', $where);
        }

        $sql .= ' GROUP BY status ';

        return $this->db->query_all($sql, $params);
    }

    // CHANGE PO STATUS TO ARCHIVED FOR SELLER AND BUYER
    public function set_inquiries_archived()
    {
		$this->db->where('status = ?', 'completed');
        $this->db->where_raw("NOW() > DATE_ADD(change_date, INTERVAL 1 WEEK)");

        return $this->db->update($this->inquiry_table, array(
            'state_seller' => 1,
            'state_buyer'  => 1,
		));
    }

    public function get_old_inquiries($days)
    {
        $sql = "SELECT id_inquiry, id_prototype
				FROM $this->inquiry_table
				WHERE
					(`status` IN ('declined') OR (state_seller = 2 AND state_buyer = 2))
					AND DATEDIFF(NOW(), change_date) >= ?";

        return $this->db->query_all($sql, array($days));
    }

    public function delete_inquiries($inquiry_list)
    {
        $this->db->in('id_inquiry', $inquiry_list);

        return $this->db->delete($this->inquiry_table);
    }

    public function delete_prototypes($prototype_list)
    {
        $this->db->in('id_prototype', $prototype_list);

        return $this->db->delete($this->prototype_table);
    }
}
