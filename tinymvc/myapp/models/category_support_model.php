<?php

/**
 * category_support_model.php
 *
 *
 * @package       TinyMVC
 * @author        Boinitchi Ion
 */

class Category_Support_Model extends TinyMVC_Model{
    var $obj;
    private $email_support_category_table = "email_support_category";
    private $users_table = "users";
    private $user_groups_table = "user_groups";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

    function get_staff_ep($conditions = array()){
        $user_type = 'ep_staff';
        extract($conditions);

        if(isset($user_type)){
            $where[] = " $this->users_table.user_type = ? ";
            $params[] = $user_type;
        }

        if(isset($id_users)){
            $id_users = getArrayFromString($id_users);
            $where[] = " $this->users_table.idu IN (" . implode(',', array_fill(0, count($id_users), '?')) . ") ";
            array_push($params, ...$id_users);
        }

        $sql = "SELECT idu, fname, lname, gr_name
                FROM $this->users_table
                JOIN $this->user_groups_table ON $this->user_groups_table.idgroup = $this->users_table.user_group";

        if(count($where))
            $sql .= " WHERE " . implode(" AND", $where);

        return $this->db->query_all($sql, $params);
    }

    function get_staff($conditions = array()){
        $where = $params = array();
        $user_type = 'ep_staff';
        extract($conditions);

        if(isset($user_type)){
            $where[] = " $this->users_table.user_type = ? ";
            $params[] = $user_type;
        }

        if(isset($id_user)){
            $where[] = " $this->users_table.idu = ? ";
            $params[] = $id_user;
        }

        $sql = "SELECT idu, fname, lname, gr_name
                FROM $this->users_table
                JOIN $this->user_groups_table ON $this->user_groups_table.idgroup = $this->users_table.user_group";

        if(count($where))
            $sql .= " WHERE " . implode(" AND", $where);

        return $this->db->query_one($sql, $params);
    }

    function get_support_categories($conditions = array()){
        $where = $params = array();
        $group_by= ' id_spcat ';
        $order_by= ' id_spcat ASC ';
        extract($conditions);

        if(isset($sort_by)){
            foreach($sort_by as $sort_item){
                $sort_item = explode('-', $sort_item);
                $multi_order_by[] = $sort_item[0].' '.$sort_item[1];
            }

            $order_by = implode(',', $multi_order_by);
        }

        if(isset($keywords)){
            $where[] = " $this->users_table.fname LIKE ? OR
                         $this->users_table.lname LIKE ? OR
                         $this->email_support_category_table.category LIKE ?";

            array_push($params, ...array_fill(0, 3, '%' . $keywords . '%'));
        }

        $sql = "SELECT
                    $this->email_support_category_table.*,
                    GROUP_CONCAT(CONCAT_WS(' ', $this->users_table.fname, $this->users_table.lname ), ' - ', $this->user_groups_table.gr_name SEPARATOR ', ') AS full_name,
                    GROUP_CONCAT($this->users_table.idu SEPARATOR ', ') AS ids_user,
                    GROUP_CONCAT($this->user_groups_table.gr_name SEPARATOR ', ') AS used_group
                FROM $this->email_support_category_table
                JOIN $this->users_table ON FIND_IN_SET($this->users_table.idu, $this->email_support_category_table.user_list)
                JOIN $this->user_groups_table ON $this->user_groups_table.idgroup = $this->users_table.user_group";

        if(count($where))
            $sql .= " WHERE " . implode(" AND", $where);

        $sql .= " GROUP BY " . $group_by;
        $sql .= " ORDER BY " . $order_by;

        if (isset($start) && isset($per_p)) {
            $start = (int) $start;
            $per_p = (int) $per_p;
            $sql .= ' LIMIT ' . $start . ',' . $per_p;
        }

        return $this->db->query_all($sql, $params);
    }

