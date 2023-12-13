<?php

declare(strict_types=1);

use App\Common\Database\BaseModel;

/**
 *
 *
 * model
 *
 *
 * @deprecated
 */
class Admin_Contact_Model extends BaseModel
{
	private $users_table = "users";
	private $users_groups_table = "user_groups";
	private $admin_contact_table = "admin_contact";

	public function send_admin_message($insert)
	{
		return $this->db->insert($this->admin_contact_table, $insert);
	}

	public function get_contact_admin_messages($conditions)
	{
		$base_columns = "ac.*";
		$additional_columns = "CONCAT(u.fname, ' ', u.lname) as user_name, gr.gr_type, u.user_photo, u.logged";
		$order_by = "";

		extract($conditions);

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		if(isset($online)){
            $this->db->where('u.logged', $online);
		}

		if(isset($user)){
            $this->db->where('u.idu', $user);
		}

		if(isset($date_from)){
            $this->db->where('ac.date_time', $date_from);
		}

		if(isset($date_to)){
            $this->db->where('ac.date_time', $date_to);
        }

        $columns = implode(', ', array($base_columns, $additional_columns));

        $this->db->select($columns);
        $this->db->from("{$this->admin_contact_table} ac");
        $this->db->join("{$this->users_table} u", "ac.id_sender = u.idu", "left");
        $this->db->join("{$this->users_groups_table} gr", "gr.idgroup = u.user_group", "left");

        if(!empty($order_by)){
            $this->db->orderby($order_by);
        }

        if(isset($start) && isset($per_p)){
            $this->db->limit($per_p, $start);
        }

        return $this->db->get();
	}

	public function get_contact_admin_messages_count($conditions)
	{
		$where = array();
		$params = array();

		extract($conditions);

		if(isset($online)){
			$where[] = " u.logged=? ";
			$params[] = $online;
		}

		if(isset($user)){
			$where[] = " u.idu=? ";
			$params[] = $user;
		}

		if(isset($date_from)){
			$where[] = " ac.date_time >= ? ";
			$params[] =  $date_from;
		}

		if(isset($date_to)){
			$where[] = " ac.date_time <=? ";
			$params[] = $date_to;
        }

		if(isset($keywords)){
			$where[] = " MATCH (ac.content, ac.search_info) AGAINST (?) ";
			$params[] = $keywords;
        }

		$sql = "SELECT COUNT(*) as counter
				FROM admin_contact ac
                LEFT JOIN users u ON ac.id_sender = u.idu";

		if(count($where))
			$sql .= " WHERE " . implode(" AND ", $where);

		$temp = $this->db->query_one($sql, $params);
		return $temp['counter'];
	}
}
