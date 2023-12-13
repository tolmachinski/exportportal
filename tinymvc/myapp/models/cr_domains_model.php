<?php
/**
 * Cr_domains_Model.php
 *
 * model for countries representatives domains
 *
 * @author Cravciuc Andrei
 */

class Cr_domains_Model extends TinyMVC_Model {

    var $obj;
    private $cr_domains_table = "cr_domains";
    private $cr_domains_users_table = "cr_domains_users";
    private $countries_table = "port_country";

    public $path_folder = 'public/img/country_representative';

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

    public function set_cr_domain($data = array()) {
        if (empty($data))
            return false;

        $this->db->insert($this->cr_domains_table, $data);
        return $this->db->last_insert_id();
    }

    public function get_cr_domain($conditions = array()) {
        if(empty($conditions)){
            return false;
        }

        $where = array();
        $params = array();

        extract($conditions);

        if(isset($id_domain)){
            $where[] = "id_domain = ?";
            $params[] = $id_domain;
        }

        if(isset($id_country)){
            $where[] = "id_country = ?";
            $params[] = $id_country;
        }

        if(isset($country_alias)){
            $where[] = "country_alias = ?";
            $params[] = $country_alias;
        }

        $sql = "SELECT  crd.*,
                        pc.country, pc.country_alias
				FROM {$this->cr_domains_table} crd
                INNER JOIN {$this->countries_table} pc ON crd.id_country = pc.id";

        if(!empty($where)){
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " LIMIT 1 ";

        return $this->db->query_one($sql, $params);
    }

    public function update_cr_domain($id_domain = 0, $data = array()) {
        if (empty($data))
            return false;

        $this->db->where('id_domain', $id_domain);
        return $this->db->update($this->cr_domains_table, $data);
    }

    public function delete_cr_domain($id_domain = 0) {
        $this->db->where('id_domain', $id_domain);
        return $this->db->delete($this->cr_domains_table);
    }

    public function get_cr_domains($conditions = array()) {
        $where = array();
        $params = array();
        $order_by = "crd.domain_date_create DESC";

        extract($conditions);

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
        }

        if (isset($domains_list)) {
            $domains_list = getArrayFromString($domains_list);
            $where[] = " crd.id_domain IN (" . implode(',', array_fill(0, count($domains_list), '?')) . ") ";
            array_push($params, ...$domains_list);
        }

        if(isset($not_domains_list)){
            $not_domains_list = getArrayFromString($not_domains_list);
            $where[] = "id_domain NOT IN (" . implode(',', array_fill(0, count($not_domains_list), '?')) . ") ";
            array_push($params, ...$not_domains_list);
        }

        $sql = "SELECT  crd.*,
                        pc.country, pc.country_alias
                FROM {$this->cr_domains_table} crd
                INNER JOIN {$this->countries_table} pc ON crd.id_country = pc.id ";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY {$order_by} ";

        if(isset($limit)){
            $sql .= " LIMIT {$limit}";
        }

        return $this->db->query_all($sql, $params);
    }

    public function get_cr_domains_count($conditions = array()) {
        $where = array();
        $params = array();

        extract($conditions);

        if (isset($domains_list)) {
            $domains_list = getArrayFromString($domains_list);
            $where[] = " crd.id_domain IN (" . implode(',', array_fill(0, count($domains_list), '?')) . ") ";
            array_push($params, ...$domains_list);
        }

        if (isset($not_domains_list)) {
            $not_domains_list = getArrayFromString($not_domains_list);
            $where[] = "id_domain NOT IN (" . implode(',', array_fill(0, count($not_domains_list), '?')) . ") ";
            array_push($params, ...$not_domains_list);
        }

        $sql = "SELECT COUNT(*) as counter
			    FROM {$this->cr_domains_table} crd
                INNER JOIN {$this->countries_table} pc ON crd.id_country = pc.id";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $record = $this->db->query_one($sql, $params);

        return $record['counter'];
    }

    // DOMAIN's USERS RELATIONS
    public function set_user_domain_relation($insert = array()){
        if(empty($insert)){
            return false;
        }

        $this->db->insert($this->cr_domains_users_table, $insert);
        return $this->db->last_insert_id();
    }

    public function set_user_domains_relation($insert = array()){
        if(empty($insert)){
            return false;
        }

        $this->db->insert_batch($this->cr_domains_users_table, $insert);
        return $this->db->getAffectableRowsAmount();
    }

    public function get_user_domain_relation($id_user = 0){
        $sql = "SELECT  crd.*,
                        pc.country, pc.country_alias
                FROM {$this->cr_domains_users_table} crdu
                INNER JOIN {$this->cr_domains_table} crd ON crdu.id_domain = crd.id_domain
                INNER JOIN {$this->countries_table} pc ON crd.id_country = pc.id
                WHERE crdu.id_user = ?
                ORDER BY pc.country ASC
                LIMIT 1";
        return $this->db->query_one($sql, array($id_user));
    }

    public function get_user_domains_relation($id_user = 0){
        $sql = "SELECT  crd.*,
                        pc.country, pc.country_alias
                FROM {$this->cr_domains_users_table} crdu
                INNER JOIN {$this->cr_domains_table} crd ON crdu.id_domain = crd.id_domain
                INNER JOIN {$this->countries_table} pc ON crd.id_country = pc.id
                WHERE crdu.id_user = ?
                ORDER BY pc.country ASC";
        return $this->db->query_all($sql, array($id_user));
    }

    public function delete_user_domains_relation($id_user = 0){
        $this->db->where('id_user', $id_user);
        return $this->db->delete($this->cr_domains_users_table);
    }
}
