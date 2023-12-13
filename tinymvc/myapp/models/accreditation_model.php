<?php

/**
 * Accreditation_Model.php
 *
 * Accreditation model
 *
 * @author Cravciuc Andrei
 *
 * @deprecated v2.28.6 in favor of \Verification_Document_Types_Model
 */
class Accreditation_Model extends TinyMVC_Model {

	// HOLD THE CURRENT CONTROLLER INSTANCE
	// var $obj;
	// private $accreditation_docs_table = 'accreditation_docs';
	// private $accreditation_docs_groups_relation_table = 'accreditation_docs_groups_relation';
	// private $accreditation_docs_countries_relation_table = 'verification_documents_countries_relation';
	// private $accreditation_docs_industries_relation_table = 'verification_documents_industries_relation';
	// private $users_table = 'users';
	// private $users_calling_statuses_table = 'users_calling_statuses';
	// private $user_groups_table = 'user_groups';
	// private $company_base_table = 'company_base';
	// private $orders_shippers_table = 'orders_shippers';
	// private $port_country_table = 'port_country';
	// private $zips_table = 'zips';
	// private $item_category_table = 'item_category';

    /* public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    } */

	/* public function insert_doc($data = array()){
        if(empty($data)){
            return false;
        }

		$this->db->insert($this->accreditation_docs_table, $data);
		return $this->db->last_insert_id();
	} */

    /* public function insert_doc_groups_relation($data)
    {
        $this->db->insert_batch($this->accreditation_docs_groups_relation_table, $data);

		return $this->db->getAffectableRowsAmount();
    } */

    /* public function insert_doc_countries_relation($data)
    {
        if (empty($data)) {
            return;
        }

        $this->db->insert_batch($this->accreditation_docs_countries_relation_table, $data);

		return $this->db->affectedRows();
    } */

    /* public function insert_doc_industries_relation($data)
    {
        if (empty($data)) {
            return;
        }

        $this->db->insert_batch($this->accreditation_docs_industries_relation_table, $data);

		return $this->db->affectedRows();
	} */

    /* public function delete_doc_groups_relation($id_doc)
    {
        $this->db->where('id_document', $id_doc);

		return $this->db->delete($this->accreditation_docs_groups_relation_table);
    } */

    /* public function delete_doc_countries_relation($id_doc)
    {
        $this->db->where('id_document', $id_doc);

		return $this->db->delete($this->accreditation_docs_countries_relation_table);
    } */

    /* public function delete_doc_industries_relation($id_doc)
    {
        $this->db->where('id_document', $id_doc);

		return $this->db->delete($this->accreditation_docs_industries_relation_table);
	} */

	/* public function update_doc($id_doc, $data = array()){
        if(empty($data)){
            return false;
        }

        $this->db->where('id_document', $id_doc);
		return $this->db->update($this->accreditation_docs_table, $data);
	} */

	/* public function delete_doc($id_doc){
        $this->db->where('id_document', $id_doc);
		return $this->db->delete($this->accreditation_docs_table);
	} */

    /* public function get_document($id_doc){
        $sql = "SELECT *
                FROM {$this->accreditation_docs_table}
                WHERE id_document = ?";

        return $this->db->query_one($sql, array($id_doc));
    } */

