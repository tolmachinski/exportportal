<?php

/**
 * email_message_model.php
 *
 *
 * @package       TinyMVC
 * @author        Boinitchi Ion
 */

class Email_Message_Model extends TinyMVC_Model{
    var $obj;
    private $email_support_category_table = "email_support_category";
    private $email_message_table = "email_message";
    private $user_groups_table = "user_groups";
    private $users_table = "users";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

    function get_email_messages($conditions = array()){
        $where   = array();
        $params  = array();
        $order_by= ' id_mess ASC ';

        extract($conditions);

        if (isset($sort_by)){
            foreach($sort_by as $sort_item){
                $sort_item = explode('-', $sort_item);
                $multi_order_by[] = $sort_item[0].' '.$sort_item[1];
            }

            $order_by = implode(',', $multi_order_by);
        }

        if (isset($keywords)){
            $where[] = " (email_from  LIKE ? OR mess_subject LIKE ?)";
            array_push($params, ...array_fill(0, 2, '%' . $keywords . '%'));
        }

        if (isset($id_cat_support)){
            $where[] = " $this->email_message_table.id_suppcat = ? ";
            $params[]= $id_cat_support;
        }

        if (isset($id_user_assign)){
            $where[] = " $this->email_message_table.attach_user = ? ";
            $params[]= $id_user_assign;
        }

        if (isset($email_account)){
            $where[] = " $this->email_message_table.email_account = ? ";
            $params[]= $email_account;
        }

        if (isset($date_start)){
            $where[] = " $this->email_message_table.mess_time >= ? ";
            $params[]= $date_start;
        }

        if (isset($date_end)){
            $where[] = " $this->email_message_table.mess_time <= ? ";
            $params[]= $date_end;
        }

        if (isset($status_record)){
            $where[] = " $this->email_message_table.status_record = ? ";
            $params[]= $status_record;
        }

        $sql = "SELECT $this->email_message_table.*,
                    us.fname,
                    us.lname,
                    ep.fname as ep_fname,
                    ep.lname as ep_lname,
                    $this->email_support_category_table.category,
                    $this->user_groups_table.gr_name
                FROM $this->email_message_table
                LEFT JOIN $this->users_table us ON us.idu = $this->email_message_table.id_user
                LEFT JOIN $this->users_table ep ON ep.idu = $this->email_message_table.attach_user
                LEFT JOIN $this->email_support_category_table ON $this->email_support_category_table.id_spcat = $this->email_message_table.id_suppcat
                LEFT JOIN $this->user_groups_table ON $this->user_groups_table.idgroup = ep.user_group";

        if(count($where))
            $sql .= " WHERE " . implode(" AND", $where);

        $sql .= " ORDER BY " . $order_by;
        if(isset($start) && isset($per_p)) {
            $start = (int) $start;
            $per_p = (int) $per_p;
            $sql .= ' LIMIT ' . $start . ',' . $per_p;
        }

        return $this->db->query_all($sql, $params);
    }

    function get_email_account(){
        $sql = "SELECT $this->email_message_table.email_account
                FROM $this->email_message_table
                WHERE $this->email_message_table.email_account != ''
                GROUP BY $this->email_message_table.email_account";

        return $this->db->query_all($sql);
    }

    function get_email_message($conditions = array()){
        $where = $params = array();
        extract($conditions);

        if(isset($id_record)){
            $where[] = " id_mess = ? ";
            $params[] = $id_record;
        }

        if (isset($id_user_assign)){
            $where[] = " $this->email_message_table.attach_user = ? ";
            $params[]= $id_user_assign;
        }

        $sql = "SELECT *
                FROM $this->email_message_table";

        if(count($where))
            $sql .= " WHERE " . implode(" AND ", $where);

        return $this->db->query_one($sql, $params);
    }

    function get_email_message_count($conditions = array()){
        $where = $params = array();
        extract($conditions);

        if (isset($keywords)){
            $where[] = " (email_from  LIKE ? OR mess_subject LIKE ?)";
            array_push($params, ...array_fill(0, 2, '%' . $keywords . '%'));
        }

        if (isset($id_cat_support)){
            $where[] = " $this->email_message_table.id_suppcat = ? ";
            $params[]= $id_cat_support;
        }

        if (isset($id_user_assign)){
            $where[] = " $this->email_message_table.attach_user = ? ";
            $params[]= $id_user_assign;
        }

        if (isset($email_account)){
            $where[] = " $this->email_message_table.email_account = ? ";
            $params[]= $email_account;
        }

        if (isset($date_start)){
            $where[] = " $this->email_message_table.mess_time >= ? ";
            $params[]= $date_start;
        }

        if (isset($date_end)){
            $where[] = " $this->email_message_table.mess_time <= ? ";
            $params[]= $date_end;
        }

        if (isset($status_record)){
            $where[] = " $this->email_message_table.status_record = ? ";
            $params[]= $status_record;
        }

        $sql = "SELECT COUNT(*) as counter
                FROM $this->email_message_table
                LEFT JOIN $this->users_table ON $this->users_table.idu = $this->email_message_table.id_user
                LEFT JOIN $this->email_support_category_table ON $this->email_support_category_table.id_spcat = $this->email_message_table.id_suppcat";

        if(count($where))
            $sql .= " WHERE " . implode(" AND", $where);

        $temp = $this->db->query_one($sql, $params);
        return $temp['counter'];
    }

    function set_email_message($data){
        $this->db->insert($this->email_message_table, $data);
        return $this->db->last_insert_id();
    }

    function set_email_message_batch($data){
        $this->db->insert_batch($this->email_message_table, $data);
        return $this->db->getAffectableRowsAmount();
    }

    function update_email_message($id_record, $data){
        $this->db->where('id_mess', $id_record);
        return $this->db->update($this->email_message_table, $data);
    }

    function check_email_message($conditions = array()){
        $where = $params = array();
        extract($conditions);

        if(isset($id_record)){
            $where[] = " id_mess = ? ";
            $params[] = $id_record;
        }

        if (isset($id_user_assign)){
            $where[] = " attach_user = ? ";
            $params[]= $id_user_assign;
        }

        if(isset($not_id_record)){
            $where[] = " id_mess != ? ";
            $params[] = $not_id_record;
        }

        $sql = "SELECT COUNT(*) as counter
                FROM $this->email_message_table";

        if(count($where))
            $sql .= " WHERE " . implode(" AND ", $where);

        $rez = $this->db->query_one($sql, $params);
        return $rez['counter'];
    }

    function delete_email_message($id_record){
        $this->db->where('id_mess', $id_record);
        return $this->db->delete($this->email_message_table);
    }
}
