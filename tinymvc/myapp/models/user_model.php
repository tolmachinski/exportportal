<?php
/**
 * user_model.php
 * users model
 * @author Litra Andrei
 */

use App\Common\Database\BaseModel;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Statement;

/**
 * @deprecated in favor of \Users_Model
 */
class User_Model extends BaseModel {

	// hold the current controller instance
    private $users_table = "users";
    private $user_notifications_table = "users_notification_messages";
    private $relation_industry_table = 'users_relation_industries';
	private $country_table = "port_country";
	private $users_table_primary_key = 'idu';
	private $users_groups_table = "user_groups";
    private $user_info_change_table = "user_info_change";
	private $user_langs_restrictions_table = "users_lang_restrictions";
	private $additional_rights_table = 'user_rights_aditional';
	private $shipper_users_table = "shipper_users";
	private $shipper_staff_groups_table = "shipper_staff_groups";
	private $shipper_staff_user_group_table = "shipper_staff_user_group";
	private $langs_table = "translations_languages";
	private $session_expire_min = 15;
	private $rights_table = 'rights';
	private $emails_labels = array(
		'Ok' => 'success',
		'Unknown' => 'warning',
		'Bad' => 'danger'
	);
	private $user_columns_exported_to_crm = array(
        'idu' => '',
        'zoho_id_record',
		'fname' => '',
		'lname' => '',
		'email' => '',
        'user_group' => '',
		'user_photo' => '',
		'registration_date' => '',
		'address' => '',
		'phone_code' => '',
		'phone' => '',
		'description' => '',
		'fax_code' => '',
		'fax' => '',
		'is_verified' => '',
		'status' => '',
		'skype' => '',
		'website' => '',
		'facebook' => '',
		'twitter' => '',
		'instagram' => '',
		'linkedin' => '',
		'youtube' => '',
    );

	/**
	 * Returns the users table name
	 *
	 * @return string
	 */
	public function get_users_table(): string
	{
		return $this->users_table;
    }

    private function get_notifications_table(): string
    {
        return $this->user_notifications_table;
    }

	/**
	 * Returns the user groups table name
	 *
	 * @return string
	 */
	public function get_user_groups_table(): string
	{
		return $this->users_groups_table;
	}

	/**
	 * Returns the users table primary key
	 *
	 * @return string
	 */
	public function get_users_table_primary_key(): string
	{
		return $this->users_table_primary_key;
	}

	public function users_export_types() {
        $buyer_fields = array(
            'Name' => 'u.fname',
            'Surname' => 'u.lname',
            'User email' => 'u.email',
            'Status' => 'u.status',
            'Registration date' => 'u.registration_date',
            'User address' => 'u.address',
            'Full name' => 'concat(u.fname, " ", u.lname)',
            'User phone' => 'concat(u.phone_code, " ", u.phone)',
            'Group name' => 'ug|ug.gr_name',
            'User city' => 'z|z.city',
            'User country' => 'pcu|pcu.country',
        );

        return array(
            'Buyer' => $buyer_fields,
            'Seller' => $buyer_fields,
            'Shipper' => $buyer_fields
        );
    }

	function setUserMain($results){
        $this->db->insert($this->users_table, $results);

		$last_inserted_id = $this->db->last_insert_id();

		if ( ! empty($last_inserted_id)) {
            /** @var User_Statistic_Model $userStatisticModel */
            $userStatisticModel = model(User_Statistic_Model::class);

            /** @var Crm_Model $crmModel */
            $crmModel = model(Crm_Model::class);

            $userStatisticModel->init_user_statistic($last_inserted_id);
            $crmModel->create_or_update_record($last_inserted_id);
		}

		return $last_inserted_id;
	}

	function deleteUser($id_user)
	{
		$this->db->where('idu', $id_user);
		$this->db->delete($this->users_table);
	}

	function getIdPrincipalByUserId($id_user){
		$this->db->select('idu, id_principal');
		$this->db->from($this->users_table);
		$this->db->where_raw('id_principal = (SELECT id_principal from ' . $this->users_table . ' WHERE idu= ?)', [$id_user]);
		return $this->db->query_all();
	}
	function updateUserMain($id, $data){
		$this->db->where('idu', $id);
		$result = $this->db->update($this->users_table, $data);

        if ($result && isset($data['fname'], $data['lname'])) {
            /** @var Elasticsearch_User_Model $elasticsearchUserModel */
            $elasticsearchUserModel = model(Elasticsearch_User_Model::class);

            $elasticsearchUserModel->update_other_models((int) $id, (string) $data['fname'], (string) $data['lname']);
		}

		$need_export_to_crm = array_intersect_key($this->user_columns_exported_to_crm, $data);

		if ( ! empty($need_export_to_crm)) {
            /** @var Crm_Model $crmModel */
            $crmModel = model(Crm_Model::class);

			$crmModel->create_or_update_record($id);
		}

        return $result;
	}

	/**
	 * Cancels the verification for the users
	 *
	 * @param int[] $users_ids
	 *
	 * @return boolean
	 */
	public function cancel_verification_of_users(array $users_ids)
	{
		if (empty($users_ids)) {
			return true;
		}
		$this->db->in('idu', $users_ids);

		foreach ($users_ids as $user_id) {
            /** @var Crm_Model $crmModel */
            $crmModel = model(Crm_Model::class);

			$crmModel->create_or_update_record($user_id);
		}

		return $this->db->update($this->users_table, array('is_verified' => 0));
	}

    public function get_user_lang_restriction($ids, array $params = array())
    {
        $columns = "*";
        $with = array();
        $conditions = array();
        $order = array();
        $group = array();
        $limit = null;
        $skip = null;
        $users = arrayable_to_array($ids);
        if(empty($users)) {
            return array();
        }

        extract($params);
        extract($with, EXTR_PREFIX_ALL, 'with');
        extract($conditions, EXTR_PREFIX_ALL, 'condition');
        if(!empty($columns)) {
            $columns = is_string($columns) ? $columns : implode(', ', (array) $columns);
        } else {
            $columns = "*";
        }

        $this->db->select($columns);
        $this->db->from("{$this->user_langs_restrictions_table} ulr");

        // Resolve joins
        if(!empty($with)) {
            if(isset($with_lang) && $with_lang) {
                $this->db->join("{$this->langs_table} l", "ON JSON_CONTAINS(R.languages, JSON_ARRAY(L.id_lang))", 'left');
            }
            if(isset($with_users) && $with_users) {
                $this->db->join("{$this->users_table} u", "u.id_user = ulr.id_user", 'left');
            }
        }

        //Resolve conditions
        $this->db->in("ulr.id_user", $users);
        if(!empty($conditions)) {
            // Here be dragons
        }

        // Resolve group by
        foreach ($group as $column) {
            $this->db->groupby($column);
        }

        // Resolve order by
        foreach ($order as $column => $direction) {
            if (!empty($direction) && is_string($direction)) {
                $this->db->orderby("{$column} {$direction}");
            } else {
                $this->db->orderby($column);
            }
        }

        // Resolve limit
        if (null !== $limit) {
            if (null !== $skip) {
                $this->db->limit($limit, $skip);
            } else {
                $this->db->limit($limit);
            }
        }

        // Fetch data
        if (!$this->db->query()) {
            return array();
        }

        if(!empty($with) || (!is_scalar($ids) && is_arrayable($ids))) {
            $data = $this->db->getQueryResult()->fetchAllAssociative();
        } else {
            $data = $this->db->getQueryResult()->fetchAssociative();
        }

        return $data ? $data : array();
    }

    public function create_user_lang_restriction($user_id, $languages)
    {
        if (empty($user_id) || empty($languages)) {
            return false;
        }

        $languages = arrayable_to_array($languages);
        $insert_data = array(
            'id_user'   => $user_id,
            'languages' => json_encode($languages, JSON_PRETTY_PRINT)
        );

        return $this->db->insert($this->user_langs_restrictions_table, $insert_data);
    }

    public function remove_user_lang_restriction($user_id)
    {
        $this->db->where('id_user = ?', $user_id);

        return $this->db->delete($this->user_langs_restrictions_table);
    }

    public function replace_user_lang_restriction($user_id, $languages)
    {
        $removed = $this->remove_user_lang_restriction($user_id);
        if (empty($languages)) {
            return $removed;
        }

        return $removed && $this->create_user_lang_restriction($user_id, $languages);
    }

    function get_users_for_export($params = [])
    {
        $this->db->select($params['select_fields']);
        $this->db->from('users u');
        $this->db->where('u.fake_user', 0);
        $this->db->in('u.user_group', $params['groups']);

        if ($params['country']) {
            $this->db->where('u.country', $params['country']);
		}
        if ($params['status']) {
            $this->db->where('u.status', $params['status']);
        }
        if ($params['reg_from']) {
            $this->db->where('DATE(u.registration_date) >=', $params['reg_from']);
        }
        if ($params['reg_to']) {
            $this->db->where('DATE(u.registration_date) <=', $params['reg_to']);
        }

        if (!empty($params['restrictedFrom']) || !empty($params['restrictedTo'])) {
            $joinWhere = [" users_blocking_statistics.`type` = 'restriction' "];

            if (!empty($params['restrictedFrom'])) {
                $joinWhere[] = " DATE(users_blocking_statistics.`blocking_date`) >= '{$params['restrictedFrom']}' ";
            }

            if (!empty($params['restrictedTo'])) {
                $joinWhere[] = " DATE(users_blocking_statistics.`blocking_date`) <= '{$params['restrictedTo']}' ";
            }

            $joinConditions = implode('AND', $joinWhere);

            $this->db->join(
                "(
                    SELECT users_blocking_statistics.`id_user`
                    FROM users_blocking_statistics
                    WHERE {$joinConditions}
                    GROUP BY users_blocking_statistics.`id_user`
                ) restrictions",
                "u.`idu` = restrictions.`id_user`",
                "right"
            );
        }