    /* public function get_documents($conditions = array()){
        $where = array();
        $having = array();
        $params = array();
        $relParams = [];
        $columns = array();
        $order_by = ' id_document DESC ';
        extract($conditions);

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		if (isset($documents_list)) {
			$where[] = ' id_document IN (' . implode(',', array_fill(0, count($documents_list), '?')) . ') ';
            array_push($params, ...$documents_list);
        }

        if (!empty($i18n_with_lang)) {
            $columns[] = "JSON_EXTRACT(document_i18n, '$.{$i18n_with_lang}.*.value') as `translated_language_values`";
            $having[] = "
                (
                    JSON_CONTAINS_PATH(document_i18n, 'one', '$.{$i18n_with_lang}') AND
                    NOT JSON_CONTAINS(translated_language_values, JSON_ARRAY(null)) AND
                    NOT JSON_CONTAINS(translated_language_values, JSON_ARRAY(\"\"))
                )
            ";
        }

        if(!empty($i18n_without_lang)) {
            $columns[] = "JSON_EXTRACT(document_i18n, '$.{$i18n_without_lang}.*.value') as `not_translated_language_value`";
            $having[] = "
                (
                    NOT JSON_CONTAINS_PATH(document_i18n, 'one', '$.{$i18n_without_lang}') OR
                    JSON_CONTAINS(not_translated_language_value, JSON_ARRAY(null)) OR
                    JSON_CONTAINS(not_translated_language_value, JSON_ARRAY(\"\"))
                )
            ";
        }

        if (isset($base_update_from)) {
            $where[] = "document_base_text_updated_at >= ?";
            $params[] = $base_update_from;
        }

        if (isset($base_update_to)) {
            $where[] = "document_base_text_updated_at <= ?";
            $params[] = $base_update_to;
        }

        $rel = '';
        if(!empty($keywords)){
			if(str_word_count_utf8($keywords) > 1){
				$order_by = ' REL_tags DESC ';
				$where[] = ' MATCH (document_title) AGAINST (?)';
				$params[] = $keywords;
				$rel = " , MATCH (document_title) AGAINST ( ? ) as REL_tags";
                $relParams[] = $keywords;
			} else{
				$where[] = " document_title LIKE ?";
                $params[] = '%' . $keywords . '%';
			}
        }

        if(!empty($columns)){
            $columns = ', ' . implode(', ', $columns);
        } else {
            $columns = '';
        }

        $sql = "SELECT
                    *
                    $rel
                    $columns
                FROM $this->accreditation_docs_table";

        if(!empty($where)){
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        if(!empty($having)){
            $sql .= ' HAVING ' . implode(' AND ', $having);
        }

		$sql .= ' ORDER BY ' . $order_by;

		if (isset($start) && isset($per_p)) {
            $start = (int) $start;
            $per_p = (int) $per_p;
            $sql .= " LIMIT {$start}, {$per_p} ";
        }

        // the order of the passed variables to the array_merge function matters
        $params = array_merge(
            $relParams,
            $params,
        );

        if(null !== $this->db->getQueryResult()) {
            $this->db->getQueryResult()->closeCursor();
        }

        return $this->db->query_all($sql, $params);
    } */

    /* public function count_documents($conditions = array()){
        $where = array();
        $having = array();
        $columns = array();
        $params = array();
        extract($conditions);

        if (isset($documents_list)) {
			$where[] = ' id_document IN (' . implode(',', array_fill(0, count($documents_list), '?')) . ') ';
            array_push($params, ...$documents_list);
        }

        if(isset($keywords)){
			$words = explode(' ', $keywords);
			if(count($words) > 1){
				$where[] = ' MATCH(document_title) AGAINST ( ? ) ';
				$params[] = $keywords;
			} else{
				$where[] = " document_title LIKE ? ";
                $params[] = '%' . $keywords . '%';
			}
        }

        if(!empty($i18n_with_lang)) {
            $where[] = "
                (
                    JSON_CONTAINS_PATH(document_i18n, 'one', '$.{$i18n_with_lang}') AND
                    NOT JSON_CONTAINS(JSON_EXTRACT(document_i18n, '$.{$i18n_with_lang}.*.value'), JSON_ARRAY(null)) AND
                    NOT JSON_CONTAINS(JSON_EXTRACT(document_i18n, '$.{$i18n_with_lang}.*.value'), JSON_ARRAY(\"\"))
                )
            ";
        }

        if(!empty($i18n_without_lang)) {
            $where[] = "
                (
                    NOT JSON_CONTAINS_PATH(document_i18n, 'one', '$.{$i18n_without_lang}') OR
                    JSON_CONTAINS(JSON_EXTRACT(document_i18n, '$.{$i18n_without_lang}.*.value'), JSON_ARRAY(null)) OR
                    JSON_CONTAINS(JSON_EXTRACT(document_i18n, '$.{$i18n_without_lang}.*.value'), JSON_ARRAY(\"\"))
                )
            ";
        }

        if (isset($base_update_from)) {
            $where[] = "document_base_text_updated_at >= ?";
            $params[] = $base_update_from;
        }

        if (isset($base_update_to)) {
            $where[] = "document_base_text_updated_at <= ?";
            $params[] = $base_update_to;
        }

        $sql = "SELECT
                    COUNT(*) as counter
                FROM {$this->accreditation_docs_table}";

        if(!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        if(!empty($having)){
            $sql .= ' HAVING ' . implode(' AND ', $having);
        }

        $res = $this->db->query_one($sql, $params);
        return $res['counter'];
    } */

