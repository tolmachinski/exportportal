<?php

/**
 * Items_Highlight_Model.php
 *
 * company staff model
 *
 * @deprecated in favor of \Highlighted_Products_Model
 */
class Items_Highlight_Model extends TinyMVC_Model {
	public $hi_table = 'item_highlight';

	private function _params_get_items_highlighted($conditions = array()){
		$this->db->from("{$this->hi_table} ih");
		$this->db->join("items i", "ih.id_item = i.id", "inner");
		$this->db->join("item_photo ip", "ih.id_item=ip.sale_id AND ip.main_photo = 1", "left");
		$this->db->join("item_category cat", "i.id_cat = cat.category_id", "left");

		extract($conditions);

		if(isset($id_item)){
            $id_item = getArrayFromString($id_item);

			$this->db->in("ih.id_item", $id_item);
        }

		if(isset($highlight_number)){
			$this->db->where("ih.id_highlight = ?", (int) $highlight_number);
        }

		if(isset($status)){
			$this->db->where("ih.status = ?", $status);
        }

		if(isset($expire_days)){
			$this->db->where_raw(" DATEDIFF(ih.end_date, CURDATE()) <= ? AND DATEDIFF(ih.end_date, CURDATE()) >= 0 ", [$expire_days]);
        }

		if(isset($paid)){
			$this->db->where("ih.paid = ?", (int) $paid);
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
			$this->db->where("ih.end_date >= ?", $expire_start_date);
        }

		if(isset($expire_finish_date)){
			$this->db->where("ih.end_date <= ?", $expire_finish_date);
        }

		if(isset($create_date_from)){
			$this->db->where("ih.create_date >= ?", $create_date_from);
        }

		if(isset($create_date_to)){
			$this->db->where("ih.create_date <= ?", $create_date_to);
        }

		if(isset($start_date_update)){
			$this->db->where("ih.update_date >= ?", $start_date_update);
        }

		if(isset($finish_date_update)){
			$this->db->where("ih.update_date <= ?", $finish_date_update);
        }

		if(isset($keywords)){
			if(str_word_count_utf8($keywords) > 1){
				$this->db->where_raw("MATCH(i.title,i.description,i.search_info) AGAINST (?)", [$keywords]);
			} else{
				$this->db->where_raw("(i.title LIKE ? OR i.description LIKE ? OR i.search_info LIKE ?)", array_fill(0, 3, "%{$keywords}%"));
			}
        }
	}

    function get_items_highlight($conditions = array()){
		$order_by = null;

		$this->db->select("ih.*, i.title,ip.photo_name, cat.breadcrumbs");

		$this->_params_get_items_highlighted($conditions);

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

	function get_items_highlight_count($conditions = array()){
		$this->db->select("COUNT(ih.id_item) as total_rows");

		$this->_params_get_items_highlighted($conditions);

		$records = $this->db->query_one();

		return $records['total_rows'];
    }

	function set_notice($id, $notice){
		$sql = "UPDATE " . $this->hi_table . "
				SET notice = CONCAT_WS(',', ?, notice)
				WHERE id_highlight = ?";
		return $this->db->query($sql, array($notice, $id));
	}

	function update_highlight_item($id, $update){
        $this->db->where('id_highlight', $id);
        return $this->db->update($this->hi_table, $update);
	}

	function get_highlight_item($id_highlight){
		$sql = "SELECT *
				FROM ".$this->hi_table."
				WHERE id_highlight = ? ";
        return $this->db->query_one($sql, array($id_highlight));
	}

	function get_highlight_item_id($id){
		$sql = "SELECT ith.*, i.id_seller, i.id_cat, i.title
				FROM $this->hi_table ith
				INNER JOIN items i ON ith.id_item=i.id AND ith.id_highlight=?";
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
				FROM item_highlight ith
				INNER JOIN items it ON ith.id_item = it.id
				INNER JOIN item_category c ON FIND_IN_SET(it.id_cat, c.cat_childrens) OR it.id_cat = c.category_id";

		if(count($where))
		    $sql .= " WHERE " . implode(" AND ", $where);

		$sql .= " GROUP BY c.category_id";

		$rez = $this->db->query_all($sql, $params);

		foreach($rez as $item)
			$ds[$item['category_id']] = $item;

		$tree_array = array();
		foreach($ds as $key => &$node){
			if($node['parent'] == 0)
				$tree_array[$key] = &$node;
			else
				$ds[$node['parent']]['subcats'][$key] = &$node;
		}
		return $tree_array;
	}
}
