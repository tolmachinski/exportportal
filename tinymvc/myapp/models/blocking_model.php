<?php

use App\Common\Database\BaseModel;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Statement;

/**
 * billing_model.php
 *
 * billing model
 *
 * @author Cravciuc Andrei
 */

class Blocking_Model extends BaseModel
{
    private $news_table = 'seller_news';
    private $pictures_table = "seller_photo";
    private $video_table = "seller_videos";
    private $updates_table = "seller_updates";
    private $library_table = "seller_library";
    private $company_base_table = "company_base";
    private $orders_shippers_table = "orders_shippers";
	private $items_table = "items";
	private $users_table = "users";
	private $user_groups_table = "user_groups";
    private $rights_table = "rights";
    private $group_rights_table = "usergroup_rights";
    private $aditional_rights_table = "user_rights_aditional";
    var $obj;

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
        $this->obj->load->library("elasticsearch");
    }

	// CRON - BLOCK EXPIRED PAID UNTIL USERS
	public function get_groups_rights(){
        $this->db->select("ug.idgroup, ug.gr_name, ug.gr_type, GROUP_CONCAT(r.r_alias SEPARATOR ',') as rights_list");
        $this->db->from("{$this->user_groups_table} ug");
        $this->db->join("{$this->group_rights_table} gr", "ug.idgroup = gr.idgroup", "left");
        $this->db->join("{$this->rights_table} r", "gr.idright = r.idright", "left");
        $this->db->where("r.for_posting_content", 1);
        $this->db->in("ug.gr_type", array('Buyer','Seller','Shipper'));
        $this->db->groupby("ug.idgroup");
		return $this->db->get();
	}

	public function get_group_rights($id_group){
        $this->db->select("ug.idgroup, ug.gr_name, ug.gr_type, GROUP_CONCAT(r.r_alias SEPARATOR ',') as rights_list");
        $this->db->from("{$this->user_groups_table} ug");
        $this->db->join("{$this->group_rights_table} gr", "ug.idgroup = gr.idgroup", "left");
        $this->db->join("{$this->rights_table} r", "gr.idright = r.idright", "left");
        $this->db->where("ug.idgroup", $id_group);
        $this->db->where("r.for_posting_content", 1);
        $this->db->in("ug.gr_type", array('Buyer','Seller','Shipper'));
		return $this->db->get_one();
	}

	public function get_aditional_rights($conditions = array()){
		extract($conditions);

        $this->db->select("ar.id_user, GROUP_CONCAT(r.r_alias SEPARATOR ',') as rights_list");
        $this->db->from("{$this->aditional_rights_table} ar");
        $this->db->join("{$this->rights_table} r", "ar.id_right = r.idright", "left");
        $this->db->where("r.for_posting_content", 1);
        $this->db->where("ar.right_paid", 1);

		if(isset($users_list)){
            if(!is_array($users_list)){
                $users_list = explode(',', $users_list);
            }

            $users_list = array_map('intval', $users_list);
            $this->db->in("ar.id_user", $users_list);
        }

        $this->db->groupby("ar.id_user");
		return $this->db->get();
	}

	public function get_user_aditional_rights($id_user){
        $this->db->select("GROUP_CONCAT(r.r_alias SEPARATOR ',') as rights_list");
        $this->db->from("{$this->aditional_rights_table} ar");
        $this->db->join("{$this->rights_table} r", "ar.id_right = r.idright", "left");
        $this->db->where("r.for_posting_content", 1);
        $this->db->where("ar.right_paid", 1);
        $this->db->where("ar.id_user", $id_user);
		return $this->db->get_one();
	}

    function get_aditional_rights_expired(){
        $this->db->select("ar.*, r.r_name, r.r_alias, r.r_descr");
        $this->db->from("{$this->aditional_rights_table} ar");
        $this->db->join("{$this->rights_table} r", "ar.id_right = r.idright", "left");
        $this->db->where_raw("ar.right_paid_until != '0000-00-00'");
        $this->db->where_raw("ar.right_paid_until < DATE_FORMAT(NOW(),'%Y-%m-%d')");
		return $this->db->get();
    }

    function delete_aditional_rights_expired(){
        $this->db->where_raw("right_paid_until != '0000-00-00'");
        $this->db->where_raw("right_paid_until < DATE_FORMAT(NOW(),'%Y-%m-%d')");
        $this->db->delete($this->aditional_rights_table);
    }

    public function block_users_data_by_rights($users_list_by_rights = array(), $userId = null){
        if(empty($users_list_by_rights)){
            return true;
        }

        foreach ($users_list_by_rights as $right_alias => $users_list_by_right) {
            $temp_users_list_by_right = is_array($users_list_by_right) ? implode(',', array_filter($users_list_by_right)) : $users_list_by_right;
            if(empty($temp_users_list_by_right)){
                continue;
            }

            $params = array(
                'users_list' => $temp_users_list_by_right
            );

            switch ($right_alias) {
                case 'have_news':
                    $params['blocked'] = 0;
                    $this->change_blocked_users_news($params, array('blocked' => 2));
                break;
                case 'have_pictures':
                    $params['blocked'] = 0;
                    $this->change_blocked_users_pictures($params, array('blocked' => 2));
                break;
                case 'have_videos':
                    $params['blocked'] = 0;
                    $this->change_blocked_users_videos($params, array('blocked' => 2));
                break;
                case 'have_updates':
                    $params['blocked'] = 0;
                    $this->change_blocked_users_updates($params, array('blocked' => 2));
                break;
                case 'have_library':
                    $params['blocked'] = 0;
                    $this->change_blocked_users_libraries($params, array('blocked' => 2));
                break;
                case 'have_staff':
                    $this->change_blocked_users_staffs($params, array('status' => 'blocked'));
                break;
                case 'have_company':
                    $params['blocked'] = 0;
                    $this->change_blocked_users_companies($params, array('blocked' => 2));
                break;
                case 'id_generated':
                    $this->block_companies_index_name($params);
                break;
                case 'sell_item':
                    $params['blocked'] = 0;
                    $this->change_blocked_users_items($params, array('blocked' => 2));
                break;
                case 'shipper_edit_company':
                    $this->change_visible_shipper_companies($params, array('visible' => 0));
                break;
                case 'manage_b2b_requests':
                    /** @var Elasticsearch_B2b_Model $elasticsearchB2bModel */
                    $elasticsearchB2bModel = model(Elasticsearch_B2b_Model::class);

                    $elasticsearchB2bModel->removeB2bRequestsByConditions(['userId' => (int) $userId]);
                break;
            }
        }
    }

    public function block_user_content($id_user, array $excludedRights = []) {
        $user = model('user')->getSimpleUser($id_user);
        if(empty($user)){
            return false;
        }

        $group_rights = $this->get_group_rights((int) $user['user_group']);
        $additional_rights = $this->get_aditional_rights(array('users_list' => $id_user));
        $block_rights = array_unique(
            array_merge(
                array_filter(explode(',', $group_rights['rights_list'])),
                array_filter(explode(',', $additional_rights['rights_list']))
            )
        );

        if (!empty($excludedRights)) {
            $block_rights = array_diff($block_rights, $excludedRights);
        }

        if(!empty($block_rights)){
            $users_list_by_rights = array_fill_keys($block_rights, $id_user);
            $this->block_users_data_by_rights($users_list_by_rights, $id_user);
        }

        return $this->change_blocked_users(array(
            'users_list' => $id_user,
            'user_page_blocked' => 0
        ), array('user_page_blocked' => 2));
    }

    public function unblock_user_content($id_user){
        $user = model('user')->getSimpleUser($id_user);
        if(empty($user)){
            return false;
        }

        $this->unblock_user_data_by_rights($id_user, (int) $user['user_group']);

        $this->change_blocked_users(array(
            'users_list' => $id_user,
            'user_page_blocked' => 2
        ), array('user_page_blocked' => 0));
    }

    public function unblock_user_data_by_rights($id_user = 0, $group = 0){
        if($id_user == 0){
			return false;
		}

        $group_rights = $this->get_group_rights($group);
        $aditional_rights = $this->get_user_aditional_rights($id_user);
        $aditional_rights = array_filter(explode(',', $aditional_rights['rights_list']));
        $rights = array_filter(explode(',', $group_rights['rights_list']));
        if(!empty($rights) && !empty($aditional_rights)){
            $rights = array_merge($rights, $aditional_rights);
        }

        if(empty($rights)){
            return true;
        }

        foreach ($rights as $right_alias) {
            $params = array(
                'users_list' => $id_user
            );

            switch ($right_alias) {
                case 'have_news':
                    $params['blocked'] = 2;
                    $this->change_blocked_users_news($params, array('blocked' => 0));
                break;
                case 'have_pictures':
                    $params['blocked'] = 2;
                    $this->change_blocked_users_pictures($params, array('blocked' => 0));
                break;
                case 'have_videos':
                    $params['blocked'] = 2;
                    $this->change_blocked_users_videos($params, array('blocked' => 0));
                break;
                case 'have_updates':
                    $params['blocked'] = 2;
                    $this->change_blocked_users_updates($params, array('blocked' => 0));
                break;
                case 'have_library':
                    $params['blocked'] = 2;
                    $this->change_blocked_users_libraries($params, array('blocked' => 0));
                break;
                case 'have_staff':
                    $this->change_blocked_users_staffs($params, array('status' => 'active'));
                break;
                case 'have_company':
                    $params['blocked'] = 2;
                    $this->change_blocked_users_companies($params, array('blocked' => 0));
                break;
                case 'id_generated':
                    $this->unblock_companies_index_name($params);
                break;
                case 'sell_item':
                    $params['blocked'] = 2;
                    $this->change_blocked_users_items($params, array('blocked' => 0));
                break;
                case 'manage_b2b_requests':
                    /** @var Elasticsearch_B2b_Model $elasticsearchB2bModel */
                    $elasticsearchB2bModel = model(Elasticsearch_B2b_Model::class);

                    $elasticsearchB2bModel->indexByConditions(['userId' => (int) $id_user]);
                break;
            }
        }
    }

	public function change_blocked_users_news($conditions = array(), $update = array()){
        extract($conditions);
        if(empty($users_list)){
			return false;
		}

        $users_list = getArrayFromString($users_list);

        $this->db->in('id_seller', $users_list);

        if(isset($blocked)){
		    $this->db->where('blocked', $blocked);
        }

		return $this->db->update($this->news_table, $update);
	}

	public function change_blocked_users_pictures($conditions = array(), $update = array()){
        extract($conditions);
        if(empty($users_list)){
			return false;
		}

        $users_list = getArrayFromString($users_list);

        $this->db->in('id_seller', $users_list);

        if(isset($blocked)){
		    $this->db->where('blocked', $blocked);
        }

		return $this->db->update($this->pictures_table, $update);
	}

	public function change_blocked_users_videos($conditions = array(), $update = array()){
        extract($conditions);
        if(empty($users_list)){
			return false;
		}

        $users_list = getArrayFromString($users_list);

        $this->db->in('id_seller', $users_list);

        if(isset($blocked)){
		    $this->db->where('blocked', $blocked);
        }

		return $this->db->update($this->video_table, $update);
	}

	public function change_blocked_users_updates($conditions = array(), $update = array()){
        extract($conditions);
        if(empty($users_list)){
			return false;
		}

        $users_list = getArrayFromString($users_list);

        $this->db->in('id_seller', $users_list);

        if(isset($blocked)){
		    $this->db->where('blocked', $blocked);
        }

		return $this->db->update($this->updates_table, $update);
	}

	public function change_blocked_users_libraries($conditions = array(), $update = array()){
        extract($conditions);
        if(empty($users_list)){
			return false;
		}

        $users_list = getArrayFromString($users_list);

        $this->db->in('id_seller', $users_list);

        if(isset($blocked)){
		    $this->db->where('blocked', $blocked);
        }

		return $this->db->update($this->library_table, $update);
	}

	public function change_blocked_users_staffs($conditions = array(), $update = array()){
        extract($conditions);

		if(empty($users_list)){
			return false;
		}

        $users_list = getArrayFromString($users_list);

		$sql = "SELECT GROUP_CONCAT(DISTINCT(u.idu)) as sellers_staff
				FROM company_base cb
				INNER JOIN company_users cu ON cu.id_company = cb.id_company
				INNER JOIN users u ON cu.id_user = u.idu AND u.user_type = 'users_staff'
				WHERE cb.id_user IN ( " . implode(',', array_fill(0, count($users_list), '?')) . " ) ";
		$res = $this->db->query_one($sql, $users_list);
		if(empty($res['sellers_staff'])){
			return true;
		}

		$this->db->in('idu', $res['sellers_staff']);
		return $this->db->update($this->users_table, $update);
	}

	public function change_blocked_users_companies($conditions = array(), $update = array()){
        extract($conditions);
        if(empty($users_list)){
			return false;
		}

        $users_list = getArrayFromString($users_list);

        $this->db->in('id_user', $users_list);

        if(isset($blocked)){
		    $this->db->where('blocked', $blocked);
        }

        $this->db->update($this->company_base_table, $update);

        if(isset($update['blocked'])){
            if($update['blocked'] > 0){
                $elasticsearch_query = array(
                    "terms" => array(
                        "id_user" => explode(',', $users_list)
                    )
                );
                library('elasticsearch')->delete_by_query('company', $elasticsearch_query);
            } else{
                model('elasticsearch_company')->index(array('users_list' => $users_list));
            }
        }

        return true;
    }

    public function change_visible_shipper_companies($conditions = array(), $update = array()){
        extract($conditions);
        if (empty($users_list)){
			return false;
        }

        $users_list = getArrayFromString($users_list);

        $this->db->in('id_user', $users_list);

        return $this->db->update($this->orders_shippers_table, $update);
    }

	public function change_blocked_users_items($conditions = array(), $update = array()){
        extract($conditions);

        if(empty($users_list)){
            return false;
        }

        $users_list = getArrayFromString($users_list);

        $this->db->in('id_seller', $users_list);

        if(isset($blocked)){
		    $this->db->where('blocked', $blocked);
        }

        $this->db->update($this->items_table, $update);

        if(isset($update['blocked'])){
            if($update['blocked'] > 0){
                $elasticsearch_query = array(
                    "terms" => array(
                        "id_seller" => $users_list
                    )
                );
                library('elasticsearch')->delete_by_query('items', $elasticsearch_query);
            } else{
                $query = $this->createQueryBuilder();
                $query
                    ->select('id')
                    ->from('items')
                    ->where(
                        $query->expr()->in(
                            'id_seller',
                            array_map(
                                fn (int $index, $user) => $query->createNamedParameter(
                                    (int) $user,
                                    ParameterType::STRING,
                                    $this->nameScopeParameter("userId{$index}")
                                ),
                                array_keys($users_list),
                                $users_list
                            )
                        )
                    )
                ;

                /** @var Statement $statement */
                $statement = $query->execute();
                $items_id = array_column($statement->fetchAllAssociative(), 'id');
                if (!empty($items_id)) {
                    model('elasticsearch_items')->index($items_id);
                }
            }
        }
	}

	public function change_blocked_users($conditions = array(), $update = array()){
        extract($conditions);

        if(empty($users_list)){
            return false;
        }

        $users_list = getArrayFromString($users_list);

        $this->db->in('idu', $users_list);

        if(isset($user_page_blocked)){
		    $this->db->where('user_page_blocked', $user_page_blocked);
        }

		return $this->db->update($this->users_table, $update);
	}

	public function block_companies_index_name($conditions = array()){
        extract($conditions);
        if(empty($users_list)){
			return false;
        }

        $users_list = getArrayFromString($users_list);

		$sql = "UPDATE $this->company_base_table
				SET index_name_temp = index_name,
					index_name = ''
				WHERE id_user IN (" . implode(',', array_fill(0, count($users_list), '?')) . ")";
		return $this->db->query($sql, $users_list);
	}

	public function unblock_companies_index_name($conditions = array()){
        extract($conditions);
        if(empty($users_list)){
			return false;
		}

        $users_list = getArrayFromString($users_list);

		$sql = "UPDATE $this->company_base_table
				SET index_name = index_name_temp,
					index_name_temp = ''
				WHERE id_user IN (" . implode(',', array_fill(0, count($users_list), '?')) . ") AND index_name = '' AND index_name_temp != ''";
		return $this->db->query($sql, $users_list);
	}
}