    function get_category_by_user($conditions = array()){
        $result = $listUsers = array();
        $idUser = '';
        $detailInfo = false;
        extract($conditions);

        if(isset($list_user))
            $listUsers = explode(',', $list_user);

        if(isset($id_user))
            $idUser = $id_user;

        $categories = $this->get_support_categories();
        foreach($categories as $numCat => $category){
            $idSuppCat = $category['id_spcat'];
            $idUsers   = explode(',' , $category['user_list']);

            if($detailInfo){
                $groupUsers= explode(',' , $category['used_group']);
                $nameUsers = explode(',' , $category['full_name']);
            }

            foreach($idUsers as $numUser => $userId){
                if(!empty($listUsers) && !in_array($userId, $listUsers)) continue;

                if(!empty($idUser) && $idUser != $userId) continue;

                if($detailInfo){
                    $fullName = explode('-', $nameUsers[$numUser]);

                    $result[$idSuppCat][$userId]['id_user']     = $userId;
                    $result[$idSuppCat][$userId]['category']= $category['category'];
                    $result[$idSuppCat][$userId]['used_group']  = $groupUsers[$numUser];
                    $result[$idSuppCat][$userId]['full_name']   = $fullName[0];
                }else{
                    if(!empty($listUsers)){
                        $result[$idSuppCat][$userId]['category'] = $category['category'];
                    }else{
                        $result[$idSuppCat]['category'] = $category['category'];
                    }
                }
            }
        }
        return $result;
    }

    function get_categories_name($conditions = array()){
        $group_by= ' id_spcat ';
        $order_by= ' id_spcat ASC ';
        $column  = ' * ';
        extract($conditions);

        $sql = "SELECT $column
                FROM $this->email_support_category_table";

        $sql .= " GROUP BY ".$group_by;
        $sql .= " ORDER BY ".$order_by;

        if(isset($start) && isset($per_p)) {
            $start = (int) $start;
            $per_p = (int) $per_p;
            $sql .= ' LIMIT ' . $start . ',' . $per_p;
        }

        return $this->db->query_all($sql);
    }

    function get_support_category($conditions = array()){
        $where = $params = array();
        extract($conditions);

        if(isset($id_record)){
            $where[] = " id_spcat = ? ";
            $params[] = $id_record;
        }

        $sql = "SELECT
                    $this->email_support_category_table.*,
                    GROUP_CONCAT(CONCAT_WS(' ', $this->users_table.fname, $this->users_table.lname ), ' - ', $this->user_groups_table.gr_name SEPARATOR ', ') AS full_name,
                    GROUP_CONCAT($this->users_table.idu SEPARATOR ', ') AS ids_user,
                    GROUP_CONCAT($this->user_groups_table.gr_name SEPARATOR ', ') AS user_groups
                FROM $this->email_support_category_table
                JOIN $this->users_table ON FIND_IN_SET($this->users_table.idu, $this->email_support_category_table.user_list)
                JOIN $this->user_groups_table ON $this->user_groups_table.idgroup = $this->users_table.user_group";

        if(count($where))
            $sql .= " WHERE " . implode(" AND ", $where);

        return $this->db->query_one($sql, $params);
    }

    function get_support_category_count($conditions = array()){
        $where = $params = array();
        extract($conditions);

        if(isset($keywords)){
            $where[] = " $this->users_table.fname LIKE ? OR
                         $this->users_table.lname LIKE ? OR
                         $this->email_support_category_table.category LIKE ?";

            array_push($params, ...array_fill(0, 3, '%' . $keywords . '%'));
        }

        $sql = "SELECT COUNT(DISTINCT $this->email_support_category_table.id_spcat) as total_record
                FROM $this->email_support_category_table
                JOIN $this->users_table ON FIND_IN_SET($this->users_table.idu, $this->email_support_category_table.user_list)";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND", $where);
        }

        $temp = $this->db->query_one($sql, $params);
        return $temp['total_record'];
    }

    function set_support_category($data){
        $this->db->insert($this->email_support_category_table, $data);
        return $this->db->last_insert_id();
    }

    function update_support_category($id_record, $data){
        $this->db->where('id_spcat', $id_record);
        return $this->db->update($this->email_support_category_table, $data);
    }

    function check_support_category($conditions = array()){
        $where = $params = array();
        extract($conditions);

        if(isset($id_record)){
            $where[] = " id_spcat = ? ";
            $params[] = $id_record;
        }

        if(isset($category)){
            $where[] = " category = ? ";
            $params[] = $category;
        }

        if(isset($not_id_record)){
            $where[] = " id_spcat != ? ";
            $params[] = $not_id_record;
        }

        $sql = "SELECT COUNT(DISTINCT $this->email_support_category_table.id_spcat) as total_record
                FROM $this->email_support_category_table
                JOIN $this->users_table ON FIND_IN_SET($this->users_table.idu, $this->email_support_category_table.user_list)";

        if(count($where))
            $sql .= " WHERE " . implode(" AND ", $where);

        $rez = $this->db->query_one($sql, $params);
        return $rez['total_record'];
    }

    function delete_support_category($id_record){
        $this->db->where('id_spcat', $id_record);
        return $this->db->delete($this->email_support_category_table);
    }
}