        if (!empty($params['blockedFrom']) || !empty($params['blockedTo'])) {
            $joinWhere = [" users_blocking_statistics.`type` = 'blocking' "];

            if (!empty($params['blockedFrom'])) {
                $joinWhere[] = " DATE(users_blocking_statistics.`blocking_date`) >= '{$params['blockedFrom']}' ";
            }

            if (!empty($params['blockedTo'])) {
                $joinWhere[] = " DATE(users_blocking_statistics.`blocking_date`) <= '{$params['blockedTo']}' ";
            }

            $joinConditions = implode('AND', $joinWhere);

            $this->db->join(
                "(
                    SELECT users_blocking_statistics.`id_user`
                    FROM users_blocking_statistics
                    WHERE {$joinConditions}
                    GROUP BY users_blocking_statistics.`id_user`
                ) blocking",
                "u.`idu` = blocking.`id_user`",
                "right"
            );
        }

        foreach($params['joins'] as $join){
            switch($join){
                case 'ug':
                    $this->db->join('user_groups ug', 'u.user_group = ug.idgroup', 'left');
                break;
                case 'z':
                    $this->db->join('zips z', 'u.city = z.id', 'left');
                break;
                case 'pcu':
                    $this->db->join('port_country pcu', 'u.country = pcu.id', 'left');
                break;
            }
        }

        $this->db->orderby('u.registration_date DESC');
        return $this->db->query_all();
	}

	function getUser($idu = 0){
		$sql = "SELECT 	u.*, CONCAT_WS(' ',u.fname, u.lname) as user_name,
						ug.gr_name, ug.gr_type, ug.gr_alias, ug.stamp_pic,
						pc.country as user_country
				FROM users u
				LEFT JOIN user_groups ug ON u.user_group = ug.idgroup
				LEFT JOIN port_country pc ON u.country = pc.id
				WHERE u.idu = ?";
		return $this->db->query_one($sql, array($idu));
	}

	function getUserForAddAccounts($idu)
	{
		$columns = array(
			'fname',
			'lname',
            'legal_name',
			'email',
			'email_status',
			'user_ip',
			'fake_user',
			'country',
			'state',
			'city',
			'zip',
			'address',
			'phone_code_id',
			'phone_code',
			'phone',
            'fax',
            'fax_code',
            'fax_code_id',
			'user_initial_lang_code',
			'id_principal',
            'description',
            'user_find_type',
            'user_find_info',
		);
		$this->db->select(implode(',', $columns));
		$this->db->from('users u');
		$this->db->where('u.idu', $idu);
		return $this->db->query_one();

	}

	function get_users_data_for_crm($users_ids) {
		$columns = array(
            'u.idu',
            'u.zoho_id_record',
            'CONCAT(u.fname, " ", u.lname) AS full_name',
            'u.fname AS first_name',
			'u.lname AS last_name',
			'u.email AS email',
			'u.user_photo AS photo',
			'u.registration_date AS registration_date',
			'u.address AS address',
			'CONCAT(u.phone_code, u.phone) AS phone',
			'u.description AS description',
			'CONCAT(u.fax_code, u.fax) AS fax',
			'u.is_verified',
            'u.status',
            'u.user_group',
			'u.zip',
			'u.skype AS skype',
			'u.website AS website',
			'u.facebook AS facebook',
			'u.twitter AS twitter',
			'u.instagram AS instagram',
			'u.linkedin AS linkedin',
			'u.youtube AS youtube',
			'ug.gr_type AS group_type',
			'ug.gr_name AS group_name',
			'pc.country AS country',
			's.state AS state',
			'z.city AS city',
            'z.timezone',
		);

		$this->db->select(implode(',', $columns));
		$this->db->from('users u');
		$this->db->join('user_groups ug', 'u.user_group = ug.idgroup', 'left');
		$this->db->join('port_country pc', 'u.country = pc.id', 'left');
		$this->db->join('states s', 'u.state = s.id', 'left');
		$this->db->join('zips z', 'u.city = z.id', 'left');
		$this->db->join('crm_sync_users crm', 'u.idu = crm.id_user', 'left');
		$this->db->in('u.user_group', array(1, 2, 3, 5, 6, 31));
		$this->db->where_raw('(crm.id_user IS NULL OR crm.is_resolved = 1)');
		$this->db->in('u.idu', $users_ids);

		return $this->db->query_all();
	}

	function get_user_by_condition($conditions){
		$where = array();
		$params = array();

		extract($conditions);

		//not valid anymore since email is not unique
		// if(isset($email)){
		// 	$where[] = " u.email = ? ";
		// 	$params[] = $email;
		// }

		if(isset($id_user)) {
			$where[] = " u.idu = ? ";
			$params[] = $id_user;
		}

		if (isset($id_principal)) {
			$where[] = " u.id_principal = ? ";
			$params[] = $id_principal;
		}

		if(isset($id_company)) {
			$where[] = " u.id_company = ? ";
			$params[] = $id_company;
        }

		if(isset($activation_code)){
			$where[] = " u.activation_code = ? ";
			$params[] = $activation_code;
		}

		if(isset($accreditation_token)){
			$where[] = " u.accreditation_token = ? ";
			$params[] = $accreditation_token;
		}

		if(isset($accreditation_files)){
			$where[] = " u.accreditation_files = ? ";
			$params[] = $accreditation_files;
		}

		if(isset($status)) {
			$where[] = " u.`status` IN({$status})";
        }

		if (isset($status_is_not)) {
            $status_is_not = getArrayFromString($status_is_not);
			$where[] = " u.`status` NOT IN(" . implode(', ', array_fill(0, count($status_is_not), '?')) . ")";
            array_push($params, ...$status_is_not);
        }

        if (isset($user_group)) {
            $user_group = getArrayFromString($user_group);
			$where[] = " u.`user_group` IN(" . implode(', ', array_fill(0, count($user_group), '?')) . ")";
            array_push($params, ...$user_group);
        }

		if(empty($where)){
			return false;
		}

		$sql = "SELECT u.*, CONCAT_WS(' ',u.fname, u.lname) as user_name,
						ug.gr_name, ug.gr_type, ug.gr_alias, ug.stamp_pic
				FROM users u
				LEFT JOIN user_groups ug ON u.user_group = ug.idgroup
				WHERE " . implode(" AND ", $where) ."
				LIMIT 1";
		return $this->db->query_one($sql, $params);
	}

	function get_shipper_staff_users($conditions = array()){
		$order_by = " registration_date DESC ";
		$per_p = 10;
		$page = 1;

		extract($conditions);

		$where = $params = [];

		if (isset($users_list)) {
            $users_list = getArrayFromString($users_list);
			$where[] = " u.idu IN (" . implode(',', array_fill(0, count($users_list), '?')) . ")";
            array_push($params, ...$users_list);
        }

		if(isset($group)){
			$where[] = " sug.id_group = (?)";
            $params[] = $group;
		}

		if (isset($status)) {
            $status = getArrayFromString($status);
			$where[] = " u.status IN(" . implode(',', array_fill(0, count($status), '?')) . ")";
            array_push($params, ...$status);
        }

		if(isset($user_type)) {
            $user_type = getArrayFromString($user_type);
			$where[] = " u.user_type IN(" . implode(','. array_fill(0, count($user_type), '?')) . ")";
            array_push($params, ...$user_type);
        }

		if(isset($logged)){
			$where[] = " u.logged = ? ";
			$params[] = $logged;
		}

		if(isset($ip)){
			$where[] = " u.user_ip = ? ";
			$params[] = $ip;
		}

		if(isset($shipper)){
			$where[] = " su.id_shipper = ? ";
			$params[] = $shipper;
		}

		if(isset($registration_start_date)){
			$where[] = " u.registration_date >= ? ";
			$params[] = formatDate($registration_start_date, 'Y-m-d');
		}

		if(isset($registration_end_date)){
			$where[] = " u.registration_date <= ? ";
			$params[] = formatDate($registration_end_date, 'Y-m-d');
		}

		if(isset($activity_start_date)){
			$where[] = " u.last_active >= ? ";
			$params[] = formatDate($activity_start_date, 'Y-m-d');
		}

		if(isset($activity_end_date)){
			$where[] = " u.last_active <= ? ";
			$params[] = formatDate($activity_end_date, 'Y-m-d');
		}

		if(isset($keywords)){
			$words = explode(" ", $keywords);
			foreach($words as $word){
				if(strlen($word) > 3) {
					$s_word[] = " (u.fname LIKE ? OR u.lname LIKE ? OR u.email LIKE ?)";
                    array_push($params, ...array_fill(0, 3, '%' . $word . '%'));
                }
			}

			if (!empty($s_word)) {
				$where[] = " (" . implode(" AND ", $s_word) . ")";
            }
		}

		$sql = "SELECT u.*, sg.name_sgroup, sg.id_sgroup
				FROM users u
				INNER JOIN $this->shipper_users_table su ON su.id_user = u.idu
				LEFT JOIN $this->shipper_staff_user_group_table sug ON u.idu = sug.id_user
				LEFT JOIN $this->shipper_staff_groups_table sg ON sug.id_group = sg.id_sgroup ";

		if (!empty($where)){
			$sql .= " WHERE " . implode(" AND ", $where);
		}

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(' , ', $multi_order_by);
		}

		$sql .= " ORDER BY {$order_by}";

        if(isset($start) && isset($per_p)){
            $start = (int) $start;
            $per_p = (int) $per_p;

            $sql .= " LIMIT $start, $per_p";
		}

		return $this->db->query_all($sql, $params);
	}

	function get_staff_user($idu, $id_company){
		$sql = "SELECT us.*, csug.id_group
				FROM users us
				LEFT JOIN company_users cu ON us.idu = cu.id_user
				LEFT JOIN company_staff_user_group csug ON us.idu = csug.id_user
				WHERE us.idu = ? AND cu.id_company = ? AND us.user_type = 'users_staff'";
		return $this->db->query_one($sql, array($idu, $id_company));
	}

	function get_staff_users($id_company, $conditions = array()){
		extract($conditions);

        $where = array(" cu.id_company = ? AND us.user_type = 'users_staff' ");
		$params = array($id_company);

		if(isset($users_list)){
            $users_list = getArrayFromString($users_list);
			$where[] = " cu.id_user IN (" . implode(',', array_fill(0, count($users_list), '?')) . ") ";
            array_push($params, ...$users_list);
		}

		$sql = "SELECT us.*, csug.id_group
				FROM company_users cu
				LEFT JOIN users us ON cu.id_user = us.idu
				LEFT JOIN company_staff_user_group csug ON us.idu = csug.id_user";

		$sql .= " WHERE " . implode(" AND ", $where);

		return $this->db->query_all($sql, $params);
	}

	public function getSimpleUser($userId, $columns = "users.*")
    {
        $query = $this->createQueryBuilder();
        $query
            ->select($columns, "ug.stamp_pic", "ug.gr_name", "ug.gr_type")
            ->from('users')
            ->leftJoin('users', 'user_groups', 'ug', 'users.user_group = ug.idgroup')
            ->where(
                $query->expr()->eq('users.idu', $query->createNamedParameter($userId, null, ':user'))
            )
        ;
        /** @var Statement $statement */
        $statement = $query->execute();

        return $statement->fetchAssociative() ?: null;
	}

	function getSimpleUserByEmail($email, $columns = "users.*"){
		$sql = "SELECT $columns, ug.stamp_pic, ug.gr_name, ug.gr_type
				FROM users
				LEFT JOIN user_groups ug ON  users.user_group = ug.idgroup
				WHERE users.email = ? ";
		return $this->db->query_one($sql, array($email));
	}

    /**
     * Get the list of users by their IDs.
     *
     * @param string|array $usersList
     * @param string $columns
     * @return void
     */
	public function getSimpleUsers($usersList, $columns = "users.*")
    {
        // Transform list to array
        if (is_string($usersList)) {
            $usersList = explode(',', $usersList ?: '') ?: [];
        } else {
            $usersList = (array) $usersList;
        }

        // Filter and normalize list
        $usersList = array_filter(array_map(fn($id) => (int) $id, $usersList), fn($id) => $id);
        if (empty($usersList)) {
            return [];
        }

        $query = $this->createQueryBuilder();
        $query
            ->select($columns, "ug.stamp_pic", "ug.gr_name", "ug.gr_type")
            ->from('users')
            ->leftJoin('users', 'user_groups', 'ug', 'users.user_group = ug.idgroup')
            ->where(
                $query->expr()->in(
                    'users.idu',
                    array_map(
                        fn (int $index, $id) => $query->createNamedParameter(
                            (int) $id,
                            ParameterType::INTEGER,
                            $this->nameScopeParameter("userId{$index}")
                        ),
                        array_keys($usersList),
                        $usersList
                    )
                )
            )
        ;
        /** @var Statement $statement */
        $statement = $query->execute();

        return $statement->fetchAllAssociative() ?: null;
	}

    function getUserByEmail($email)
    {
		$this->db->select('*');
		$this->db->from($this->users_table);
		$this->db->where('email', $email);
		return $this->db->query_one();
	}

	function getUserByCleanSessionToken($token){
		$sql = "SELECT *
				FROM users
				WHERE clean_session_token = ?";
		return $this->db->query_one($sql, array($token));
	}

	function get_simple_users($conditions = array()){
		$order_by = " registration_date DESC ";

		extract($conditions);

		$where = $params = [];

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(' , ', $multi_order_by);
		}

		if(!empty($users_list)){
            $users_list = getArrayFromString($users_list);
			$where[] = " idu IN (" . implode(',', array_fill(0, count($users_list), '?')) . ") ";
            array_push($params, ...$users_list);
        }

        if(isset($registration_start_date)){
			$where[] = " DATE(registration_date) >= ? ";
			$params[] = $registration_start_date;
		}

		if(isset($registration_end_date)){
			$where[] = " DATE(registration_date) <= ? ";
			$params[] = $registration_end_date;
        }

        if (isset($fake_user)) {
            $where[] = " fake_user = ? ";
            $params[] = (int) $fake_user;
        }

		if(isset($group)) {
            $group = getArrayFromString($group);
			$where[] = " user_group IN (" . implode(',', array_fill(0, count($group), '?')) . ")";
            array_push($params, ...$group);
        }

        $selected_columns = $select ?? '*';

		$sql = "SELECT {$selected_columns} FROM users ";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
        }

		$sql .= " ORDER BY " . $order_by;

		return $this->db->query_all($sql, $params);
	}

	function get_users_last_id(){
		$sql = "SELECT idu
				FROM users
				ORDER BY idu DESC
				LIMIT 0,1";

		return $this->db->query_one($sql)['idu'] ?: 0;
	}

	function get_count_new_users($id_user){
		$sql = "SELECT COUNT(*) as counter
				FROM users
				WHERE idu > ? ";

		return $this->db->query_one($sql, array($id_user))['counter'];
	}

	function getGmapUsers($conditions = array()){
		$additional = false;
		$join_additional = "";

		$company_info = false;
		$city_detail = false;

		extract($conditions);

		$where = $params = [];

        if(!empty($users_list)){
            $users_list = getArrayFromString($users_list);
			$where[] = " u.idu IN (" . implode(',', array_fill(0, count($users_list), '?')) . ") ";
            array_push($params, ...$users_list);
        }

		if(isset($group)) {
            $group = getArrayFromString($group);
			$where[] = " u.user_group IN (" . implode(',', array_fill(0, count($group), '?')) . ")";
            array_push($params, ...$group);
        }

		if(isset($group_type)) {
            $group_type = getArrayFromString($group_type);
			$where[] = " gr.gr_type IN (" . implode(',', array_fill(0, count($group_type), '?')) . ")";
            array_push($params, ...$group_type);
        }

		if(isset($status)) {
            $status = getArrayFromString($status);
			$where[] = " u.status IN(" . implode(',', array_fill(0, count($status), '?')) . ")";
            array_push($params, ...$status);
		}

		if(isset($email_status)) {
            $email_status = getArrayFromString($email_status);
            $where[] = " u.email_status IN(" . implode(',', array_fill(0, count($email_status), '?')) . ")";
            array_push($params, ...$email_status);
        }

		if(isset($accreditation_files)){
			$where[] = " u.accreditation_files = ? ";
			$params[] = $accreditation_files;
		}

		if(isset($logged)){
			$where[] = " u.logged = ? ";
			$params[] = $logged;
		}

		if(isset($fake_user)){
			$where[] = " u.fake_user = ? ";
			$params[] = $fake_user;
        }

        if(isset($is_model)){
			$where[] = " u.is_model = ? ";
			$params[] = $is_model;
		}

		if(isset($ip)){
			$where[] = " u.user_ip = ? ";
			$params[] = $ip;
		}

		if(isset($user_find_type)){
			$where[] = " u.user_find_type = ? ";
			$params[] = $user_find_type;

			if(isset($user_find_info)){
				$where[] = " u.user_find_info = ? ";
				$params[] = $user_find_info;
			}
		}

		if(isset($registration_start_date)){
			$where[] = " DATE(u.registration_date) >= ? ";
			$params[] = formatDate($registration_start_date, 'Y-m-d');
		}

		if(isset($registration_end_date)){
			$where[] = " DATE(u.registration_date) <= ? ";
			$params[] = formatDate($registration_end_date, 'Y-m-d');
		}

		if(isset($resend_email_from_date)){
			$where[] = " DATE(u.resend_email_date) >= ? ";
			$params[] = formatDate($resend_email_from_date, 'Y-m-d H:i:s');
		}

		if(isset($resend_email_to_date)){
			$where[] = " DATE(u.resend_email_date) <= ? ";
			$params[] = formatDate($resend_email_to_date, 'Y-m-d H:i:s');
		}

		if(isset($activity_start_date)){
			$where[] = " DATE(u.last_active) >= ? ";
			$params[] = formatDate($activity_start_date, 'Y-m-d');
		}

		if(isset($activity_end_date)){
			$where[] = " DATE(u.last_active) <= ? ";
			$params[] = formatDate($activity_end_date, 'Y-m-d');
		}

		if($additional){
			$join_additional .= " LEFT JOIN port_country pc ON u.country = pc.id ";

			if(isset($id_continent)){
				$where[] = " pc.id_continent = ? ";
				$params[] = $id_continent;
			}

			if(isset($country)){
				$where[] = " u.country = ?";
				$params[] = $country;
			}

			if(isset($country_list)){
                $country_list = getArrayFromString($country_list);
				$where[] = " u.country IN (" . implode(',', array_fill(0, count($country_list), '?')) . ") ";
                array_push($params, ...$country_list);
			}

			if(isset($state)){
				$where[] = " u.state = ?";
				$params[] = $state;
			}

			if(isset($city)){
				$where[] = " u.city = ?";
				$params[] = $city;
			}
		}

		if(isset($statistic_filter)){
			$statistic_where = array();
			if(isset($statistic_filter['items_total']['from'])){
				$statistic_where[] = " us.items_total >= {$statistic_filter['items_total']['from']} ";
			}

			if(isset($statistic_filter['items_total']['to'])){
				$statistic_where[] = " us.items_total <= {$statistic_filter['items_total']['to']} ";
			}

			if (!empty($statistic_where)) {
				$join_additional .= " INNER JOIN user_statistic us ON u.idu = us.id_user AND " . implode(" AND ", $statistic_where);
			}
		}

		if(isset($keywords)){
			$words = explode(" ", $keywords);
			foreach($words as $word){
				if (strlen($word) > 3) {
					$s_word[] = " (u.fname LIKE ? OR u.lname LIKE ? OR u.email LIKE ? OR CONCAT(phone_code,'',phone) LIKE ?)";
                    array_push($params, ...array_fill(0, 4, '%' . $word . '%'));
                }
			}

			if(!empty($s_word)){
				$where[] = " (" . implode(" OR ", $s_word) . ") ";
			}
		}

		$sql = "SELECT  u.*, CONCAT_WS(' ',u.fname, u.lname) as user_name,
						gr.gr_name, gr.gr_type
				FROM users u
				LEFT JOIN user_groups gr ON u.user_group = gr.idgroup
				$join_additional";

		if(!empty($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
		}

		return $this->db->query_all($sql, $params);
	}

    /**
     * get users
     *
     * @param array $conditions
     *
     * @return array
     */
    public function findUsers(array $conditions): array
    {
        return $this->findRecords(
            null,
            $this->get_users_table(),
            null,
            $conditions
        );
    }

    /**
     * @deprecated
     * @see findUsers()
     */
	function getUsers($conditions = array()){
		$additional = false;
		$select_additional= "";
		$join_additional = "";

		$company_info = false;
		$city_detail = false;

		$order_by = " registration_date DESC";

		$per_p = 10;
		$page = 1;

		extract($conditions);

		$where = $params = [];

		if(isset($id_user)) {
			$where[] = " u.idu = ? ";
			$params[] = $id_user;
		}

		if(isset($user_photo_exist)) {
			$where[] = " u.user_photo != '' ";
		}

		if(isset($user_photo_actulized)) {
			$where[] = " u.user_photo_actulized = ? ";
			$params[] = $user_photo_actulized;
		}

		if(isset($users_list)) {
			$users_list = getArrayFromString($users_list);
			$where[] = " u.idu IN (" . implode(',', array_fill(0, count($users_list), '?')) . ")";
			array_push($params, ...$users_list);
        }

		if(isset($group)) {
            $group = getArrayFromString($group);
			$where[] = " u.user_group IN (" . implode(',', array_fill(0, count($group), '?')) . ")";
            array_push($params, ...$group);
        }

		if(isset($group_type)) {
            $group_type = getArrayFromString($group_type);
			$where[] = " gr.gr_type IN (" . implode(',', array_fill(0, count($group_type), '?')) . ")";
            array_push($params, ...$group_type);
        }

        if (isset($status)) {
            $status = getArrayFromString($status);
			$where[] = " u.status IN(" . implode(', ', array_fill(0, count($status), '?')) . ")";
            array_push($params, ...$status);
		}

		if(isset($email_status)) {
            $email_status = getArrayFromString($email_status);
			$where[] = " u.email_status IN(" . implode(',', array_fill(0, count($email_status), '?')) . ")";
            array_push($params, ...$email_status);
        }

		if(isset($accreditation_files)){
			$where[] = " u.accreditation_files = ? ";
			$params[] = $accreditation_files;
		}

		if(isset($logged)){
			$where[] = " u.logged = ? ";
			$params[] = $logged;
        }

		if(isset($location_completion)){
            if (!$location_completion) {
                $where[] = "(u.country IS NULL OR u.state IS NULL OR u.city IS NULL OR u.country = 0 OR u.state = 0 OR u.city = 0)";
            } else {
                $where[] = "(u.country IS NOT NULL AND u.state IS NOT NULL AND u.city IS NOT NULL AND u.country != 0 AND u.state != 0 AND u.city != 0)";
            }
		}

		if(isset($fake_user)){
			$where[] = " u.fake_user = ? ";
			$params[] = $fake_user;
        }

        if(isset($is_model)){
			$where[] = " u.is_model = ? ";
			$params[] = $is_model;
		}

		if(isset($is_verified)){
			$where[] = " u.is_verified = ? ";
			$params[] = $is_verified;
		}

		if(isset($ip)){
			$where[] = " u.user_ip = ? ";
			$params[] = $ip;
		}

		if(isset($user_find_type)){
			$where[] = " u.user_find_type = ? ";
			$params[] = $user_find_type;

			if(isset($user_find_info)){
				$where[] = " u.user_find_info = ? ";
				$params[] = $user_find_info;
			}
		}

		if(isset($registration_start_date)){
			$where[] = " DATE(u.registration_date) >= ? ";
			$params[] = formatDate($registration_start_date, 'Y-m-d');
		}

		if(isset($registration_end_date)){
			$where[] = " DATE(u.registration_date) <= ? ";
			$params[] = formatDate($registration_end_date, 'Y-m-d');
		}

		if(isset($document_upload_date_from)){
			$where[] = " DATE(u.accreditation_files_upload_date) >= ? ";
			$params[] = formatDate($document_upload_date_from, 'Y-m-d H:i:s');
		}

		if(isset($document_upload_date_to)){
			$where[] = " DATE(u.accreditation_files_upload_date) <= ? ";
			$params[] = $document_upload_date_to;
		}

		if(isset($resend_email_from_date)){
			$where[] = " DATE(u.resend_email_date) >= ? ";
			$params[] = $resend_email_from_date;
		}

		if(isset($resend_email_to_date)){
			$where[] = " DATE(u.resend_email_date) <= ? ";
			$params[] = formatDate($resend_email_to_date, 'Y-m-d H:i:s');
		}

		if(isset($activity_start_date)){
			$where[] = " DATE(u.last_active) >= ? ";
			$params[] = formatDate($activity_start_date, 'Y-m-d');
		}

		if(isset($activity_end_date)){
			$where[] = " DATE(u.last_active) <= ? ";
			$params[] = formatDate($activity_end_date, 'Y-m-d');
		}

		if(isset($on_crm)){
			$where[] = 'u.zoho_id_record IS ' . ($on_crm ? 'NOT ' : '') . 'NULL';
		}

		if(isset($is_verified)){
			$where[] = " u.is_verified = ? ";
			$params[] = $is_verified;
		}

        if (isset($zoho_id_record)) {
            $where[] = " u.zoho_id_record = ? ";
			$params[] = $zoho_id_record;
        }

		if($additional){
			$select_additional = ", pc.country as user_country, pc.zip ";
			$join_additional .= " LEFT JOIN port_country pc ON u.country = pc.id ";

			if(isset($id_continent)){
				$where[] = " pc.id_continent = ? ";
				$params[] = $id_continent;
			}

			if(isset($country)){
				$where[] = " u.country = ?";
				$params[] = $country;
            }

            if (isset($focus_countries)) {
                $where[] = " pc.is_focus_country = ?";
				$params[] = (int) $focus_countries;
            }

			if(isset($country_list)){
                $country_list = getArrayFromString($country_list);
				$where[] = " u.country IN (" . implode(',', array_fill(0, count($country_list), '?')) . ") ";
                array_push($params, ...$country_list);
			}

			if(isset($state)){
				$where[] = " u.state = ?";
				$params[] = $state;
			}

			if(isset($city)){
				$where[] = " u.city = ?";
				$params[] = $city;
			}
		}

		if($company_info){
			$select_additional .= ", cb.name_company, cb.legal_name_company, cb.id_company, cb.index_name, cb.type_company, cb.logo_company, cb.rating_company";
			$join_additional .= " LEFT JOIN company_base cb ON u.idu = cb.id_user ";
			$where[] = " (cb.type_company = 'company' OR cb.id_company IS NULL) ";
		}

		if($city_detail){
			$select_additional .= ", CONCAT_WS(', ', z.city, st.state) as user_city, z.timezone ";
			$join_additional .= " 	LEFT JOIN zips z ON u.city = z.id
									LEFT JOIN states st ON u.state = st.id ";
        }

        if($auth_detail){
			$select_additional .= ", auth.reset_password_date ";
			$join_additional .= " 	LEFT JOIN auth_context_form auth ON u.id_principal = auth.id_principal";
        }

        if(isset($search_by_company)) {
            $params = [...$params, ...array_fill(0, 4, "%$search_by_company%")];
            $where[] = "
            ((u.idu IN (SELECT id_user FROM company_base WHERE name_company LIKE ? OR legal_name_company LIKE ?)) OR
            (u.idu IN (SELECT id_user FROM company_buyer WHERE company_name LIKE ? )) OR
            (u.idu IN (SELECT id_user FROM orders_shippers WHERE co_name LIKE ? ))) ";
        }

        if(isset($search_by_item)) {
            $params = [...$params, "%$search_by_item%"];
            $where[] = " (u.idu IN (SELECT id_seller FROM items WHERE title LIKE ?)) ";
        }

		if(isset($statistic_filter)){
			$statistic_where = array();
			if(isset($statistic_filter['items_total']['from'])){
				$statistic_where[] = " us.items_total >= {$statistic_filter['items_total']['from']} ";
			}

			if(isset($statistic_filter['items_total']['to'])){
				$statistic_where[] = " us.items_total <= {$statistic_filter['items_total']['to']} ";
			}

			if (!empty($statistic_where)) {
				$join_additional .= " INNER JOIN user_statistic us ON u.idu = us.id_user AND " . implode(" AND ", $statistic_where);
			}
        }

		if(isset($keywords)){
			$words = explode(" ", $keywords);
			foreach($words as $word){
				if (strlen($word) > 3) {
					$s_word[] = " (u.fname LIKE ? OR u.lname  LIKE ? OR u.email LIKE ? OR CONCAT(phone_code,'',phone) LIKE ?)";
                    array_push($params, ...array_fill(0, 4, '%' . $word . '%'));
                }
			}

			if (!empty($s_word)) {
				$where[] = " (" . implode(" AND ", $s_word) . ")";
            }
        }

		if(!empty($gmap_bounds)){
			$lng_operand = $gmap_bounds['nelng'] < $gmap_bounds['swlng']?" OR ":" AND ";
			$where[] = "(u.user_city_lng >= ? {$lng_operand} u.user_city_lng <= ?)";
			$where[] = "u.user_city_lat >= ? AND u.user_city_lat <= ?";
            array_push($params, ...[$gmap_bounds['swlng'], $gmap_bounds['nelng'], $gmap_bounds['swlat'], $gmap_bounds['nelat']]);
		}

        if (!empty($restrictedFrom) || !empty($restrictedTo)) {
            $joinWhere = [" users_blocking_statistics.`type` = 'restriction' "];

            if (!empty($restrictedFrom)) {
                $joinWhere[] = " DATE(users_blocking_statistics.`blocking_date`) >= '{$restrictedFrom}' ";
            }

            if (!empty($restrictedTo)) {
                $joinWhere[] = " DATE(users_blocking_statistics.`blocking_date`) <= '{$restrictedTo}' ";
            }

            $joinConditions = implode('AND', $joinWhere);

			$join_additional .= " RIGHT JOIN (
                SELECT users_blocking_statistics.`id_user`
                FROM users_blocking_statistics
                WHERE {$joinConditions}
                GROUP BY users_blocking_statistics.`id_user`
            ) restrictions ON u.`idu` = restrictions.`id_user` ";
        }

        if (!empty($blockedFrom) || !empty($blockedTo)) {
            $joinWhere = [" users_blocking_statistics.`type` = 'blocking' "];

            if (!empty($blockedFrom)) {
                $joinWhere[] = " DATE(users_blocking_statistics.`blocking_date`) >= '{$blockedFrom}' ";
            }

            if (!empty($blockedTo)) {
                $joinWhere[] = " DATE(users_blocking_statistics.`blocking_date`) <= '{$blockedTo}' ";
            }

            $joinConditions = implode('AND', $joinWhere);

			$join_additional .= " RIGHT JOIN (
                SELECT users_blocking_statistics.`id_user`
                FROM users_blocking_statistics
                WHERE {$joinConditions}
                GROUP BY users_blocking_statistics.`id_user`
            ) blocking ON u.`idu` = blocking.`id_user` ";
        }

		$user_select = 'u.*';
		if(isset($select_fields)){
			$user_select = cleanInput($select_fields);
		}
		$sql = "SELECT  $user_select, CONCAT_WS(' ',u.fname, u.lname) as user_name,
						gr.gr_name, gr.gr_type
						$select_additional
				FROM users u
				LEFT JOIN user_groups gr ON u.user_group = gr.idgroup
				$join_additional";

		if(!empty($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
        }

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(' , ', $multi_order_by);
		}

		$sql .= " ORDER BY " . $order_by;

        if (!isset($no_limits)) {
            if(!isset($start, $per_p)){
                if(!isset($users_list)){

                    if(!isset($count)) {
                        $count = $this->count_users($conditions);
                    }

                    /* block for count pagination */
                    $pages = ceil($count/$per_p);

                    if($page > $pages) {
                        $page = $pages;
                    }

                    if(!isset($start)){
                        $start = ($page-1)*$per_p;

                        if($start < 0)
                            $start = 0;
                    }

                    if($start >= 0) {
                        $sql .=  " LIMIT " . $start;
                    }

                    if($per_p > 0) {
                        $sql .= ", " . $per_p;
                    }
                }
            } else{
                $sql .=  " LIMIT $start, $per_p";
            }
        }

        return $this->db->query_all($sql, $params);
    }

	function count_users($conditions = array()){
		$additional = false;
		$company_info = false;
		$city_detail = false;
		$join = "";

		extract($conditions);

		$where = $params = [];

		if (isset($users_list)) {
            $users_list = getArrayFromString($users_list);
			$where[] = " u.idu IN (" . implode(',', array_fill(0, count($users_list), '?')) . ")";
            array_push($params, ...$users_list);
		}

		if (isset($group)) {
            $group = getArrayFromString($group);
			$where[] = " u.user_group IN (" . implode(',', array_fill(0, count($group), '?')) . ")";
            array_push($params, ...$group);
		}

		if (isset($group_type)) {
            $group_type = getArrayFromString($group_type);
			$where[] = " gr.gr_type IN (" . implode(',', array_fill(0, count($group_type), '?')) . ")";
            array_push($params, ...$group_type);
		}

		if (isset($status)) {
            $status = getArrayFromString($status);
			$where[] = " u.status IN(" . implode(', ', array_fill(0, count($status), '?')) . ")";
            array_push($params, ...$status);
		}

		if (isset($email_status)) {
            $email_status = getArrayFromString($email_status);
			$where[] = " u.email_status IN(" . implode(',', array_fill(0, count($email_status), '?')) . ")";
            array_push($params, ...$email_status);
		}

		if (isset($accreditation_files)) {
			$where[] = " u.accreditation_files = ? ";
			$params[] = $accreditation_files;
		}

		if (isset($logged)) {
			$where[] = " u.logged = ? ";
			$params[] = $logged;
        }

        if(isset($document_upload_date_from)){
			$where[] = " DATE(u.accreditation_files_upload_date) >= ? ";
			$params[] = formatDate($document_upload_date_from, 'Y-m-d H:i:s');
		}

		if(isset($document_upload_date_to)){
			$where[] = " DATE(u.accreditation_files_upload_date) <= ? ";
			$params[] = $document_upload_date_to;
		}

        if(isset($location_completion)){
            if (!$location_completion) {
                $where[] = "(u.country IS NULL OR u.state IS NULL OR u.city IS NULL OR u.country = 0 OR u.state = 0 OR u.city = 0)";
            } else {
                $where[] = "(u.country IS NOT NULL AND u.state IS NOT NULL AND u.city IS NOT NULL AND u.country != 0 AND u.state != 0 AND u.city != 0)";
            }
		}

		if (isset($fake_user)) {
			$where[] = " u.fake_user = ? ";
			$params[] = $fake_user;
        }

        if (isset($is_model)) {
			$where[] = " u.is_model = ? ";
			$params[] = $is_model;
		}

		if (isset($ip)) {
			$where[] = " u.user_ip = ? ";
			$params[] = $ip;
		}

		if(isset($is_verified)){
			$where[] = " u.is_verified = ? ";
			$params[] = $is_verified;
		}

        if (isset($zoho_id_record)) {
            $where[] = " u.zoho_id_record = ? ";
			$params[] = $zoho_id_record;
        }

		if(isset($user_find_type)){
			$where[] = " u.user_find_type = ? ";
			$params[] = $user_find_type;

			if (isset($user_find_info)) {
				$where[] = " u.user_find_info = ? ";
				$params[] = $user_find_info;
			}
		}

		if (isset($registration_start_date)) {
			$where[] = " DATE(u.registration_date) >= ? ";
			$params[] = formatDate($registration_start_date, 'Y-m-d');
		}

		if (isset($registration_end_date)) {
			$where[] = " DATE(u.registration_date) <= ? ";
			$params[] = formatDate($registration_end_date, 'Y-m-d');
		}

		if (isset($resend_email_from_date)) {
			$where[] = " DATE(u.resend_email_date) >= ? ";
			$params[] = formatDate($resend_email_from_date, 'Y-m-d H:i:s');
		}

		if (isset($resend_email_to_date)) {
			$where[] = " DATE(u.resend_email_date) <= ? ";
			$params[] = formatDate($resend_email_to_date, 'Y-m-d H:i:s');
		}

		if(isset($document_upload_date_to)){
			$where[] = " DATE(u.accreditation_files_upload_date) <= ? ";
			$params[] = $document_upload_date_to;
		}

		if(isset($resend_email_from_date)){
			$where[] = " DATE(u.resend_email_date) >= ? ";
			$params[] = $resend_email_from_date;
		}

		if (isset($activity_start_date)) {
			$where[] = " DATE(u.last_active) >= ? ";
			$params[] = formatDate($activity_start_date, 'Y-m-d');
		}

		if (isset($activity_end_date)) {
			$where[] = " DATE(u.last_active) <= ? ";
			$params[] = formatDate($activity_end_date, 'Y-m-d');
		}

		if ($additional) {
			$join .= " LEFT JOIN port_country pc ON u.country = pc.id ";

			if (isset($id_continent)) {
				$where[] = " pc.id_continent = ? ";
				$params[] = $id_continent;
			}

			if (isset($country)) {
				$where[] = " u.country = ?";
				$params[] = $country;
			}

			if (isset($country_list)) {
                $country_list = getArrayFromString($country_list);
				$where[] = " u.country IN (" . implode(',', array_fill(0, count($country_list), '?')) . ") ";
                array_push($params, ...$country_list);
			}

			if (isset($state)) {
				$where[] = " u.state = ?";
				$params[] = $state;
			}

			if (isset($city)) {
				$where[] = " u.city = ?";
				$params[] = $city;
			}
		}

		if ($company_info) {
			$join .= " LEFT JOIN company_base cb ON u.idu = cb.id_user ";
			$where[] = " (cb.type_company = 'company' OR cb.id_company IS NULL) ";
		}

		if ($city_detail) {
			$join .= " 	LEFT JOIN zips z ON u.city = z.id
						LEFT JOIN states st ON u.state = st.id ";
        }

        if(isset($search_by_company)) {
            $params = [...$params, ...array_fill(0, 4, "%$search_by_company%")];
            $where[] = "
            ((u.idu IN (SELECT id_user FROM company_base WHERE name_company LIKE ? OR legal_name_company LIKE ?)) OR
            (u.idu IN (SELECT id_user FROM company_buyer WHERE company_name LIKE ? )) OR
            (u.idu IN (SELECT id_user FROM orders_shippers WHERE co_name LIKE ? ))) ";
        }

        if(isset($search_by_item)) {
            $params = [...$params, "%$search_by_item%"];
            $where[] = " (u.idu IN (SELECT id_seller FROM items WHERE title LIKE ?)) ";
        }

		if (isset($statistic_filter)) {
			$statistic_where = array();
			if (isset($statistic_filter['items_total']['from'])) {
				$statistic_where[] = " us.items_total >= {$statistic_filter['items_total']['from']} ";
			}

			if (isset($statistic_filter['items_total']['to'])) {
				$statistic_where[] = " us.items_total <= {$statistic_filter['items_total']['to']} ";
			}

			if (!empty($statistic_where)) {
				$join .= " INNER JOIN user_statistic us ON u.idu = us.id_user AND " . implode(" AND ", $statistic_where);
			}
		}

		if (!empty($gmap_bounds)) {
			$lng_operand = $gmap_bounds['nelng'] < $gmap_bounds['swlng']?" OR ":" AND ";
			$where[] = "(u.user_city_lng >= ? {$lng_operand} u.user_city_lng <= ?)";
			$where[] = "u.user_city_lat >= ? AND u.user_city_lat <= ?";
            array_push($params, ...[$gmap_bounds['swlng'], $gmap_bounds['nelng'], $gmap_bounds['swlat'], $gmap_bounds['nelat']]);
		}

		if (isset($keywords)) {
			$words = explode(" ", $keywords);
			foreach ($words as $word) {
				if (strlen($word) > 3) {
					$s_word[] = " (u.fname LIKE ? OR u.lname LIKE ? OR u.email LIKE ? OR CONCAT(phone_code,'',phone) LIKE ?)";
                    array_push($params, ...array_fill(0, 4, '%' . $word . '%'));
				}
			}

			if (!empty($s_word)) {
				$where[] = " (" . implode(" AND ", $s_word) . ")";
			}
		}

		if (isset($on_crm)) {
			$where[] = 'u.zoho_id_record IS ' . ($on_crm ? 'NOT ' : '') . 'NULL';
		}

		if (isset($is_verified)) {
			$where[] = " u.is_verified = ? ";
			$params[] = $is_verified;
		}

        if (!empty($restrictedFrom) || !empty($restrictedTo)) {
            $joinWhere = [" users_blocking_statistics.`type` = 'restriction' "];

            if (!empty($restrictedFrom)) {
                $joinWhere[] = " DATE(users_blocking_statistics.`blocking_date`) >= '{$restrictedFrom}' ";
            }

            if (!empty($restrictedTo)) {
                $joinWhere[] = " DATE(users_blocking_statistics.`blocking_date`) <= '{$restrictedTo}' ";
            }

            $joinConditions = implode('AND', $joinWhere);

			$join .= " RIGHT JOIN (
                SELECT users_blocking_statistics.`id_user`
                FROM users_blocking_statistics
                WHERE {$joinConditions}
                GROUP BY users_blocking_statistics.`id_user`
            ) restrictions ON u.`idu` = restrictions.`id_user` ";
        }

        if (!empty($blockedFrom) || !empty($blockedTo)) {
            $joinWhere = [" users_blocking_statistics.`type` = 'blocking' "];

            if (!empty($blockedFrom)) {
                $joinWhere[] = " DATE(users_blocking_statistics.`blocking_date`) >= '{$blockedFrom}' ";
            }

            if (!empty($blockedTo)) {
                $joinWhere[] = " DATE(users_blocking_statistics.`blocking_date`) <= '{$blockedTo}' ";
            }

            $joinConditions = implode('AND', $joinWhere);

			$join .= " RIGHT JOIN (
                SELECT users_blocking_statistics.`id_user`
                FROM users_blocking_statistics
                WHERE {$joinConditions}
                GROUP BY users_blocking_statistics.`id_user`
            ) blocking ON u.`idu` = blocking.`id_user` ";
        }

		$sql = "SELECT COUNT(*) as counter
				FROM users u
				LEFT JOIN user_groups gr ON u.user_group = gr.idgroup
				$join";

		if(!empty($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
        }

		return $this->db->query_one($sql, $params)['counter'];
    }

    function getReferer($id_user) {
		$this->db->select("user_find_type, user_find_info");
		$this->db->from("{$this->users_table}");
		$this->db->where("idu = ?", $id_user);
		return $this->db->query_one();
    }

	function update_users_lat_lng(){
        $update_users = "UPDATE
                            users, zips
                        SET
                            users.user_city_lat = zips.city_lat,
                            users.user_city_lng = zips.city_lng
                        WHERE users.city = zips.id";

        $this->db->query($update_users);
	}

	function getSellersForList($users_list, $items_info = false){
        $users_list = getArrayFromString($users_list);
		$sql = "SELECT u.idu, CONCAT(u.fname, ' ', u.lname) as user_name, u.logged, u.paid, u.user_photo,
				pc.country as user_country, pcc.country as company_country,
				ug.gr_name, ug.idgroup as user_group,
				cb.name_company, cb.type_company, cb.index_name, cb.id_company , cb.logo_company, cb.rating_count_company, cb.rating_company ";

		if ($items_info) {
			$sql .= ", COUNT(it.id) as active_listing ";
        }

		$sql .="FROM $this->users_table u
				LEFT JOIN port_country pc ON u.country = pc.id
				LEFT JOIN user_groups ug ON u.user_group = ug.idgroup
				LEFT JOIN company_base cb ON cb.id_user = u.idu AND cb.type_company = 'company'
				LEFT JOIN port_country pcc ON cb.id_country = pcc.id ";

		if ($items_info) {
			$sql .= " INNER JOIN items it ON u.idu = it.id_seller ";
        }

		$sql .= " WHERE u.idu IN (" . implode(',', array_fill(0, count($users_list), '?')) . ") GROUP BY u.idu";
		return  $this->db->query_all($sql, $users_list);
	}

	function existStaffUser($id_user, $id_company){
		$sql = "SELECT COUNT(*) as exist
				FROM users u
				INNER JOIN company_users cu ON u.idu = cu.id_user
				WHERE u.idu = ? AND u.user_type = 'users_staff' AND cu.id_company = ? AND cu.company_type = 'company'
				LIMIT 1";
		return $this->db->query_one($sql, array($id_user,$id_company))['exist'];
	}

	function updateUserResetCode($id, $code){
		$this->db->where('idu', $id);
		return $this->db->update($this->users_table, array('activation_code' => $code));
    }
    function updateUserResetCodeByPrincipal($id_principal, $code){
		$this->db->where('id_principal', $id_principal);
        $this->db->where('status !=', 'deleted');

		return $this->db->update($this->users_table, array('activation_code' => $code));
	}

	function updateUserEmailStatus($email, $status){
		$this->db->where('email', $email);
		return $this->db->update(
			$this->users_table,
			array(
				'email_status' => $status
			)
		);
	}

	function updateUserByIdPrincipal($id_principal, $data)
	{
        $this->db->where('id_principal', $id_principal);
        $this->db->where('status !=', 'deleted');

		return $this->db->update($this->users_table, $data);
	}

	function update_users_status($data){
        $users_ids = getArrayFromString($data['id_users_list']);

        $this->db->in('idu', $users_ids);
        $updateResult = $this->db->update($this->users_table, ['status' => $data['status']]);

        if ($updateResult) {
            /** @var Crm_Model $crmModel */
            $crmModel = model(Crm_Model::class);

            foreach ($users_ids as $user_id) {
                $crmModel->create_or_update_record($user_id);
            }
        }

		return $updateResult;
	}

	function update_users_sent_systmess_date($users_ids = array()){
		if (empty($users_ids)) {
			return false;
		}

        $users_ids = getArrayFromString($users_ids);

        $this->db->in("idu", $users_ids);
		return $this->db->update($this->users_table, array('sent_systmess_date' => date('Y-m-d H:i:s')));
	}

	function unsubscribe_users($id_principal)
	{
        $this->db->where('id_principal', $id_principal);
        $this->db->where('status !=', 'deleted');

		return $this->db->update(
			$this->users_table,
			array(
				'notify_email' 		 => 0,
				'subscription_email' => 0
			)
		);
	}

	function getUserCountry($idu){
		$sql = "SELECT pc.country as country_name
				FROM port_country pc
				LEFT JOIN users u ON u.country = pc.id
				WHERE u.idu = ?";

		return $this->db->query_one($sql, array($idu));
	}

	//users notices
	function set_notice($idu, $notice){
		$sql = "UPDATE {$this->users_table}
				SET notice = CONCAT_WS(',', ?, notice)
				WHERE idu = ?";
		return $this->db->query($sql, array(json_encode($notice), $idu));
	}

	function get_notice($idu, $in_array = TRUE){
		$sql = "SELECT notice
				FROM $this->users_table
				WHERE idu = ?";
		$rez = $this->db->query_one($sql, array($idu));

		if($in_array && !empty($rez['notice'])){
			$last_char = substr($rez['notice'], -1);
			if($last_char == ','){
				$rez['notice'] = $rez['notice'];
				$rez['notice'] = substr($rez['notice'], 0, strlen($rez['notice'])-1);
			}
			$json = "[" .$rez['notice']. "]";
			$notices = json_decode($json, true);
			$notices = array_filter($notices);
		}else
			$notices = $rez['notice'];

		return $notices;
	}

	function delete_staff_user($id_user) {
		$this->db->where('idu', $id_user);
		$this->db->where('user_type', 'users_staff');
		$this->db->delete('users');
		return $this->db->numRows();
	}

	function exist_user($user_id){
        $this->db->select('COUNT(*) as counter');
        $this->db->where('idu', $user_id);
        $this->db->limit(1);

        return $this->db->get_one($this->users_table)['counter'];
	}

	function exist_user_by_clean_session_token($token){
        $this->db->select('COUNT(*) as counter');
        $this->db->where('clean_session_token', $token);
        $this->db->limit(1);

        return $this->db->get_one($this->users_table)['counter'];
	}

	function exist_user_by_email($email){
        $this->db->select('COUNT(*) as counter');
        $this->db->where('email', $email);
        $this->db->limit(1);

        return $this->db->get_one($this->users_table)['counter'];
    }

    function get_report_users_yoy(array $params = array()){
        $select = "{$this->users_table}.country, YEAR({$this->users_table}.registration_date) as year_of_registration, COUNT(*) as count_registered_users";
        $group_by = "{$this->users_table}.country, YEAR({$this->users_table}.registration_date)";

        extract($params);

        if (isset($select)) {
            $this->db->select($select);
        }
        $this->db->from($this->users_table);
        if (isset($join_with_country)) {
            $this->db->join($this->country_table, "{$this->users_table}.country = {$this->country_table}.id", 'left');
        }

        if (isset($only_focus_country)) {
            $this->db->where("{$this->country_table}.is_focus_country", 1);
        }

        if (isset($group_by)) {
            $this->db->groupby(is_array($group_by) ? implode(', ', $group_by) : $group_by);
        }

        $this->db->where("{$this->users_table}.fake_user", 0);
        $this->db->where_raw("YEAR({$this->users_table}.registration_date) >= 2014");
        $this->db->in("{$this->users_table}.user_group", array(1, 2, 3, 5, 6, 31));

        return $this->db->get();
    }

    function get_report_users_qoq(array $params = array()){
        $select = "{$this->users_table}.country, COUNT(*) as count_registered_users, (MONTH({$this->users_table}.registration_date) + 2) DIV 3 as quarter, YEAR({$this->users_table}.registration_date) as registration_year";
        $group_by = "{$this->users_table}.country, YEAR({$this->users_table}.registration_date), (MONTH({$this->users_table}.registration_date) + 2) DIV 3";

        extract($params);

        if (isset($select)) {
            $this->db->select($select);
        }

        $this->db->from($this->users_table);
        if (isset($join_with_country)) {
            $this->db->join($this->country_table, "{$this->users_table}.country = {$this->country_table}.id", 'left');
        }

        if (isset($only_focus_country)) {
            $this->db->where("{$this->country_table}.is_focus_country", 1);
        }

        if (isset($group_by)) {
            $this->db->groupby(is_array($group_by) ? implode(', ', $group_by) : $group_by);
        }

        $this->db->where("{$this->users_table}.fake_user", 0);
        $this->db->where_raw("YEAR({$this->users_table}.registration_date) >= 2014");
        $this->db->in("{$this->users_table}.user_group", array(1, 2, 3, 5, 6, 31));

        return $this->db->get();
    }

    function get_report_users_mom(array $params = array()){
        $select = "{$this->users_table}.country, COUNT(*) as count_registered_users, MONTH({$this->users_table}.registration_date) as month, YEAR({$this->users_table}.registration_date) as registration_year";
        $group_by = "{$this->users_table}.country, YEAR({$this->users_table}.registration_date), MONTH({$this->users_table}.registration_date)";

        extract($params);

        if (isset($select)) {
            $this->db->select($select);
        }

        $this->db->from($this->users_table);
        if (isset($join_with_country)) {
            $this->db->join($this->country_table, "{$this->users_table}.country = {$this->country_table}.id", 'left');
        }

        if (isset($only_focus_country)) {
            $this->db->where("{$this->country_table}.is_focus_country", 1);
        }

        if (isset($group_by)) {
            $this->db->groupby(is_array($group_by) ? implode(', ', $group_by) : $group_by);
        }

        $this->db->where("{$this->users_table}.fake_user", 0);
        $this->db->where_raw("YEAR({$this->users_table}.registration_date) >= 2014");
        $this->db->in("{$this->users_table}.user_group", array(1, 2, 3, 5, 6, 31));

        return $this->db->get();
    }

    function get_report_users_mom_by_user_types(array $params = array()){
        $select = "{$this->users_table}.country, YEAR({$this->users_table}.registration_date) as registration_year, (MONTH({$this->users_table}.registration_date) + 2) DIV 3 as quarter, MONTH({$this->users_table}.registration_date) as month, {$this->users_table}.user_group, COUNT(*) as count_registered_users";
        $group_by = "{$this->users_table}.country, YEAR({$this->users_table}.registration_date), MONTH({$this->users_table}.registration_date), {$this->users_table}.user_group";

        extract($params);

        if (isset($select)) {
            $this->db->select($select);
        }

        $this->db->from($this->users_table);
        if (isset($join_with_country)) {
            $this->db->join($this->country_table, "{$this->users_table}.country = {$this->country_table}.id", 'left');
        }

        if (isset($only_focus_country)) {
            $this->db->where("{$this->country_table}.is_focus_country", 1);
        }

        if (isset($group_by)) {
            $this->db->groupby(is_array($group_by) ? implode(', ', $group_by) : $group_by);
        }

        $this->db->where("{$this->users_table}.fake_user", 0);
        $this->db->where_raw("YEAR({$this->users_table}.registration_date) >= 2014");
        $this->db->in("{$this->users_table}.user_group", array(1, 2, 3, 5, 6, 31));

        return $this->db->get();
    }

    function get_report_users_yoy_by_user_types(array $params = array()){
        $select = "{$this->users_table}.country, {$this->users_table}.user_group, YEAR({$this->users_table}.registration_date) as registration_year, COUNT(*) as count_registered_users";
        $group_by = "{$this->users_table}.country, YEAR({$this->users_table}.registration_date), {$this->users_table}.user_group";

        extract($params);

        if (isset($select)) {
            $this->db->select($select);
        }

        $this->db->from($this->users_table);
        if (isset($join_with_country)) {
            $this->db->join($this->country_table, "{$this->users_table}.country = {$this->country_table}.id", 'left');
        }

        if (isset($only_focus_country)) {
            $this->db->where("{$this->country_table}.is_focus_country", 1);
        }

        if (isset($group_by)) {
            $this->db->groupby(is_array($group_by) ? implode(', ', $group_by) : $group_by);
        }

        $this->db->where("{$this->users_table}.fake_user", 0);
        $this->db->where_raw("YEAR({$this->users_table}.registration_date) >= 2014");
        $this->db->in("{$this->users_table}.user_group", array(1, 2, 3, 5, 6, 31));

        return $this->db->get();
    }

    /**
     * @param int $conditions['modelUser']
     * @param int $conditions['fakeUser']
     * @param array $conditions['userGroups']
     *
     * @return array
     */
    function countUsersGroupedByUserGroup(array $conditions = []):array
    {
        $queryResult = $this->findRecords(null, $this->get_users_table(), null, [
           'columns'    => [
               'user_group',
               'COUNT(*) as countUsers'
           ],
           'group'      => [
               'user_group'
           ],
           'conditions' => $conditions ?: null
        ]);

        return array_column($queryResult, null, 'user_group');
    }

    /**
     * @param int $conditions['modelUser']
     * @param int $conditions['fakeUser']
     * @param array $conditions['userGroups']
     * @param array $conditions['userStatuses']
     *
     * @return array
     */
    function countUsersGroupedByUserGroupAndStatus(array $conditions = []):array
    {
        $queryResult = $this->findRecords(null, $this->get_users_table(), null, [
           'columns'    => [
               'user_group',
               'status',
               'COUNT(*) as countUsers'
           ],
           'group'      => [
               'user_group',
               'status'
           ],
           'conditions' => $conditions ?: null
        ]);

        return arrayByKey($queryResult, 'user_group', true);
    }

	/* user change password and email*/
	function set_user_info_change($data){
		return $this->db->insert($this->user_info_change_table, $data);
	}

	function get_user_info_change_by_user($id_user, $type){
        $this->db->where('id_user', $id_user);
        $this->db->where('type', $type);
        $this->db->limit(1);

        return $this->db->get_one($this->user_info_change_table);
	}

	function get_user_info_change($confirm, $type){
        $this->db->where('confirmation_code', $confirm);
        $this->db->where('type', $type);
        $this->db->limit(1);

		return $this->db->get_one($this->user_info_change_table);
	}

	function delete_user_info_change($id_user, $type) {
		$this->db->where('id_user = ? AND type = ?', array($id_user, $type));
		return $this->db->delete($this->user_info_change_table);
	}

	//forgot password
	function check_reset_code($code){
		$sql = "SELECT 	u.*, CONCAT_WS(' ',u.fname, u.lname) as user_name,
						ug.gr_name, ug.gr_type, ug.stamp_pic, u.id_principal
				FROM users u
				LEFT JOIN user_groups ug ON u.user_group = ug.idgroup
				WHERE activation_code = ?";
		return $this->db->query_one($sql, array($code));
	}

	//logout
	function old_session_users(){
		$this->db->select('idu, ssid');
		$this->db->from('users');
		$this->db->where('FLOOR(( TIME_TO_SEC( NOW() ) - TIME_TO_SEC( last_active )	) / 60 ) > ?', $this->session_expire_min);
		$this->db->where('logged = ?', '1');

		return $this->db->query_all();
	}

	function logout_old_session_users($id_user = false){
		$this->db->where('logged = ?', '1');

		if($id_user === false){
			$this->db->where('FLOOR(( TIME_TO_SEC( NOW() ) - TIME_TO_SEC( last_active )	) / 60 ) > ?', $this->session_expire_min);
		} else{
			if(!is_array($id_user)){
				$id_user = explode(',', $id_user);
				$id_user = array_map('intval', $id_user);
				$id_user = array_filter($id_user);
			}

			if(empty($id_user)){
				return false;
			}

			$this->db->in('idu', $id_user);
		}

		return $this->db->update('users',array('logged' => '0'));
	}

	function logoutOldSession(){
		$this->db->where('FLOOR(( TIME_TO_SEC( NOW() ) - TIME_TO_SEC( last_active )	) / 60 ) > ?', $this->$this->session_expire_min);
		$this->db->where('logged = ?', '1');
		return $this->db->update('users',array('logged' => '0'));
	}

	//force logout
	function force_logout(){
		return $this->db->update('users',array('logged' => 0));
	}

	//login
	function getLoginInfo_md5($id_user){
		$sql = "SELECT 	u.*, CONCAT_WS(' ', u.fname, u.lname) as user_name,
						gr.gr_name, gr.gr_type, gr.gr_lang_restriction_enabled
				FROM users u
				LEFT JOIN user_groups gr ON u.user_group = gr.idgroup
				WHERE MD5(idu) = ?";
		return $this->db->query_one($sql,  array($id_user));
	}

	function getLoginInfoById($id_user){
		$sql = "SELECT 	u.*, CONCAT_WS(' ', u.fname, u.lname) as user_name,
						gr.gr_name, gr.gr_type, gr.gr_lang_restriction_enabled
				FROM users u
				LEFT JOIN user_groups gr ON u.user_group = gr.idgroup
				WHERE idu = ? ";

		return $this->db->query_one($sql,  array($id_user));
	}

	function getLoginInfoByIdPrincipal($id_principal){
		$sql = "SELECT 	u.*, CONCAT_WS(' ', u.fname, u.lname) as user_name,
						gr.gr_name, gr.gr_type, gr.gr_lang_restriction_enabled
				FROM users u
				LEFT JOIN user_groups gr ON u.user_group = gr.idgroup
				WHERE id_principal = ? AND u.status != 'deleted'";

		return $this->db->query_one($sql,  array($id_principal));
	}

	function get_company_staff_users($conditions = array()){
		$order_by = " registration_date DESC ";
		$per_p = 10;
		$page = 1;

		extract($conditions);

		$where = $params = [];

		if (isset($users_list)) {
            $users_list = getArrayFromString($users_list);
			$where[] = " u.idu IN (" . implode(',', array_fill(0, count($users_list), '?')) . ")";
            array_push($params, ...$users_list);
        }

		if(isset($group)){
			$where[] = " sug.id_group = (?)";
            $params[] = $group;
		}

		if (isset($status)) {
            $status = getArrayFromString($status);
			$where[] = " u.status IN(" . implode(', ', array_fill(0, count($status), '?')) . ")";
            array_push($params, ...$status);
		}

		if(isset($user_type)) {
            $user_type = getArrayFromString($user_type);
			$where[] = " u.user_type IN(" . implode(',', array_fill(0, count($user_type), '?')) . ")";
            array_push($params, ...$user_type);
        }


		if(isset($company)){
			$where[] = " u.id_company = ? ";
			$params[] = $company;
		}

		if(isset($logged)){
			$where[] = " u.logged = ? ";
			$params[] = $logged;
		}

		if(isset($ip)){
			$where[] = " u.user_ip = ? ";
			$params[] = $ip;
		}

		if(isset($registration_start_date)){
			$where[] = " u.registration_date >= ? ";
			$params[] = formatDate($registration_start_date, 'Y-m-d');
		}

		if(isset($registration_end_date)){
			$where[] = " u.registration_date <= ? ";
			$params[] = formatDate($registration_end_date, 'Y-m-d');
		}

		if(isset($activity_start_date)){
			$where[] = " u.last_active >= ? ";
			$params[] = formatDate($activity_start_date, 'Y-m-d');
		}

		if(isset($activity_end_date)){
			$where[] = " u.last_active <= ? ";
			$params[] = formatDate($activity_end_date, 'Y-m-d');
		}

		if(isset($keywords)){
			$words = explode(" ", $keywords);
			foreach($words as $word){
				if (strlen($word) > 3) {
					$s_word[] = " (u.fname LIKE ? OR u.lname LIKE ? OR u.email LIKE ?)";
                    array_push($params, ...array_fill(0, 3, '%' . $word . '%'));
                }
			}

			if (!empty($s_word)) {
				$where[] = " (" . implode(" AND ", $s_word) . ")";
            }
		}

		$sql = "SELECT u.*, sg.name_sgroup, sg.id_sgroup
				FROM users u
				LEFT JOIN company_staff_user_group sug ON u.idu = sug.id_user
				LEFT JOIN company_staff_groups sg ON sug.id_group = sg.id_sgroup ";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
        }

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(' , ', $multi_order_by);
		}

		$sql .= " ORDER BY " . $order_by;

		if(!isset($users_list)){

			if(!isset($count))
				$count = $this->count_users($conditions);

			/* block for count pagination */
			$pages = ceil($count/$per_p);

			if($page > $pages)
				$page = $pages;

			if(!isset($start)){
				$start = ($page-1)*$per_p;

				if($start < 0)
					$start = 0;
			}

			if($start >= 0)
				$sql .=  " LIMIT " . $start;

			if($per_p > 0)
				$sql .= ", " . $per_p;
		}

		return $this->db->query_all($sql, $params);
	}

	function get_user_ssid($id){
		return $this->db->query_one("SELECT ssid FROM users WHERE idu= ?", array($id))['ssid'];
	}

	function get_companies_by_users($users){
        $users = getArrayFromString($users);

        $this->db->select('DISTINCT(id_company)');
        $this->db->where_raw('id_company <> 0');
        $this->db->in('idu', $users);

        return $this->db->get($this->users_table);
	}

	function get_users_of_company(array $id_companies, array $user_exceptions = array(), array $users_in = array()){
        if (empty($id_companies)) {
            return [];
        }

        $id_companies = getArrayFromString($id_companies);

        $this->db->select("idu, CONCAT(fname, ' ', lname) as user_name");
        $this->db->in('id_company', $id_companies);

        if(!empty($user_exceptions)){
            $user_exceptions = getArrayFromString($user_exceptions);
            $this->db->where_raw('idu NOT IN (' . implode(',', array_fill(0, count($user_exceptions), '?')) . ')', $user_exceptions);
		}

        if(!empty($users_in)){
            $users_in = getArrayFromString($users_in);
            $this->db->in('idu', $users_in);
		}

        return $this->db->get($this->users_table);
	}

	public function get_users_for_message($conditions = array()){
		$this->db->select("
			u.idu as user_id, CONCAT(u.fname,' ',u.lname) as user_name, u.user_group, u.user_photo, u.logged,
			ug.gr_name, ug.gr_type,
			ifnull(cb.name_company, os.co_name) as company_name,
			IFNULL(cb.id_company, os.id) as id_company,
			IFNULL(cb.index_name, NULL) as index_name
		");
		$this->db->from("{$this->users_table} u");
		$this->db->join("user_groups ug", "u.user_group = ug.idgroup", "inner");
		$this->db->join("company_base cb", "cb.id_user = u.idu", "left");
		$this->db->join("orders_shippers os", "os.id_user = u.idu", "left");
		$this->db->where("u.status = ?", "active");
		$this->db->where("u.user_page_blocked = ?", 0);

		extract($conditions);

		if(isset($country)){
			$this->db->where("u.country = ?", $country);

			if(isset($state)){
				$this->db->where("u.state = ?", $state);
			}

			if(isset($city)){
				$this->db->where("u.city = ?", $city);
			}
		}

		if(!empty($not_user_id)){
            $not_user_id = getArrayFromString($not_user_id);

			$this->db->where_raw(" u.idu NOT IN (" . implode(',', array_fill(0, count($not_user_id), '?')) . ") ", $not_user_id);
		}

		if(!empty($user_groups)){
			$user_groups = getArrayFromString($user_groups);
			$this->db->in("u.user_group", $user_groups);
		}

		if(!empty($not_user_groups)){
			$not_user_groups = getArrayFromString($not_user_groups);

			$this->db->where_raw("u.user_group NOT IN (" . implode(',', array_fill(0, count($not_user_groups), '?')) . ") ", $not_user_groups);
		}

		if(!empty($keywords)){
			if(str_word_count_utf8($keywords) > 1){
				$this->db->where_raw("MATCH (u.fname, u.lname, u.email, cb.name_company) AGAINST (?)", $keywords);
			} else{
				$this->db->where_raw("(u.fname LIKE ? || u.lname LIKE ? || u.email LIKE ? || cb.name_company LIKE ?)", array_fill(0, 4, "%{$keywords}%"));
			}
		}

		$this->db->groupby("u.idu");

		if(isset($order_by)){
			$this->db->orderby($order_by);
		}

		return $this->db->query_all();
	}

	public function clear_users($days){
        $this->db->select('GROUP_CONCAT(idu SEPARATOR ',') as users_list');
        $this->db->where('status', 'new');
        $this->db->where_raw('DATEDIFF(NOW(), last_active) > ?', $days);

        $usersList = $this->db->get_one($this->users_table);

		if (!empty($usersList['users_list'])) {
            $usersIds = getArrayFromString($usersList['users_list']);

            $this->db->in('id_user', $usersIds);
            $this->db->delete('company_base');

            $this->db->in('id_user', $usersIds);
            $this->db->delete('orders_shippers');
		}

		return;
	}

	public function clear_user_info_changes(){
        $this->db->where_raw('DATEDIFF(NOW(), date_change) > 10');
        return $this->db->delete('user_info_change');
	}

	public function get_user_location($id_user){
		$sql = 'SELECT u.city as id_city, pc.city as name_city, u.state as id_state, s.state as name_state, u.country as id_country, pcy.country as name_country
				FROM users u
				LEFT JOIN zips pc ON u.city = pc.id
				LEFT JOIN states s ON u.state = s.id
				LEFT JOIN port_country pcy ON u.country = pcy.id
				WHERE u.idu = ? ';
		return $this->db->query_one($sql, array($id_user));
	}

	/* CALLING STATUSES ACTIONS */
	function insert_call_status($data = array()){
		if(empty($data)){
			return false;
		}

		return $this->db->insert('users_calling_statuses', $data);
    }

	function update_call_status($id_status, $data = array()){
		if(empty($data)){
			return false;
		}

		$this->db->where('id_status', $id_status);
		return $this->db->update('users_calling_statuses', $data);
    }

	function delete_call_status($id_status){
		$this->db->where('id_status', $id_status);
		return $this->db->delete('users_calling_statuses');
    }

	function get_calling_status($id_status){
		$this->db->select('*');
    	$this->db->from('users_calling_statuses');
		$this->db->where('id_status', $id_status);
		return $this->db->query_one();
    }

	function is_used_calling_status($id_status){
		$this->db->select('COUNT(*) as counter');
    	$this->db->from('users');
		$this->db->where('calling_status', $id_status);

        return $this->db->query_one()['counter'];
    }

	function get_calling_statuses($conditions = array()){
        $order_by = " id_status ASC ";

		extract($conditions);

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(' , ', $multi_order_by);
		}

		$sql = "SELECT * FROM users_calling_statuses ORDER BY {$order_by}";

		return $this->db->query_all($sql);
    }

	function count_calling_statuses($conditions = array()){
        extract($conditions);

        $this->db->select('COUNT(*) as counter');
        $this->db->limit(1);

        return $this->db->get_one('users_calling_statuses')['counter'];
	}

	/* CALLING STATUSES ACTIONS END */

	/* NOTIFICATION MESSAGES ACTIONS */
	function insert_notification_message($data = array()){
		return empty($data) ? false : $this->db->insert('users_notification_messages', $data);
    }

	function update_notification_message($id_message, $data = array()){
		if(empty($data)){
			return false;
		}

		$this->db->where('id_message', $id_message);
		return $this->db->update('users_notification_messages', $data);
    }

    function delete_notification_message($id_message) {
        return $this->removeRecords(null, $this->get_notifications_table(), null, [
            'conditions' => [
                'notification_message' => $id_message,
            ]
        ]);
    }

	function get_notification_message($id_message){
		$this->db->select('*');
    	$this->db->from('users_notification_messages');
		$this->db->where('id_message', $id_message);
		return $this->db->query_one();
    }

	function get_notification_messages($conditions = array()){
        $order_by = " id_message ASC ";

        extract($conditions);

		$where = $params = [];

		if (isset($sort_by)) {
			foreach ($sort_by as $sort_item) {
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0] . ' ' . $sort_item[1];
			}

			$order_by = implode(' , ', $multi_order_by);
		}

		if (isset($message_module)) {
			$where[] = " message_module = ? ";
			$params[] = $message_module;
		}

		$sql = "SELECT * FROM `users_notification_messages`";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
		}

        $sql .= " ORDER BY {$order_by}";

        if (isset($start) && isset($per_p)) {
            $start = (int) $start;
            $per_p = (int) $per_p;

            $sql .= " LIMIT $start, $per_p";
		}

		return $this->db->query_all($sql, $params);
    }

	function count_notification_messages($conditions = array()){
        extract($conditions);

        $this->db->select('COUNT(*) as counter');
        $this->db->limit(1);

        if (isset($message_module)) {
            $this->db->where('message_module', $message_module);
		}

        return $this->db->get_one('users_notification_messages')['counter'];
	}

	/* NOTIFICATION MESSAGES ACTIONS END */

	function get_users_expired_paid($conditions = array()){
		$limit = 50;

		extract($conditions);

		$where = $params = [];

		if(isset($days_before_expire)){
			$where[] = " DATEDIFF(paid_until,CURDATE()) < ? ";
			$params[] = $days_before_expire;
		}

		$sql = "SELECT 	u.idu, u.paid_until, u.fname, u.lname, u.user_group, u.ssid,
						ug.gr_name, ug.gr_type
				FROM $this->users_table u
				LEFT JOIN user_groups ug ON u.user_group = ug.idgroup
				WHERE paid_until != '0000-00-00' AND user_page_blocked = 0 ";

		if(!empty($where)){
			$sql .= " AND " . implode(" AND ", $where);
		}

		$sql .= " LIMIT {$limit}";
		return $this->db->query_all($sql, $params);
	}

	function get_fake_users(){
		$sql = "SELECT 	u.idu, u.paid_until, u.fname, u.lname, u.user_group,
						ug.gr_name, ug.gr_type
				FROM $this->users_table u
				LEFT JOIN user_groups ug ON u.user_group = ug.idgroup
				WHERE u.fake_user = 1 and u.user_group in (1,2,3,5,6,31)";

		return $this->db->query_all($sql);
	}

	/**
     * @author Bendiucov Tatiana
     * @deprecated [06.01.2022]
     * @see Buyer_Item_Categories_Stats_Model
     */
	function set_user_relation_industries($data = array()) {
		if(empty($data)){
			return false;
		}

		$this->db->insert_batch("users_relation_industries", $data);
		return $this->db->last_insert_id();
	}

    /**
     * @author Bendiucov Tatiana
     * @deprecated [06.01.2022]
     * @see Buyer_Item_Categories_Stats_Model @getUserRelationIndustries
     */
	function get_user_relation_industries($id_user = 0, $limit = null) {
		$this->db->select("uri.*, ic.name");
		$this->db->from("users_relation_industries uri");
		$this->db->join("item_category ic", "uri.id_industry = ic.category_id", "left");
		$this->db->where('id_user = ?', $id_user);
		$this->db->orderby('uri.order_relation ASC');

		if (null !== $limit) {
			$this->db->limit((int) $limit);
		}

		return $this->db->get();
	}

    /**
     * @author Bendiucov Tatiana
     * @deprecated [06.01.2022]
     * @see Buyer_Item_Categories_Stats_Model
     */
	function delete_user_relation_industries($id_user = 0) {
		$this->db->where('id_user = ?', $id_user);
		$this->db->delete("users_relation_industries");
	}

    /**
     * @author Bendiucov Tatiana
     * @deprecated [06.01.2022]
     * @see Buyer_Item_Categories_Stats_Model
     */
	function get_industries_of_iterest($conditions = array()){
		$this->db->select("it.category_id, it.name,  COUNT(uri.id_user) as users_interested_in");
		$this->db->from("item_category it");
		$this->db->join("users_relation_industries uri", "it.category_id = uri.id_industry", "inner");

		$this->db->where("it.parent = ?", 0);

		extract($conditions);

		if(isset($id_user)){
			$this->db->where("uri.id_user = ?", $id_user);
		}

		if(isset($id_industry)){
			$this->db->in("uri.id_industry", $id_industry);
		}

		$this->db->groupby("it.category_id");
		$this->db->orderby("users_interested_in DESC");

		return $this->db->get();
	}

    /**
     * @author Bendiucov Tatiana
     * @deprecated [06.01.2022]
     * @see Buyer_Item_Categories_Stats_Model
     */
	function get_users_relation_industries($conditions = array()) {
		extract($conditions);

        $this->db->from("users_relation_industries");
		if(isset($id_user)){
			$this->db->where('id_user = ?', $id_user);
		}

		if(isset($industries)){
			if(!is_array($industries)){
				$industries = explode(',', $industries);
				$industries = array_map('intval', $industries);
			}

			if(empty($industries)){
				$industries = array(0);
			}

			$this->db->in('id_industry', $industries);
		}

		return $this->db->get();
	}

    /**
     * Gets the list of users that have all provided rights
     */
    public function findUsersWithAllRights(array $rights = []): array
    {
        if (empty($rights)) {
            return [];
        }

        // Make the general query
        $query = $this->createQueryBuilder();
        // Make rights subquery
        $rightsSubQuery = $this->createQueryBuilder();
        $rightsSubQuery
            ->select('idgroup')
            ->from($this->rights_table)
            ->leftJoin($this->rights_table, 'usergroup_rights', null, "{$this->rights_table}.idright = usergroup_rights.idright")
            ->andWhere(
                $rightsSubQuery->expr()->and(
                    ...array_map(
                        fn (string $right) => $rightsSubQuery->expr()->eq('r_alias', $query->createPositionalParameter($right)),
                        $rights
                    )
                )
            )
        ;

        // Build general query
        $query
            ->select('*')
            ->from($this->get_users_table())
            ->where(
                $query->expr()->or(
                    $query->expr()->in('user_group', $rightsSubQuery->getSQL())
                )
            )
        ;
        // Execute query and get the statement
        /** @var \Doctrine\DBAL\Statement $statement */
        $statement = $query->execute();

        return $statement->fetchAllAssociative();
    }

    /**
     * Gets the list of users that have all provided additional rights.
     */
    public function findUsersWithAllAdditionalRights(array $rights = [], bool $onlyPaidRules = false): array
    {
        if (empty($rights)) {
            return [];
        }

        // Make the general query
        $query = $this->createQueryBuilder();
        // Make additional rigths subquery
        $additionalRightsSubQuery = $this->createQueryBuilder();
        $additionalRightsSubQuery
            ->select("{$this->additional_rights_table}.id_user")
            ->from($this->rights_table)
            ->leftJoin($this->rights_table, $this->additional_rights_table, null, "{$this->rights_table}.idright = {$this->additional_rights_table}.id_right")
            ->andWhere(
                $additionalRightsSubQuery->expr()->or(
                    ...array_map(
                        fn (string $right) => $additionalRightsSubQuery->expr()->eq('r_alias', $query->createPositionalParameter($right)),
                        $rights
                    )
                )
            )
        ;
        if ($onlyPaidRules) {
            $additionalRightsSubQuery->andWhere('right_paid', $query->createPositionalParameter(1, ParameterType::INTEGER));
        }
        // Build general query
        $query
            ->select('*')
            ->from($this->get_users_table())
            ->where(
                $query->expr()->or(
                    $query->expr()->in('idu', $additionalRightsSubQuery->getSQL())
                )
            )
        ;
        // Execute query and get the statement
        /** @var \Doctrine\DBAL\Statement $statement */
        $statement = $query->execute();

        return $statement->fetchAllAssociative();
    }

    /** @deprecated */
	public function get_users_by_right($right = 'administrate_orders')
	{
		$sql = "SELECT u.*
			FROM users u
			INNER JOIN usergroup_rights ugr ON u.user_group = ugr.idgroup
			INNER JOIN rights r ON r.idright = ugr.idright AND r.r_alias = ?";
		return $this->db->query_all($sql, [$right]);
	}

    /** @deprecated */
	public function get_users_by_additional_right($right = null)
	{
		if (null === $right) {
			return array();
		}

		$this->db->select("USERS.*");
		$this->db->from("`{$this->users_table}` as `USERS`");
		$this->db->where_raw(
			"`USERS`.`idu` IN (
				SELECT `ADDITIONAL_RIGHTS`.`id_user`
				FROM `{$this->additional_rights_table}` AS `ADDITIONAL_RIGHTS`
				LEFT JOIN `{$this->rights_table}` AS `RIGHTS` ON `ADDITIONAL_RIGHTS`.`id_right` = `RIGHTS`.`idright`
				WHERE `RIGHTS`.`r_alias` = ?
			)",
			$right
		);

		return array_filter((array) $this->db->query_all());
	}

	public function get_users_with_old_accreditations_documents($limit = null)
	{
		$this->db->select('*');
		$this->db->from("`{$this->users_table}` AS `USERS`");
		$this->db->where("`USERS`.accreditation_docs != ?", "");
		$this->db->where("`USERS`.accreditation_docs_exported = ?", 0);
		if (null !== $limit) {
			$this->db->limit((int) $limit);
		}

		return $this->db->query_all();
	}

	public function get_user_by_accreditation_token($token)
	{
		$this->db->select('`USERS`.*, `GROUPS`.`gr_name`, `GROUPS`.`gr_type`, `GROUPS`.`stamp_pic`');
		$this->db->from("`{$this->users_table}` AS `USERS`");
		$this->db->join("`{$this->users_groups_table}` AS `GROUPS`", "`USERS`.`user_group` = `GROUPS`.`idgroup`", 'left');
		$this->db->where("`USERS`.`accreditation_token` = ?", (string) $token);

		return !empty($user = $this->db->query_one()) ? $user : null;
	}

    function get_user_by_confirm_token($token){
        $sql = "SELECT
                    u.*,
                    ug.gr_name,
                    ug.gr_type,
                    ug.stamp_pic
                FROM {$this->users_table} u
                LEFT JOIN user_groups ug
                    ON ug.idgroup = u.user_group
                WHERE u.confirmation_token = ?";

		return $this->db->query_one($sql, array($token));
	}

	function get_users_by_confirm_token($token)
	{
		$this->db->select($this->users_table . '.*, ug.gr_name, ug.gr_type, ug.stamp_pic');
		$this->db->from($this->users_table);
		$this->db->join("{$this->users_groups_table} AS ug", "{$this->users_table}.user_group = ug.idgroup", 'left');
		$this->db->where("{$this->users_table}.confirmation_token", $token);

		return $this->db->query_all();
	}

	public function get_all_users($columns = null)
	{
		if (is_string($columns)){
			$columns = array_filter(preg_split("/\,(\s*)?/", $columns));
		}
		if (is_array($columns) && !empty($columns = array_map(function ($column) { return "`{$column}`"; }, array_filter($columns)))) {
			$this->db->select(implode(', ', $columns));
        }
        $this->db->from($this->users_table);

		return $this->db->get();
	}

	public function get_not_verified_emails()
	{
		$this->db->select('email');
		$this->db->from($this->users_table);
		$this->db->where_raw('email_status IS NULL');

		$emails = $this->db->query_all();

		return empty($emails) ? [] : array_column($emails, 'email');
	}

	public function get_users_with_bad_emails($check_emails)
	{
		$check_emails_cond = '"' . implode ('","', $check_emails) . '"';

		$this->db->select('email');
		$this->db->from($this->users_table);
		$this->db->where('email_status', 'Bad');
		$this->db->where_raw("email IN ({$check_emails_cond})");

		$emails = $this->db->query_all();

		return empty($emails) ? [] : array_column($emails, 'email');
	}

	public function get_emails_status_labels()
	{
		return $this->emails_labels;
	}

    /**
     * @author Bendiucov Tatiana
     * @deprecated [06.01.2022]
     * @see getReportBuyersPerIndustry in Buyer_Item_Categories_Stats_Model
     *
     * @param array $conditions
     *
     * @return array
     */
    function get_report_buyers_per_industry(array $conditions = []): array
    {
        $conditions = array_merge(
            [
                'modelUser'         => 0,
                'fakeUser'          => 0,
            ],
            $conditions
        );
        return array_column(
            $this->findRecords(
                null,
                $this->relation_industry_table,
                null,
                [
                    'columns' => [
                        $this->relation_industry_table . '.id_industry',
                        'COUNT(*) AS countBuyers'
                    ],
                    'joins' => [
                        'users'
                    ],
                    'conditions' => $conditions,
                    'group' => [
                        $this->relation_industry_table . '.id_industry'
                    ],
                    'order' => [
                        'countBuyers'   => 'DESC'
                    ],
                ]
            ),
            null,
            'id_industry'
        );
    }

	//todo change list of fields when design will be ready
	public function get_related_users_by_user_id($id_user)
	{
		$this->db->select("`USERS`.idu, CONCAT_WS(' ',`USERS`.fname, `USERS`.lname) as user_name, `USERS`.user_photo,`USERS`.user_group, ug.gr_name, ug.gr_type, `USERS`.is_verified, `USERS`.status");
		$this->db->from("`{$this->users_table}` AS `USERS`");
		$this->db->join("user_groups AS `ug`", "`USERS`.user_group = `ug`.idgroup", "inner");
		$this->db->where_raw('`USERS`.status != "deleted" AND `USERS`.id_principal = (SELECT id_principal FROM ' . $this->users_table . ' WHERE idu = ?)', [$id_user]);

		return $this->db->query_all();
	}

	public function get_related_users_by_id_principal($id_principal)
	{
		$this->db->select("`USERS`.idu, CONCAT_WS(' ',`USERS`.fname, `USERS`.lname) as user_name, `USERS`.user_photo,`USERS`.id_principal, `USERS`.user_group, ug.gr_name, ug.gr_type, `USERS`.is_verified, `USERS`.status");
		$this->db->from("`{$this->users_table}` AS `USERS`");
		$this->db->join("user_groups AS `ug`", "`USERS`.user_group = `ug`.idgroup", "inner");
		$this->db->where('`USERS`.id_principal', $id_principal);
		$this->db->where('`USERS`.status !=', "deleted");

		return $this->db->query_all();
	}

	public function get_simple_users_by_id_principal($id_principal)
	{
		$this->db->select("`USERS`.*");
		$this->db->from("`{$this->users_table}` AS `USERS`");
        $this->db->where('`USERS`.id_principal', $id_principal);
        $this->db->where('`USERS`.status !=', "deleted");

		return $this->db->query_all();
    }

    public function get_one_user_by_id_principal($id_principal)
	{
		$this->db->select("`USERS`.*");
		$this->db->from("`{$this->users_table}` AS `USERS`");
        $this->db->where('`USERS`.id_principal', $id_principal);
        $this->db->where('`USERS`.status !=', "deleted");

		return $this->db->query_one();
	}

	public function get_users_by_email($email)
	{
		$this->db->select("`USERS`.idu, ug.gr_name");
		$this->db->from("`{$this->users_table}` AS `USERS`");
		$this->db->join("user_groups AS `ug`", "USERS.user_group = `ug`.idgroup", "inner");
		$this->db->where('email', $email);

		return $this->db->query_all();
    }

	public function get_soon_expire_certification()
	{
		$this->db->select("*");
		$this->db->from($this->users_table);
		$this->db->where_raw('paid_until > NOW() AND (DATEDIFF(NOW(), paid_until) IN (-1,-3,-7,-30))');

		return $this->db->query_all();
	}

    /**
     * Returns the list of buyers suggest to the seller
     *
     * @param array $industriesIds
     * @param array $conditions
     * @param int|null $page
     * @param int|null $perPage
     *
     * @return array
     */
    public function getMatchmakingBuyers(array $industriesIds, array $conditions = [], ?int $page = null, ?int $perPage = null): array
    {
        $productRequestQueryBuilder = $this->createQueryBuilder();
        $productRequestQueryBuilder
            ->select(
                'IF(`product_requests`.`id` IS NULL, 0, 1) hasProductRequest',
                '`product_requests`.`id_user`',
            )
            ->from('product_requests', null)
            ->where(
                $productRequestQueryBuilder->expr()->in(
                    '`product_requests`.`id_category`',
                    array_map(
                        fn ($index, $industryId) => $productRequestQueryBuilder->createNamedParameter(
                            (int) $industryId,
                            ParameterType::INTEGER,
                            $this->nameScopeParameter("productRequestIndustryId{$index}")
                        ),
                        array_keys($industriesIds),
                        $industriesIds
                    )
                )
            )
            ->groupBy('`product_requests`.`id_user`')
        ;

        $viewedItemsQueryBuilder = $this->createQueryBuilder();
        $viewedItemsQueryBuilder
            ->select(
                '`last_viewed_items`.`id_user`',
                'IF(`last_viewed_items`.`id` IS NULL, 0, 1) hasLastViewedItems',
            )
            ->from('last_viewed_items', null)
            ->leftJoin(
                'last_viewed_items',
                'items',
                'items',
                '`last_viewed_items`.`id_item` = `items`.`id`'
            )
            ->leftJoin(
                'last_viewed_items',
                'item_category',
                'item_category',
                '`item_category`.`category_id` = `items`.`id_cat`'
            )
            ->where(
                $viewedItemsQueryBuilder->expr()->and(
                    $viewedItemsQueryBuilder->expr()->isNotNull('`last_viewed_items`.`id_user`'),
                    $viewedItemsQueryBuilder->expr()->in(
                        "CAST(JSON_EXTRACT(JSON_KEYS(JSON_EXTRACT(CONCAT('[', `item_category`.`breadcrumbs`, ']'), '$[0]')), '$[0]') AS UNSIGNED)",
                        array_map(
                            fn ($index, $industryId) => $viewedItemsQueryBuilder->createNamedParameter(
                                (int) $industryId,
                                ParameterType::INTEGER,
                                $this->nameScopeParameter("itemIndustryId{$index}")
                            ),
                            array_keys($industriesIds),
                            $industriesIds
                        )
                    )
                )
            )
            ->groupBy('`last_viewed_items`.`id_user`')
        ;


        /** @var \Buyer_Item_Categories_Stats_Model $buyerStats */
        $buyerStats = model(\Buyer_Item_Categories_Stats_Model::class);
        $buyerStatsTable = $buyerStats->getTable();

        $userIndustriesQueryBuilder = $this->createQueryBuilder();
        $userIndustriesQueryBuilder
            ->select(
                "`{$buyerStatsTable}`.`idu`",
                "IF(`{$buyerStatsTable}`.`id_category` IS NULL, 0, 1) hasIndustryOfInterest",
            )
            ->from($buyerStatsTable, null)
            ->where(
                $userIndustriesQueryBuilder->expr()->in(
                    "`{$buyerStatsTable}`.`id_category`",
                    array_map(
                        fn ($index, $industryId) => $userIndustriesQueryBuilder->createNamedParameter(
                            (int) $industryId,
                            ParameterType::INTEGER,
                            $this->nameScopeParameter("userIndustryId{$index}")
                        ),
                        array_keys($industriesIds),
                        $industriesIds
                    )
                )
            )
            ->groupBy("`{$buyerStatsTable}`.`idu`")
        ;

        $usersBuilder = $this->createQueryBuilder();
        $usersBuilder
            ->select(
                '`users`.`idu`',
                '`users`.`fname`',
                '`users`.`lname`',
                '`users`.`email`',
                '`users`.`phone_code`',
                '`users`.`phone`',
                '`port_country`.`country`',
                '`userProductRequests`.`hasProductRequest`',
                '`userLastViewedItems`.`hasLastViewedItems`',
                '`userIndustries`.`hasIndustryOfInterest`'
            )
            ->from('users', null)
            ->leftJoin(
                'users',
                '(' . $productRequestQueryBuilder->getSQL() . ')',
                'userProductRequests',
                '`userProductRequests`.`id_user` = `users`.`idu`'
            )
            ->leftJoin(
                'users',
                '(' . $viewedItemsQueryBuilder->getSQL() . ')',
                'userLastViewedItems',
                '`userLastViewedItems`.`id_user` = `users`.`idu`'
            )
            ->leftJoin(
                'users',
                '(' . $userIndustriesQueryBuilder->getSQL() . ')',
                'userIndustries',
                '`users`.`idu` = `userIndustries`.`idu`'
            )
            ->leftJoin(
                'users',
                'port_country',
                'port_country',
                '`users`.`country` = `port_country`.`id`',
            )
            ->where(
                $usersBuilder->expr()->and(
                    $usersBuilder->expr()->eq(
                        '`users`.`status`',
                        $usersBuilder->createNamedParameter('active', ParameterType::STRING, $this->nameScopeParameter('userStatus'))
                    ),
                    $usersBuilder->expr()->eq(
                        '`users`.`user_group`',
                        $usersBuilder->createNamedParameter(1, ParameterType::INTEGER, $this->nameScopeParameter('userGroup'))
                    ),
                    $usersBuilder->expr()->or(
                        $usersBuilder->expr()->isNotNull('`userProductRequests`.`hasProductRequest`'),
                        $usersBuilder->expr()->isNotNull('`userLastViewedItems`.`hasLastViewedItems`'),
                        $usersBuilder->expr()->isNotNull('`userIndustries`.`hasIndustryOfInterest`'),
                    )
                )
            )
            ->orderBy('`userProductRequests`.`hasProductRequest`', 'desc')
            ->addOrderBy('`userLastViewedItems`.`hasLastViewedItems`', 'desc')
            ->addOrderBy('`userIndustries`.`hasIndustryOfInterest`', 'desc')
        ;

        if (null !== $perPage) {
            $usersBuilder->setMaxResults($perPage);
        }

        if (null !== $page) {
            $usersBuilder->setFirstResult($page);
        }

        if (isset($conditions['leftProductRequests'])) {
            $usersBuilder->andWhere(
                $conditions['leftProductRequests']
                    ? $usersBuilder->expr()->isNotNull('`userProductRequests`.`hasProductRequest`')
                    : $usersBuilder->expr()->isNull('`userProductRequests`.`hasProductRequest`')
            );
        }

        if (isset($conditions['industriesOfInterest'])) {
            $usersBuilder->andWhere(
                $conditions['industriesOfInterest']
                    ? $usersBuilder->expr()->isNotNull('`userIndustries`.`hasIndustryOfInterest`')
                    : $usersBuilder->expr()->isNull('`userIndustries`.`hasIndustryOfInterest`')
            );
        }

        if (isset($conditions['lastViewedItems'])) {
            $usersBuilder->andWhere(
                $conditions['lastViewedItems']
                    ? $usersBuilder->expr()->isNotNull('`userLastViewedItems`.`hasLastViewedItems`')
                    : $usersBuilder->expr()->isNull('`userLastViewedItems`.`hasLastViewedItems`')
            );
        }

        return $this->getConnection()->fetchAllAssociative(
            $usersBuilder->getSQL(),
            array_merge(
                $productRequestQueryBuilder->getParameters(),
                $viewedItemsQueryBuilder->getParameters(),
                $userIndustriesQueryBuilder->getParameters(),
                $usersBuilder->getParameters()
            )
        );
    }

    /**
     * Returns count of the buyers suggest to the seller
     *
     * @param array $industriesIds
     * @param array $conditions
     *
     * @return int
     */
    public function getCountMatchmakingBuyers(array $industriesIds, array $conditions = []): int
    {
        $productRequestQueryBuilder = $this->createQueryBuilder();
        $productRequestQueryBuilder
            ->select(
                'IF(`product_requests`.`id` IS NULL, 0, 1) hasProductRequest',
                '`product_requests`.`id_user`',
            )
            ->from('product_requests', null)
            ->where(
                $productRequestQueryBuilder->expr()->in(
                    '`product_requests`.`id_category`',
                    array_map(
                        fn ($index, $industryId) => $productRequestQueryBuilder->createNamedParameter(
                            (int) $industryId,
                            ParameterType::INTEGER,
                            $this->nameScopeParameter("productRequestIndustryId{$index}")
                        ),
                        array_keys($industriesIds),
                        $industriesIds
                    )
                )
            )
            ->groupBy('`product_requests`.`id_user`')
        ;

        $viewedItemsQueryBuilder = $this->createQueryBuilder();
        $viewedItemsQueryBuilder
            ->select(
                '`last_viewed_items`.`id_user`',
                'IF(`last_viewed_items`.`id` IS NULL, 0, 1) hasLastViewedItems',
            )
            ->from('last_viewed_items', null)
            ->leftJoin(
                'last_viewed_items',
                'items',
                'items',
                '`last_viewed_items`.`id_item` = `items`.`id`'
            )
            ->leftJoin(
                'last_viewed_items',
                'item_category',
                'item_category',
                '`item_category`.`category_id` = `items`.`id_cat`'
            )
            ->where(
                $viewedItemsQueryBuilder->expr()->and(
                    $viewedItemsQueryBuilder->expr()->isNotNull('`last_viewed_items`.`id_user`'),
                    $viewedItemsQueryBuilder->expr()->in(
                        "CAST(JSON_EXTRACT(JSON_KEYS(JSON_EXTRACT(CONCAT('[', `item_category`.`breadcrumbs`, ']'), '$[0]')), '$[0]') AS UNSIGNED)",
                        array_map(
                            fn ($index, $industryId) => $viewedItemsQueryBuilder->createNamedParameter(
                                (int) $industryId,
                                ParameterType::INTEGER,
                                $this->nameScopeParameter("itemIndustryId{$index}")
                            ),
                            array_keys($industriesIds),
                            $industriesIds
                        )
                    )
                )
            )
            ->groupBy('`last_viewed_items`.`id_user`')
        ;

        /** @var \Buyer_Item_Categories_Stats_Model $buyerStats */
        $buyerStats = model(\Buyer_Item_Categories_Stats_Model::class);
        $buyerStatsTable = $buyerStats->getTable();

        $userIndustriesQueryBuilder = $this->createQueryBuilder();
        $userIndustriesQueryBuilder
            ->select(
                "`{$buyerStatsTable}`.`idu`",
                "IF(`{$buyerStatsTable}`.`id_category` IS NULL, 0, 1) hasIndustryOfInterest",
            )
            ->from($buyerStatsTable, null)
            ->where(
                $userIndustriesQueryBuilder->expr()->in(
                    "`{$buyerStatsTable}`.`id_category`",
                    array_map(
                        fn ($index, $industryId) => $userIndustriesQueryBuilder->createNamedParameter(
                            (int) $industryId,
                            ParameterType::INTEGER,
                            $this->nameScopeParameter("userIndustryId{$index}")
                        ),
                        array_keys($industriesIds),
                        $industriesIds
                    )
                )
            )
            ->groupBy("`{$buyerStatsTable}`.`idu`")
        ;

        $usersBuilder = $this->createQueryBuilder();
        $usersBuilder
            ->select('COUNT(*) countBuyers')
            ->from('users', null)
            ->leftJoin(
                'users',
                '(' . $productRequestQueryBuilder->getSQL() . ')',
                'userProductRequests',
                '`userProductRequests`.`id_user` = `users`.`idu`'
            )
            ->leftJoin(
                'users',
                '(' . $viewedItemsQueryBuilder->getSQL() . ')',
                'userLastViewedItems',
                '`userLastViewedItems`.`id_user` = `users`.`idu`'
            )
            ->leftJoin(
                'users',
                '(' . $userIndustriesQueryBuilder->getSQL() . ')',
                'userIndustries',
                '`users`.`idu` = `userIndustries`.`idu`'
            )
            ->leftJoin(
                'users',
                'port_country',
                'port_country',
                '`users`.`country` = `port_country`.`id`',
            )
            ->where(
                $usersBuilder->expr()->and(
                    $usersBuilder->expr()->eq(
                        '`users`.`status`',
                        $usersBuilder->createNamedParameter('active', ParameterType::STRING, $this->nameScopeParameter('userStatus'))
                    ),
                    $usersBuilder->expr()->eq(
                        '`users`.`user_group`',
                        $usersBuilder->createNamedParameter(1, ParameterType::INTEGER, $this->nameScopeParameter('userGroup'))
                    ),
                    $usersBuilder->expr()->or(
                        $usersBuilder->expr()->isNotNull('`userProductRequests`.`hasProductRequest`'),
                        $usersBuilder->expr()->isNotNull('`userLastViewedItems`.`hasLastViewedItems`'),
                        $usersBuilder->expr()->isNotNull('`userIndustries`.`hasIndustryOfInterest`'),
                    )
                )
            )
            ->orderBy('`userProductRequests`.`hasProductRequest`', 'desc')
            ->addOrderBy('`userLastViewedItems`.`hasLastViewedItems`', 'desc')
            ->addOrderBy('`userIndustries`.`hasIndustryOfInterest`', 'desc')
            ->setMaxResults(1)
        ;

        if (isset($conditions['leftProductRequests'])) {
            $usersBuilder->andWhere(
                $conditions['leftProductRequests']
                    ? $usersBuilder->expr()->isNotNull('`userProductRequests`.`hasProductRequest`')
                    : $usersBuilder->expr()->isNull('`userProductRequests`.`hasProductRequest`')
            );
        }

        if (isset($conditions['industriesOfInterest'])) {
            $usersBuilder->andWhere(
                $conditions['industriesOfInterest']
                    ? $usersBuilder->expr()->isNotNull('`userIndustries`.`hasIndustryOfInterest`')
                    : $usersBuilder->expr()->isNull('`userIndustries`.`hasIndustryOfInterest`')
            );
        }

        if (isset($conditions['lastViewedItems'])) {
            $usersBuilder->andWhere(
                $conditions['lastViewedItems']
                    ? $usersBuilder->expr()->isNotNull('`userLastViewedItems`.`hasLastViewedItems`')
                    : $usersBuilder->expr()->isNull('`userLastViewedItems`.`hasLastViewedItems`')
            );
        }

        $queryResult = $this->getConnection()->fetchAssociative(
            $usersBuilder->getSQL(),
            array_merge(
                $productRequestQueryBuilder->getParameters(),
                $viewedItemsQueryBuilder->getParameters(),
                $userIndustriesQueryBuilder->getParameters(),
                $usersBuilder->getParameters()
            )
        );

        return (int) $queryResult['countBuyers'];
    }

    //TO DELETE AFTER USE
    public function getUsersForBadges()
    {
        $this->db->select('idu,user_photo');
        $this->db->from($this->users_table);
        $this->db->where_raw('user_photo <>""');
        $this->db->where_raw('user_group in (2,3,5,6)');

        return $this->db->query_all();
    }

    //TO DELETE AFTER USE
    public function getCertifiedUsers($idUser = null)
    {
        $this->db->select("idu,email, CONCAT_WS(' ', fname, lname) as user_name");
        $this->db->from($this->users_table);
        $this->db->where_raw('user_group in (5,6)');
        $this->db->where_raw('paid_until > NOW()');
        if(isset($idUser)){
            $this->db->where('idu', $idUser);
            $this->db->limit(1);
        }

        return $this->db->query_all();
    }

    /************************************************ Scopes ************************************************/

    /**
     * Scope a query to filter by message notification id.
     *
     * @param QueryBuilder $builder
     * @param int $messageId
     *
     * @return void
     */
    protected function scopeNotificationMessage(QueryBuilder $builder, int $messageId): void
    {
        $builder->where(
            $builder->expr()->eq(
                'id_message',
                $builder->createNamedParameter($messageId, ParameterType::INTEGER, $this->nameScopeParameter('messageId'))
            )
        );
    }

    /**
     * Scope a query to filter by is demo or not.
     *
     * @param QueryBuilder $builder
     * @param int $fakeUser
     *
     * @return void
     */
    protected function scopeFakeUser(QueryBuilder $builder, int $fakeUser): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->get_users_table()}`.`fake_user`",
                $builder->createNamedParameter($fakeUser, ParameterType::INTEGER, $this->nameScopeParameter('fakeUser'))
            )
        );
    }

    /**
     * Scope a query to filter by is model or not.
     *
     * @param QueryBuilder $builder
     * @param int $modelUser
     *
     * @return void
     */
    protected function scopeModelUser(QueryBuilder $builder, int $modelUser): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->get_users_table()}`.`is_model`",
                $builder->createNamedParameter($modelUser, ParameterType::INTEGER, $this->nameScopeParameter('modelUser'))
            )
        );
    }

    /**
     * Scope a query to filter by user groups
     *
     * @param QueryBuilder $builder
     * @param array $userGroups
     *
     * @return void
     */
    protected function scopeUserGroups(QueryBuilder $builder, array $userGroups): void
    {
        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->get_users_table()}`.`user_group`",
                array_map(
                    fn (int $index, $userGroup) => $builder->createNamedParameter(
                        (int) $userGroup,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("userGroup{$index}")
                    ),
                    array_keys($userGroups),
                    $userGroups
                )
            )
        );
    }

    /**
     * Scope a query to filter by user statuses
     *
     * @param QueryBuilder $builder
     * @param array $userStatuses
     *
     * @return void
     */
    protected function scopeUserStatuses(QueryBuilder $builder, array $userStatuses): void
    {
        $builder->andWhere(
            $builder->expr()->in(
                'status',
                array_map(
                    fn (int $index, $userStatus) => $builder->createNamedParameter(
                        (string) $userStatus,
                        ParameterType::STRING,
                        $this->nameScopeParameter("userStatus{$index}")
                    ),
                    array_keys($userStatuses),
                    $userStatuses
                )
            )
        );
    }

    /**
     * Scope a query to filter by user status
     *
     * @param QueryBuilder $builder
     * @param string $userStatus
     *
     * @return void
     */
    protected function scopeUserStatus(QueryBuilder $builder, string $userStatus): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'status',
                $builder->createNamedParameter($userStatus, ParameterType::STRING, $this->nameScopeParameter('userStatus'))
            )
        );
    }

    /**
     * Scope query to filter users by matchmaking email date
     *
     * @param QueryBuilder $builder
     * @param string $date in format Y:m:d
     *
     * @return void
     */
    protected function scopeMatchmakingEmailDateLTE(QueryBuilder $builder, string $date): void
    {
        $builder->andWhere(
            $builder->expr()->or(
                $builder->expr()->lte(
                    "DATE(`{$this->get_users_table()}`.`matchmaking_email_date`)",
                    $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('matchmakingEmailDateLTE'))
                ),
                $builder->expr()->isNull('`matchmaking_email_date`')
            )
        );
    }

    /**
     * Scope query to filter users by matchmaking email date
     *
     * @param QueryBuilder $builder
     * @param int $value 0 OR 1
     *
     * @return void
     */
    protected function scopeAcceptMatchmakingEmail(QueryBuilder $builder, int $value): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->get_users_table()}`.`accept_matchmaking_email`",
                $builder->createNamedParameter($value, ParameterType::INTEGER, $this->nameScopeParameter('acceptMatchmakingEmail'))
            ),
        );
    }

    /**
     * Scope query to filter users by registration date
     *
     * @param QueryBuilder $builder
     * @param string $date in format Y:m:d
     *
     * @return void
     */
    protected function scopeRegistrationDateLTE(QueryBuilder $builder, string $date): void
    {
        $builder->andWhere(
            $builder->expr()->lte(
                "DATE(`{$this->get_users_table()}`.`registration_date`)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('registrationDateLTE'))
            ),
        );
    }

    /**
     * Scope query to filter users by matchmaking email date
     *
     * @param QueryBuilder $builder
     *
     * @return void
     */
    protected function scopeMatchmakingEmailDateNotNull(QueryBuilder $builder): void
    {
        $builder->andWhere(
            $builder->expr()->isNotNull("`{$this->get_users_table()}`.`matchmaking_email_date`")
        );
    }

    /**
     * @deprecated use the new model Users_Model
     * @todo Remove
     * Scope for join with users.
     *
     * @param QueryBuilder $builder
     *
     * @return void
     */
    protected function bindUsers(QueryBuilder $builder): void
    {
        $builder
            ->leftJoin(
                $this->relation_industry_table,
                $this->users_table,
                $this->users_table,
                "`{$this->users_table}`.`idu` = `{$this->relation_industry_table}`.`id_user`"
            )
        ;
    }
}
