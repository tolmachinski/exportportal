<?php
/**
 * banner_model.php
 *
 * banner model
 *
 * @author
 */

class Api_keys_Model extends TinyMVC_Model {
    var $obj;
    private $api_keys_table = "api_keys";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

    function get_api_keys($conditions = array()){

        $where = array();
        $params = array();

        extract($conditions);

        if(isset($date_from)){
            $where[] = " DATE(api_keys.registered) >= ? ";
            $params[] = $date_from;
        }
        if(isset($date_to)){
            $where[] = " DATE(api_keys.registered) <= ? ";
            $params[] = $date_to;
        }

        if(isset($search)){
            $where[] = " api_keys.api_key LIKE ? OR api_keys.domain LIKE ? OR api_keys.title_client LIKE ?";
            array_push($params, ...array_fill(0, 3, '%' . $search . '%'));
        }

        if (isset($moderated)) {
            $where[] = " api_keys.moderated = ? ";
            $params[] = $moderated;
        }

        if (isset($enable)) {
            $where[] = " api_keys.enable = ? ";
            $params[] = $enable;
        }

        if (isset($domain)) {
            $where[] = " api_keys.domain LIKE ? ";
            $params[] = '%' . $domain . '%';
        }


        $sql = "SELECT id_key, api_key, domain, title_client, description_client, registered, enable, moderated FROM " . $this->api_keys_table;

        if(count($where))
            $sql = $sql . ' WHERE ' . implode(' AND ' , $where);

        if (!empty($sort_by) && is_array($sort_by)) {
            $ordering = [];

            foreach ($sort_by as $column => $direction) {
                $ordering[] = $column . ' ' . $direction;
            }

            $sql .= ' ORDER BY ' . implode(', ', $ordering);
        }

        $sql .= ' LIMIT ' . (int) $start_from . ', ' . (int) $limit;

        return $this->db->query_all($sql, $params);
    }

    public function getCountApiKeys(array $conditions = array()): int
    {
        $this->db->select('count(*) AS counter');

        if (!empty($conditions['date_from'])) {
            $this->db->where('DATE(registered) >=', $conditions['date_from']);
        }

        if (!empty($conditions['date_to'])) {
            $this->db->where('DATE(registered) <=', $conditions['date_to']);
        }

        if (!empty($conditions['search'])) {
            $this->db->where_raw("api_key LIKE ? OR domain LIKE ? OR title_client LIKE ?", array_fill(0, 3, '%' . $conditions['search'] . '%'));
        }

        if (isset($conditions['moderated'])) {
            $this->db->where('moderated', (int) $conditions['moderated']);
        }

        if (isset($conditions['enable'])) {
            $this->db->where('enable', (int) $conditions['enable']);
        }

        if (!empty($conditions['domain'])) {
            $this->db->where_raw("domain LIKE ?", ['%' . $conditions['domain'] . '%']);
        }

        $result = $this->db->get_one($this->api_keys_table);

        return (int) $result['counter'];
    }

    function delete_api_key($id){
        $this->db->where('id_key', $id);
        return $this->db->delete('api_keys');
    }

    function change_visibility($id, $value){
        $this->db->where('id_key', $id);
        return $this->db->update('api_keys', array('enable' => $value));
    }

    function insert_api_key($results){
        return $this->db->insert('api_keys', $results);
    }

    function edit_api_key($data, $id){
        $this->db->where('id_key', $id);
        return $this->db->update('api_keys', $data);
    }

    function get_details($id){
        return $this->db->query_one("SELECT * FROM api_keys WHERE id_key = ?", [$id]);
    }

    function moderate_api_key($id){
        $this->db->where('id_key', $id);
        return $this->db->update('api_keys', array('moderated' => 1));
    }

    function get_count(){
        $temp = $this->db->query_one("SELECT COUNT(*) as counter FROM api_keys");
        return $temp['counter'];
    }

    function check_api_key($key){
        $sql = "SELECT COUNT(*) as counter " .
                "FROM api_keys " .
                " WHERE api_key = ? ".
                " AND enable = 1";
        $res = $this->db->query_one($sql, array($key));
        return $res['counter'];
    }

    function find_by_public_key($key){
        $sql = "SELECT ac.*, u.email
                FROM api_clients ac
                LEFT JOIN users u ON u.idu = ac.id_user
                WHERE public_key = ? ";
        return $this->db->query_one($sql, array($key));
    }

    function find_by_user($id){
        $sql = "SELECT ac.*, u.email
                FROM api_clients ac
                LEFT JOIN users u ON u.idu = ac.id_user
                WHERE id_user = ? ";
        return $this->db->query_one($sql, array($id));
    }

    function exist_user_api_registered($id_user){
        $sql = "SELECT COUNT(*) as counter
                FROM api_clients ac
                WHERE ac.id_user=?";
        $temp = $this->db->query_one($sql, array($id_user));
        return (bool) $temp['counter'];
    }

    function insert_api_client($insert){
        return $this->db->insert('api_clients', $insert);
    }

    function update_api_client($id, $update){
        $this->db->where('id_user', $id);
        return $this->db->update('api_clients', $update);
    }
}