    /* private function _accreditation_users_params($conditions = array()){
        $this->db->from("{$this->users_table} u");
        $this->db->join("{$this->user_groups_table} ug", "u.user_group = ug.idgroup", "left");
        $this->db->join("{$this->users_calling_statuses_table} cs", "u.calling_status = cs.id_status", "left");
        $this->db->join("{$this->port_country_table} pc", "u.country = pc.id", "left");
        $this->db->join("{$this->zips_table} z", "u.city = z.id", "left");

        $this->db->in('ug.gr_type', array(
            'Buyer',
            'Seller',
            'Shipper'
        ));

        extract($conditions);

		if(isset($id_user)){
            $this->db->where('u.idu = ?', $id_user);
        }

        if(isset($verified)){
            $this->db->where('u.is_verified = ?', (int) $verified);
        }

        if(isset($verification_progress)){
            $this->db->where('u.verfication_upload_progress = ?', $verification_progress);
        }

        if(isset($accreditation_files)){
            $this->db->where('u.accreditation_files = ?', $accreditation_files);
        }

        if(isset($accreditation)){
            $this->db->where('u.accreditation = ?', $accreditation);
        }

        if(isset($logged_in)){
            $this->db->where('u.logged = ?', $logged_in);
        }

        if(isset($country)){
            $this->db->where('u.country = ?', $country);
        }

        if(isset($user_group)){
            $this->db->where('u.user_group = ?', $user_group);
        }

        if(isset($status)){
            $this->db->where('u.status = ?', $status);
        }

        if(isset($email_status)){
            $this->db->where('u.email_status = ?', $email_status);
        }

        if (isset($zoho_id_record)) {
            $this->db->where('u.zoho_id_record = ?', $zoho_id_record);
        }

        if(isset($location_completion)){
            if (!$location_completion) {
                $this->db->where("(u.country IS NULL OR u.state IS NULL OR u.city IS NULL OR u.country = ? OR u.state = ? OR u.city = ?)", array(0, 0, 0));
            } else {
                $this->db->where("(u.country IS NOT NULL AND u.state IS NOT NULL AND u.city IS NOT NULL AND u.country != ? AND u.state != ? AND u.city != ?)", array(0, 0, 0));
            }
		}

        if(isset($accreditation_files_upload)){
            $this->db->where('u.accreditation_files_upload = ?', $accreditation_files_upload);
        }

		if(isset($registration_from_date)){
            $this->db->where('u.registration_date >= ?', formatDate($registration_from_date, 'Y-m-d H:i:s'));
		}

		if(isset($registration_to_date)){
            $this->db->where('u.registration_date <= ?', formatDate($registration_to_date, 'Y-m-d H:i:s'));
		}

		if(isset($resend_email_from_date)){
            $this->db->where('u.resend_email_date >= ?', formatDate($resend_email_from_date, 'Y-m-d H:i:s'));
		}

		if(isset($resend_email_to_date)){
            $this->db->where('u.resend_email_date <= ?', formatDate($resend_email_to_date, 'Y-m-d H:i:s'));
		}

		if(isset($accreditation_files_upload_from_date)){
            $this->db->where('u.accreditation_files_upload_date >= ?', formatDate($accreditation_files_upload_from_date, 'Y-m-d H:i:s'));
		}

		if(isset($accreditation_files_upload_to_date)){
            $this->db->where('u.accreditation_files_upload_date <= ?', formatDate($accreditation_files_upload_to_date, 'Y-m-d H:i:s'));
		}

		if(isset($calling_status)){
            $this->db->where('u.calling_status = ?', $calling_status);
		}

		if(isset($calling_from_date)){
            $this->db->where('u.calling_date_last >= ?', formatDate($calling_from_date, 'Y-m-d H:i:s'));
		}

		if(isset($calling_to_date)){
            $this->db->where('u.calling_date_last <= ?', formatDate($calling_to_date, 'Y-m-d H:i:s'));
		}

        if(isset($keywords)){
            $keywordsParams = [];
			$words = explode(" ", $keywords);
			foreach($words as $word){
				if(strlen($word) > 3){
					$s_word[] = " (u.fname LIKE ? OR u.lname LIKE ? OR u.email LIKE ? OR CONCAT(phone_code,'',phone) LIKE ?)";
                    array_push($keywordsParams, ...array_fill(0, 4, '%' . $word . '%'));
                }
			}

			if(!empty($s_word)){
                $this->db->where_raw(" (" . implode(" AND ", $s_word) . ")", $keywordsParams);
            }
		}

        if ($withoutActiveCancellationRequests ?? false) {
            $this->db->join("user_close_requests", "u.idu = user_close_requests.user AND user_close_requests.status IN ('init', 'confirmed')", "left");
            $this->db->where_raw('user_close_requests.idreq IS NULL');
        }
    } */

