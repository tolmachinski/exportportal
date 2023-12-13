<?php
/**
*User_Guide_Model.php
*
*User Guide Model
*
*@author Cravciuc Andrei
*/

class User_Guide_Model extends TinyMVC_Model {

	private $user_guides_table = 'user_guides';
	private $user_guides_relation_table = 'user_guides_relation';

	// EP DOCUMENTATION MENU FUNCTIONS
	function insert_user_guide($data){
		return $this->db->insert($this->user_guides_table, $data);
	}
	function delete_user_guide($id_menu){
		$this->db->where('id_menu', $id_menu);
		return $this->db->delete($this->user_guides_table);
	}

	function get_user_guide($id_menu, $columns = " * "){
        $this->db->select($columns);
        $this->db->where('id_menu', $id_menu);
        $this->db->limit(1);

        return $this->db->get_one($this->user_guides_table);
	}

	function get_user_guide_by_alias($doc_alias){
        $this->db->where('menu_alias', $doc_alias);
        $this->db->limit(1);

        return $this->db->get_one($this->user_guides_table);
	}

	function update_user_guide($id_menu, $data){
		$this->db->where('id_menu', $id_menu);
		return $this->db->update($this->user_guides_table, $data );
	}

	function update_user_guides($data){
		$this->db->update($this->user_guides_table, $data );
	}

	function update_user_guides_batch($menus){
        $errors = array();
		foreach($menus as $menu){
            if(!$this->update_user_guide($menu['id_menu'],array('id_parent' => $menu['id_parent'], 'menu_position' => $menu['menu_position']))){
                $errors[] = $menu;
            }
        }

        return count($errors);
	}

	function get_user_guides(array $conditions = array()){
        $order_by = " menu_position ASC, id_menu ASC ";
		$columns = " edm.* ";
		$left_joins = "";
		$rel = "";
		extract($conditions);

		$where = $params = [];

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		if(isset($id_parent)){
			$where[] = " edm.id_parent = ? ";
			$params[] = $id_parent;
		}

		if(isset($parent_list)){
            $parent_list = getArrayFromString($parent_list);
			$where[] = " edm.id_parent IN (" . implode(',', array_fill(0, count($parent_list), '?')) . ") ";
            array_push($params, ...$parent_list);
		}

		if(isset($menu_list)){
            $menu_list = getArrayFromString($menu_list);
			$where[] = " edm.id_menu IN (" . implode(',', array_fill(0, count($menu_list), '?')) . ") ";
            array_push($params, ...$menu_list);
		}

		if(isset($user_type)){
            $left_joins .= " LEFT JOIN $this->user_guides_relation_table edmur ON edm.id_menu = edmur.rel_id_menu";
			$where[] = " edmur.rel_user_type = ? ";
			$params[] = $user_type;
		}

		if(!empty($keywords)){
			if(str_word_count_utf8($keywords) > 1){
				$order_by = " REL_tags DESC ";
				$where[] = " MATCH (edm.menu_title, edm.menu_description, edm.menu_intro) AGAINST (?)";
				$params[] = $keywords;
				$rel = " , MATCH (edm.menu_title, edm.menu_description, edm.menu_intro) AGAINST (?) as REL_tags";
                array_unshift($params, $keywords);
			} else{
				$where[] = " (edm.menu_title LIKE ? OR edm.menu_description LIKE ? OR edm.menu_intro LIKE ?) ";
                array_push($params, ...array_fill(0, 3, '%' . $keywords . '%'));
			}
        }

		$sql = "SELECT $columns $rel
				FROM $this->user_guides_table edm
                $left_joins";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
        }

		$sql .= " GROUP BY edm.id_menu ORDER BY {$order_by}";

		if(isset($start, $per_p)){
            $start = (int) $start;
            $per_p = (int) $per_p;
			$sql .= " LIMIT $start, $per_p";
		}

