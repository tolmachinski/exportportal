<?php

/**
 * Items_Featured.php
 *
 * Items_Featured model
 *
 * @author Vadim Tabaran
 * @deprecated in favor of \Featured_Products_Model
 */
class Items_Featured_Model extends TinyMVC_Model {
	public $fe_table = 'item_featured';
	public $items_table = 'items';
	public $users_table = 'users';
	public $user_groups_table = 'user_groups';

	private function _params_get_items_featured($conditions = array()){
		$this->db->from("{$this->fe_table} i_f");
		$this->db->join("items i", "i_f.id_item = i.id", "inner");
		$this->db->join("item_photo ip", "i_f.id_item=ip.sale_id AND ip.main_photo = 1", "left");
		$this->db->join("item_category cat", "i.id_cat = cat.category_id", "left");

		extract($conditions);

		if(isset($id_item)){
			if(!is_array($id_item)){
				$id_item = explode(',', $id_item);
			}

			$this->db->in("i_f.id_item", $id_item);
        }

		if(isset($featured_number)){
			$this->db->where("i_f.id_featured = ?", (int) $featured_number);
        }

		if(isset($featured)){
			$this->db->where("i.featured = ?", (int) $featured);
        }

		if(isset($status)){
			$this->db->where("i_f.status = ?", $status);
        }

		if(isset($expire_days)){
			$this->db->where_raw(" DATEDIFF(i_f.end_date, CURDATE()) <= ? AND DATEDIFF(i_f.end_date, CURDATE()) >= 0 ", $expire_days);
        }

		if(isset($paid)){
			$this->db->where("i_f.paid = ?", (int) $paid);
        }

		if(isset($categories_list)){
            $categories_list = getArrayFromString($categories_list);

			$this->db->in("i.id_cat", $categories_list);
		}elseif(isset($category)){
			$subcats = model('Category')->get_cat_childrens($category) .  $category;
			if(!is_array($subcats)){
				$subcats = explode(',', $subcats);
			}

			if(!empty($subcats)){
				$this->db->in("i.id_cat", $subcats);
			}
		}

		if(isset($id_user)){
			$this->db->where("i.id_seller = ?", (int) $id_user);
        }

		if(isset($expire_start_date)){
			$this->db->where("i_f.end_date >= ?", $expire_start_date);
        }

		if(isset($expire_finish_date)){
			$this->db->where("i_f.end_date <= ?", $expire_finish_date);
        }

        if(isset($create_date)){
			$this->db->where("i_f.create_date = ?", $create_date);
        }

		if(isset($create_date_from)){
			$this->db->where("i_f.create_date >= ?", $create_date_from);
        }

		if(isset($create_date_to)){
			$this->db->where("i_f.create_date <= ?", $create_date_to);
        }

		if(isset($start_date_update)){
			$this->db->where("i_f.update_date >= ?", $start_date_update);
        }

		if(isset($finish_date_update)){
			$this->db->where("i_f.update_date <= ?", $finish_date_update);
        }

		if(isset($keywords)){
			if(str_word_count_utf8($keywords) > 1){
				$this->db->where_raw("MATCH(i.title,i.description,i.search_info) AGAINST ( ? )", [$keywords]);
			} else{
				$this->db->where_raw("(i.title LIKE ? OR i.description LIKE ? OR i.search_info LIKE ?)", array_fill(0, 3, "%{$keywords}%"));
			}
        }
	}

    function get_items_featured($conditions = array()){
		$order_by = null;

		$this->db->select("i_f.*, i.title,ip.photo_name, cat.breadcrumbs");

		$this->_params_get_items_featured($conditions);

		if (isset($conditions['sort_by'])) {
			$multi_order_by = array();
			foreach ($conditions['sort_by'] as $sort_item) {
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0] . ' ' . $sort_item[1];
			}

			if(!empty($multi_order_by)){
				$order_by = implode(',', $multi_order_by);
			}
		}

		if(!empty($order_by)){
			$this->db->orderby($order_by);
		}

		$this->db->limit((int) $conditions['per_p'], (int) $conditions['start']);

		$records = $this->db->query_all();

		return !empty($records) ? $records : array();
    }

	function get_items_featured_count($conditions = array()){
		$this->db->select("COUNT(i_f.id_item) as total_rows");

		$this->_params_get_items_featured($conditions);

		$records = $this->db->query_one();

		return $records['total_rows'];
    }

	function set_notice($id, $notice){
		$sql = "UPDATE " . $this->fe_table . "
				SET notice = CONCAT_WS(',', ?, notice)
				WHERE id_featured = ?";
		return $this->db->query($sql, array($notice, $id));
	}

	function update_featured_item($id, $update){
        $this->db->where('id_featured', $id);
        return $this->db->update($this->fe_table, $update);
	}

	function get_featured_item($id_featured){
		$sql = "SELECT *
				FROM ".$this->fe_table."
				WHERE id_featured = ? ";
        return $this->db->query_one($sql, array($id_featured));
	}

	function get_featured_item_id($id){
		$sql = "SELECT itf.*, i.id_seller, i.id_cat, i.title
				FROM $this->fe_table itf
				INNER JOIN items i ON itf.id_item=i.id AND itf.id_featured=?";
        return $this->db->query_one($sql, array($id));
	}

	function get_cat_tree($conditions){
		$where = array();
        $params = array();

		extract($conditions);

		if(isset($seller)){
            $where[] = " it.id_seller = ? ";
            $params[] = $seller;
		}

		$sql = "SELECT c.category_id, c.parent, c.name, c.cat_childrens, c.breadcrumbs,  COUNT(c.category_id) as counter
				FROM item_featured itf
				INNER JOIN items it ON itf.id_item = it.id
				INNER JOIN item_category c ON FIND_IN_SET(it.id_cat, c.cat_childrens) OR it.id_cat = c.category_id";

		if(count($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
		}

		$sql .= " GROUP BY c.category_id";
		$rez = $this->db->query_all($sql, $params);
		if (empty($rez)) {
			return  array();
		}

		$ds = array();
		foreach($rez as $item) {
			$ds[$item['category_id']] = $item;
		}

		$tree_array = array();
		foreach($ds as $key => &$node) {
			if($node['parent'] == 0) {
				$tree_array[$key] = &$node;
			} else {
				$ds[$node['parent']]['subcats'][$key] = &$node;
			}
		}

		return $tree_array;
    }

    public function get_soon_expire_items()
	{
		$this->db->select("fe.*, u.idu, u.user_group, (DATEDIFF(NOW(), fe.end_date) * -1) AS remain_days");
        $this->db->from("{$this->fe_table} as fe");
        $this->db->join("{$this->items_table} i", 'fe.id_item = i.id', 'left');
        $this->db->join("{$this->users_table} u", 'i.id_seller = u.idu', 'left');
		$this->db->where_raw('fe.end_date > NOW() AND (DATEDIFF(NOW(), fe.end_date) IN (-1,-3))');
		$this->db->where('u.status', 'active');
        $this->db->groupby("u.idu, remain_days");

		return $this->db->query_all();
	}

    public function insert_feature_request_batch($data){
        $this->db->insert_batch($this->fe_table, $data);
        return $this->db->getAffectableRowsAmount();
    }

}