    /* public function get_accreditation_users($conditions = array()){
        $this->db->select("
            u.*,
            pc.country as country_name,
            z.city as city_name,
            ug.gr_name, ug.gr_type,
            cs.status_title, cs.status_description, cs.status_color
        ");

        $this->_accreditation_users_params($conditions);

        $order_by = ' u.idu DESC ';
		if(isset($conditions['sort_by'])){
            $multi_order_by = array();
			foreach($conditions['sort_by'] as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

            if(!empty($multi_order_by)){
                $order_by = implode(',', $multi_order_by);
            }
        }

        $this->db->orderby($order_by);

		if(isset($conditions['start']) && isset($conditions['per_p'])) {
            $this->db->limit((int) $conditions['per_p'], (int) $conditions['start']);
        }

        $records = $this->db->query_all();

        return !empty($records) ? $records : array();
    } */

    /* public function count_accreditation_users($conditions = array()){
        $this->db->select('COUNT(u.idu) as total_rows');

        $this->_accreditation_users_params($conditions);

        $records = $this->db->query_one();

        return $records['total_rows'];
    } */

    /* function get_accreditation_docs($conditions = array()){
        $where = array();
        $params = array();
        $group = null;
        $country = null;
        $industries = array();
        $return_type = 'json';
        $language = __SITE_LANG;

        extract($conditions);

        if (null === $group) {
            return '';
        }

        $groups = getArrayFromString($group);

        $where[] = " adgr.id_group IN (" . implode(',', array_fill(0, count($groups), '?')) . ") ";
        array_push($params, ...$groups);

        if(isset($exclude_docs)){
            $where[] = ' ad.id_document NOT IN ( ' . implode(',', array_fill(0, count($exclude_docs), '?')) . ' ) ';
            array_push($params, ...$exclude_docs);
        }

        if(isset($required)){
            $where[] = ' adgr.is_required = ? ';
			$params[] = $required;
        }

        $sql = "SELECT *
                FROM {$this->accreditation_docs_table} ad
                INNER JOIN {$this->accreditation_docs_groups_relation_table} adgr
                    ON ad.id_document = adgr.id_document";

		if(!empty($where)) {
			$sql .= ' WHERE ' . implode(' AND ', $where);
		}

        $documents = $this->db->query_all($sql, $params);

		if(empty($documents)){
			return '';
		}

        // GENERATE PERSONAL DOCUMENTS BY CONDITIONS
        $accreditation_documents = array();
        foreach($documents as $document){
            // SELECT DOCUMENTS FOR SPECIFIC COUNTRY
            if($country != null){
                $doc_countries = explode(',', $document['document_countries']);
                if(!in_array($country, $doc_countries)){
                    continue;
                }
            } elseif($document['document_general_countries'] == 0){
                continue;
            }

            // SELECT DOCUMENTS FOR SPECIFIC INDUSTRIES
            if(empty($industries)){
                if($document['document_general_industries'] == 0){
                    continue;
                }
            } else{
                $doc_industries = explode(',', $document['document_industries']);
                $intersect = array_intersect($doc_industries, $industries);
                if(empty($intersect)){
                    continue;
                }
            }

			if(isset($exclude_docs) && in_array($document['id_document'], $exclude_docs)){
				continue;
			}

			$document_json = array(
				'title'        => accreditation_i18n(
                    $document['document_i18n'],
                    'title',
                    $language,
                    $document['document_title']
                ),
				'status'       => 'init',
				'id_document'  => $document['id_document'],
				'status_title' => 'Not uploaded'
            );

			if($return_type == 'json'){
				$accreditation_documents[] = json_encode($document_json);
			} else{
				$accreditation_documents[] = $document_json;
			}
        }

		if(!empty($accreditation_documents)){
			if($return_type == 'json'){
				return implode(',', $accreditation_documents);
			} else{
				return $accreditation_documents;
			}
		}

		if($return_type == 'json'){
			return '';
		} else{
			return array();
		}
    } */