		return $this->db->query_all($sql, $params);
	}

	function count_user_guides( $conditions ){
        $left_joins = "";

		extract($conditions);

		$where = $params = [];

		if(isset($id_parent)){
			$where[] = " edm.id_parent = ? ";
			$params[] = $id_parent;
		}

		if(isset($menu_list)){
            $menu_list = getArrayFromString($menu_list);
			$where[] = " edm.id_menu IN (" . implode(',', array_fill(0, count($menu_list), '?')) . ") ";
            array_push($params, ...$menu_list);
		}

		if(isset($user_type)){
            $left_joins .= " LEFT JOIN $this->user_guides_relation_table edmur ON edm.id_menu = edmur.rel_id_menu";
			$where[] = " edmur.rel_user_type = ? ";
			$params[] = $user_type;
		}

		if(!empty($keywords)){
            $words = explode(' ', $keywords);
			if(count($words) > 1){
				$where[] = " MATCH (edm.menu_title, edm.menu_description, edm.menu_intro) AGAINST (?)";
				$params[] = $keywords;
			} else{
				$where[] = " (edm.menu_title LIKE ? OR edm.menu_description LIKE ? OR edm.menu_intro LIKE ?) ";
                array_push($params, ...array_fill(0, 3, '%' . $keywords . '%'));
			}
        }

		$sql = "SELECT COUNT(*) as counter
				FROM $this->user_guides_table edm
                $left_joins";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
        }

		return $this->db->query_one($sql, $params)['counter'];
	}

	function get_last_user_guide_position($parent){
		$sql = "SELECT menu_position
				FROM $this->user_guides_table
				WHERE id_parent = ?
				ORDER BY menu_position DESC
				LIMIT 1";
		return (int) $this->db->query_one($sql, array($parent))['menu_position'];
	}

    // MENU USERTYPE RELATIONS
    function set_user_guide_relation($data = array()){
        if(empty($data)){
            return;
        }

		$this->db->insert_batch($this->user_guides_relation_table, $data);
		return $this->db->getAffectableRowsAmount();
    }

    function delete_user_guide_relation($id_menu){
		$this->db->where('rel_id_menu', $id_menu);
		return $this->db->delete($this->user_guides_relation_table);
    }

    function get_user_guide_relation($id_menu){
		$sql = "SELECT *
				FROM $this->user_guides_relation_table
				WHERE rel_id_menu = ?";
		return $this->db->query_all($sql, array($id_menu));
    }

	// MENU BREADCRUMBS
    function replace_breadcrumbs_part($old_breadcrumbs, $new_breadcrumbs, $children_list){
        $params = [$old_breadcrumbs, $new_breadcrumbs, $new_breadcrumbs];
        $children_list = getArrayFromString($children_list);
        array_push($params, ...$children_list);

		$sql = "UPDATE $this->user_guides_table
				SET menu_breadcrumbs = (if(menu_breadcrumbs != '', REPLACE(menu_breadcrumbs, ?, ?), ?))
				WHERE id_menu IN (" . implode(',', array_fill(0, count($children_list), '?')) . ")";

		$this->db->query($sql, $params);
	}

	function update_breadcrumbs($id, &$glob, &$menus){
		if(isset($glob[$id]))
			return;

		if($menus[$id]['id_parent'] == 0){

			$glob[$id][] = json_encode(array($id => $menus[$id]['menu_title']));

		}elseif(isset($glob[$menus[$id]['id_parent']])){

			$glob[$id] = $glob[$menus[$id]['id_parent']];
			$glob[$id][] = json_encode(array($id => $menus[$id]['menu_title']));

		}else{
			if(in_array($menus[$id]['id_parent'], array_keys($menus))){
				$this->update_breadcrumbs($menus[$id]['id_parent'], $glob, $menus);
				$glob[$id] = $glob[$menus[$id]['id_parent']];
				$glob[$id][] = json_encode(array($id => $menus[$id]['menu_title']));
			}else{
				$parent = $this->get_user_guide($menus[$id]['id_parent'], "id_menu, menu_breadcrumbs, menu_actualized");
				if(!empty($parent)){
					$glob[$id][] = $parent['menu_breadcrumbs'];
					$glob[$id][] = json_encode(array($id => $menus[$id]['menu_title']));
				}else{
					$this->update_user_guide($id, array('menu_actualized' => 2));
				}
			}
		}
	}

	function update_breadcrumbs_batch($menus){
		foreach($menus as $id_menu => $crumbs)
			$this->update_user_guide($id_menu,array('menu_breadcrumbs' => implode(',', $crumbs), 'menu_actualized' => 1));
	}

	// MENU CHILDREN
	function parents_from_breadcrumbs($breadcrumbs, $id_menu = 0, $list = true){
		$parents_dirty = json_decode('[' . $breadcrumbs . ']', true);
		$parents_clean = array();
		foreach ($parents_dirty as $parent) {
			foreach($parent as $id => $name){
				if($id != $id_menu)
					$parents_clean[] = $id;
			}
		}

		if ($list) {
			return implode(',' ,$parents_clean);
        }

		return $parents_clean;
	}

	function append_children_to_parents($id_list, $parents_list){
        $params = [$id_list, $id_list];
        $parents_list = getArrayFromString($parents_list);
        array_push($params, ...$parents_list);

		$sql = "UPDATE $this->user_guides_table
				SET menu_children =  if(STRCMP(menu_children,''), (CONCAT_WS(',', menu_children, ?)), ? )
				WHERE id_menu IN (" . implode(',', array_fill(0, count($parents_list), '?')) . ")";

		$this->db->query($sql, $params);
	}

	function get_user_guides_children_recursive($menu_list, &$global){
        $menu_list = getArrayFromString($menu_list);

		$sql = "SELECT id_menu, id_parent, menu_breadcrumbs
				FROM $this->user_guides_table
				WHERE id_parent IN (" . implode(',', array_fill(0, count($menu_list), '?')) . ")";

		$rez = $this->db->query_all($sql, $menu_list);

		if(count($rez) > 0){
			$new_list = array();
			foreach($rez as $child){
				$new_list[] = $child['id_menu'];
				$parents = json_decode("[" . $child['menu_breadcrumbs'] . "]", true);

				foreach($parents as $par){
					foreach($par as $id => $name){
						if($id == $child['id_menu']) continue;

						$global[$id][] = $child['id_menu'];
					}
				}
			}
			$this->get_user_guides_children_recursive(implode(',',$new_list), $global);
		}
	}

	function update_user_guide_children_batch($menus){
		foreach($menus as $id_menu => $children)
			$this->update_user_guide($id_menu, array('menu_children' => implode(',', $children)));
	}

	// GET TREE FROM SIMPLE ARRAY
	public function _menu_map($array){
		$tree = array();
		foreach($array as &$node){
			if($node['id_parent'] == 0)
				$tree[] = &$node;
			else
				$array[$node['id_parent']]['children'][] = &$node;
		}
		return $tree;
	}

    public function getUserGuidesLang()
    {
        return [
            'document_upload' => [
                'en'  => 'English',
                'fr'  => 'French',
                'es'  => 'Spanish',
                'de'  => 'German',
                'ro'  => 'Romanian',
                'ru'  => 'Russian',
                'ur'  => 'Urdu',
                'hi'  => 'Hindi',
                'id'  => 'Indonesian',
                'lt'  => 'Lithuanian',
                'tr'  => 'Turkish',
                'ar'  => 'Arabic',
                'cn'  => 'Chinese',
                'it'  => 'Italian',
                'por' => 'Portuguese'
            ],
        ];
    }

    public function getUserGuides()
    {
        return [
            'registration'       => [
                'en' => [
                    'all' => 'registration_process_quick_guide',
                ]
            ],
            'profile_completion' => [
                'en' => [
                    'all' => 'profile_completion_quick_guide',
                ]
            ],
            'add_item'           => [
                'en' => [
                    'all' => 'add_item_quick_guide',
                ]
            ],
            'item_bulk_upload'   => [
                'en' => [
                    'all' => 'items_bulk_upload_guide',
                ]
            ],
            'document_upload'    => [
                'en' => [
                    'freight_forwarder' => 'freight_forwarder_document_upload_guide',
                    'manufacturer'      => 'manufacturer_document_upload_guide',
                    'buyer'             => 'buyer_document_upload_guide',
                    'seller'            => 'seller_document_upload_guide',
                ],
                'fr' => [
                    'freight_forwarder' => 'freight_forwarder_document_upload_guide',
                    'manufacturer'      => 'manufacturer_document_upload_guide',
                    'buyer'             => 'buyer_document_upload_guide',
                    'seller'            => 'seller_document_upload_guide',
                ],
                'es' => [
                    'freight_forwarder' => 'freight_forwarder_document_upload_guide',
                    'manufacturer'      => 'manufacturer_document_upload_guide',
                    'buyer'             => 'buyer_document_upload_guide',
                    'seller'            => 'seller_document_upload_guide',
                ],
                'de' => [
                    'freight_forwarder' => 'freight_forwarder_document_upload_guide',
                    'manufacturer'      => 'manufacturer_document_upload_guide',
                    'buyer'             => 'buyer_document_upload_guide',
                    'seller'            => 'seller_document_upload_guide',
                ],
                'ro' => [
                    'freight_forwarder' => 'freight_forwarder_document_upload_guide',
                    'manufacturer'      => 'manufacturer_document_upload_guide',
                    'buyer'             => 'buyer_document_upload_guide',
                    'seller'            => 'seller_document_upload_guide',
                ],
                'ru' => [
                    'freight_forwarder' => 'freight_forwarder_document_upload_guide',
                    'manufacturer'      => 'manufacturer_document_upload_guide',
                    'buyer'             => 'buyer_document_upload_guide',
                    'seller'            => 'seller_document_upload_guide',
                ],
                'ur' => [
                    'freight_forwarder' => 'freight_forwarder_document_upload_guide',
                    'manufacturer'      => 'manufacturer_document_upload_guide',
                    'buyer'             => 'buyer_document_upload_guide',
                    'seller'            => 'seller_document_upload_guide',
                ],
                'hi' => [
                    'freight_forwarder' => 'freight_forwarder_document_upload_guide',
                    'manufacturer'      => 'manufacturer_document_upload_guide',
                    'buyer'             => 'buyer_document_upload_guide',
                    'seller'            => 'seller_document_upload_guide',
                ],
                'id' => [
                    'freight_forwarder' => 'freight_forwarder_document_upload_guide',
                    'manufacturer'      => 'manufacturer_document_upload_guide',
                    'buyer'             => 'buyer_document_upload_guide',
                    'seller'            => 'seller_document_upload_guide',
                ],
                'lt' => [
                    'freight_forwarder' => 'freight_forwarder_document_upload_guide',
                    'manufacturer'      => 'manufacturer_document_upload_guide',
                    'buyer'             => 'buyer_document_upload_guide',
                    'seller'            => 'seller_document_upload_guide',
                ],
                'tr' => [
                    'freight_forwarder' => 'freight_forwarder_document_upload_guide',
                    'manufacturer'      => 'manufacturer_document_upload_guide',
                    'buyer'             => 'buyer_document_upload_guide',
                    'seller'            => 'seller_document_upload_guide',
                ],
                'ar' => [
                    'freight_forwarder' => 'freight_forwarder_document_upload_guide',
                    'manufacturer'      => 'manufacturer_document_upload_guide',
                    'buyer'             => 'buyer_document_upload_guide',
                    'seller'            => 'seller_document_upload_guide',
                ],
                'cn' => [
                    'freight_forwarder' => 'freight_forwarder_document_upload_guide',
                    'manufacturer'      => 'manufacturer_document_upload_guide',
                    'buyer'             => 'buyer_document_upload_guide',
                    'seller'            => 'seller_document_upload_guide',
                ],
                'it' => [
                    'freight_forwarder' => 'freight_forwarder_document_upload_guide',
                    'manufacturer'      => 'manufacturer_document_upload_guide',
                    'buyer'             => 'buyer_document_upload_guide',
                    'seller'            => 'seller_document_upload_guide',
                ],
                'por' => [
                    'freight_forwarder' => 'freight_forwarder_document_upload_guide',
                    'manufacturer'      => 'manufacturer_document_upload_guide',
                    'buyer'             => 'buyer_document_upload_guide',
                    'seller'            => 'seller_document_upload_guide',
                ]
            ],
            'best_practices'     => [
                'en' => [
                    'buyer'             => 'buyer_best_practices',
                    'seller'            => 'seller_best_practices',
                    'manufacturer'      => 'manufacturer_best_practices',
                    'freight_forwarder' => 'freight_forwarder_best_practices',
                    'all'               => 'shipping-methods_best_practices'
                ],
            ],
        ];
    }
}