    /* function require_documents($conditions = array()){
        $where = array();
        $params = array();
        $group = null;
        $country = null;
        $industries = array();
        $language = __SITE_LANG;

        extract($conditions);

        if($group == null){
            return array();
        }

        if(!is_array($group)){
            $group = explode(',', $group);
            $group = array_map('intval', $group);
            $group = array_filter($group);

            if(empty($group)){
                return array();
            }
        }

        $where[] = " adgr.id_group IN (" . implode(',', array_fill(0, count($group), '?')) . ") ";
        array_push($params, ...$group);

        if(isset($exclude_docs)){
            $where[] = ' ad.id_document NOT IN ( ' . implode(',', array_fill(0, count($exclude_docs), '?')) . ' ) ';
            array_push($params, ...$exclude_docs);
        }

        if(isset($required)){
            $where[] = ' adgr.is_required = ? ';
			$params[] = $required;
        }

        $sql = "SELECT *
                FROM {$this->accreditation_docs_table} ad
                INNER JOIN {$this->accreditation_docs_groups_relation_table} adgr
                    ON ad.id_document = adgr.id_document";

		if(!empty($where)) {
			$sql .= ' WHERE ' . implode(' AND ', $where);
		}

        $documents = $this->db->query_all($sql, $params);

		if(empty($documents)){
			return '';
		}

        // GENERATE PERSONAL DOCUMENTS BY CONDITIONS
        $accreditation_documents = array();
        foreach($documents as $document){
            // SELECT DOCUMENTS FOR SPECIFIC COUNTRY
            if($country != null){
                $doc_countries = explode(',', $document['document_countries']);
                if(!in_array($country, $doc_countries)){
                    continue;
                }
            } elseif($document['document_general_countries'] == 0){
                continue;
            }

            // SELECT DOCUMENTS FOR SPECIFIC INDUSTRIES
            if(empty($industries)){
                if($document['document_general_industries'] == 0){
                    continue;
                }
            } else{
                $doc_industries = explode(',', $document['document_industries']);
                $intersect = array_intersect($doc_industries, $industries);
                if(empty($intersect)){
                    continue;
                }
            }

			if(isset($exclude_docs) && in_array($document['id_document'], $exclude_docs)){
				continue;
			}

			$document_json = array(
				'title'        => accreditation_i18n(
                    $document['document_i18n'],
                    'title',
                    $language,
                    $document['document_title']
                ),
				'status'       => 'init',
				'id_document'  => $document['id_document'],
				'status_title' => 'Not uploaded'
            );

			if($return_type == 'json'){
				$accreditation_documents[] = json_encode($document_json);
			} else{
				$accreditation_documents[] = $document_json;
			}
        }

		if(!empty($accreditation_documents)){
			if($return_type == 'json'){
				return implode(',', $accreditation_documents);
			} else{
				return $accreditation_documents;
			}
		}

		if($return_type == 'json'){
			return '';
		} else{
			return array();
		}
    } */

	/* function get_simple_accreditation_docs($conditions = array()){
        $where = array();
        $params = array();

        extract($conditions);

        if ($group_list){
            $group_list = getArrayFromString($group_list);

            $where[] = " adgr.id_group IN (" . implode(',', array_fill(0, count($group_list), '?')) . ") ";
            array_push($params, ...$group_list);
		}

        $sql = "SELECT
                    *
                FROM {$this->accreditation_docs_table} ad
                INNER JOIN {$this->accreditation_docs_groups_relation_table} adgr
                    ON ad.id_document = adgr.id_document";

		if(!empty($where)) {
			$sql .= ' WHERE ' . implode(' AND ', $where);
        }

		if(isset($group_by)) {
			$sql .= " GROUP BY {$group_by} ";
        }

		return $this->db->query_all($sql, $params);
    } */

    /* public function get_accreditation_document($id, array $params = array())
    {
		$with = array();
        $columns = array();
        $conditions = array();

        extract($params);
		extract($conditions, EXTR_PREFIX_ALL, 'condition');
        extract($with, EXTR_PREFIX_ALL, 'with');

        $this->db->select(empty($columns) ? '*' : (is_string($columns) ? $columns : implode(', ', $columns)));
        $this->db->from("{$this->accreditation_docs_table} AD");
        $this->db->where('AD.id_document = ? ', $id);

		if(isset($condition_language)){
            $this->db->where(
                "(JSON_CONTAINS_PATH(AD.document_i18n, 'all', ?, ?) = 1 AND JSON_TYPE(AD.document_i18n->?) != 'NULL' AND AD.document_i18n->? != '')",
                array(
                    "$.{$condition_language}",
                    "$.{$condition_language}.title.value",
                    "$.{$condition_language}.title.value",
                    "$.{$condition_language}.title.value"
                )
            );
        }

        $data = $this->db->query_one();

        return $data ? $data : null;
    } */

    // ADDITIONAL QUERIES
    /* function get_countries(){
        $sql = "SELECT
                    *
                FROM {$this->port_country_table}";

        return $this->db->query_all($sql);
    } */

    /* function get_groups(){
        $sql = "SELECT
                    *
                FROM {$this->user_groups_table}
                WHERE gr_type IN ('Buyer','Seller','Shipper')";

        return $this->db->query_all($sql);
    } */

    /* function get_user_by_token($token){
        $sql = "SELECT
                    u.*,
                    ug.gr_name,
                    ug.gr_type,
                    ug.stamp_pic
                FROM {$this->users_table} u
                LEFT JOIN user_groups ug
                    ON ug.idgroup = u.user_group
                WHERE u.accreditation_token = ?";

		$user = $this->db->query_one($sql, array($token));
        return $user;
    } */

    /* function get_user($id_user = 0){
        $this->db->select("u.*, ug.gr_name, ug.gr_type, ug.stamp_pic");
        $this->db->from("{$this->users_table} u");
        $this->db->join("{$this->user_groups_table} ug", "ug.idgroup = u.user_group", "inner");
        $this->db->where("u.idu", $id_user);

		$result = $this->db->get_one();
        return !empty($result) ? $result : array();
    } */

    /* function update_user($id_user, $data){
        $this->db->where('idu', $id_user);

		return $this->db->update($this->users_table, $data);
    } */

    /* function get_user_main_company($id_user){
        $sql = "SELECT
                    *
                FROM {$this->company_base_table}
                WHERE id_user = ?
                    AND parent_company = 0";

		return $this->db->query_one($sql, array($id_user));
    } */

    /* function update_user_main_company($id_user, $data){
        $this->db->where('id_user', $id_user);
        $this->db->where('parent_company', 0);

		return $this->db->update($this->company_base_table, $data);
    } */

    /* function update_user_companies($id_user, $data){
        $this->db->where('id_user', $id_user);

		return $this->db->update($this->company_base_table, $data);
    } */

    /* function update_shipper_company($id_user, $data){
        $this->db->where('id_user', $id_user);

		return $this->db->update($this->orders_shippers_table, $data);
    } */

    /* function get_users_companies($users_list){
        $users_list = getArrayFromString($users_list);
        $sql = "SELECT
                    *
                FROM {$this->company_base_table}
                WHERE id_user IN (" . implode(',', array_fill(0, count($users_list), '?')) . ")
                    AND parent_company = 0";

        return $this->db->query_all($sql, $users_list);
    } */

    /* function get_shippers_companies($users_list){
        $users_list = getArrayFromString($users_list);
        $sql = "SELECT
                    *
                FROM {$this->orders_shippers_table}
                WHERE id_user IN (" . implode(',', array_fill(0, count($users_list), '?')) . ")";

        return $this->db->query_all($sql, $users_list);
    } */

	/* function get_company_industries($id_user) {
        $sql = 'SELECT
                    GROUP_CONCAT(cri.id_industry) as industries
				FROM company_relation_industry cri
                INNER JOIN company_base cb
                    ON cri.id_company = cb.id_company
                WHERE cb.id_user = ?
                    AND cb.type_company = "company"';

		return $this->db->query_one($sql, array($id_user));
	} */
}
