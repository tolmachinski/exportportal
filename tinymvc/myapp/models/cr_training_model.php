<?php

/**
 * cr_training_model.php
 *
 * company model
 *
 * @author Litra Andrei
 */
class Cr_Training_Model extends TinyMVC_Model {

    private $cr_trainings_cr_users_table = "cr_training_cr_user_table";
    private $cr_trainings_table = "cr_trainings";

    public function insert_training($insert) {
        if(empty($insert)) {
            return false;
        }

        return $this->db->insert($this->cr_trainings_table, $insert);
    }

    public function update_training($id = 0, $data = array()) {
        if (empty($data)){
            return false;
        }

        $this->db->where("id_training", $id);
        return $this->db->update($this->cr_trainings_table, $data);
    }

    public function delete_training($ids = "") {
        if (empty($ids)){
            return false;
        }

        $this->db->in("id_training", $ids);
        return $this->db->delete($this->cr_trainings_table);
    }

    public function get_training($id = 0) {
        $sql = "SELECT *
                FROM {$this->cr_trainings_table}
                WHERE id_training = ?";
        return $this->db->query_one($sql, array($id));
    }

    function get_trainings($conditions) {
        $where = array();
        $params = array();
        $joins = array();
        $order_by = "ct.training_date DESC";
        extract($conditions);

        if(isset($start_date_to)) {
            $where[] = "ct.training_start_date <= ? ";
            $params[] = $start_date_to;
        }

        if(isset($start_date_from)) {
            $where[] = "ct.training_start_date >= ? ";
            $params[] = $start_date_from;
        }

        if(isset($finish_date_to)) {
            $where[] = "ct.training_finish_date <= ? ";
            $params[] = $finish_date_to;
        }

        if(isset($finish_date_from)) {
            $where[] = "ct.training_finish_date >= ?  ";
            $params[] = $finish_date_from;
        }

        if(isset($date_to)) {
            $where[] = "ct.training_date <= ? ";
            $params[] = $date_to;
        }

        if(isset($date_from)) {
            $where[] = "ct.training_date >= ? ";
            $params[] = $date_from;
        }

        if(isset($type_filter)) {
            $where[] = "ct.training_type = ?";
            $params[] = $type_filter;
        }

        if(isset($id_user)) {
            $joins[] = " INNER JOIN {$this->cr_trainings_cr_users_table} ctu ON ct.id_training = ctu.id_training ";
            $where[] = "ctu.id_user = ?";
            $params[] = $id_user;
        }

        if(!empty($keywords)){
            $words = explode(" ", $keywords);
            foreach($words as $word){
                if(mb_strlen($word) > 3) {
                    $s_word[] =  " (ct.training_title LIKE ? OR ct.training_description LIKE ?) ";
                    array_push($params, ...array_fill(0, 2, '%' . $word . '%'));
                }
            }

            if(!empty($s_word)) {
                $where[] = " (". implode(" AND ", $s_word).")";
            }
        }

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode("-", $sort_item);
				$multi_order_by[] = $sort_item[0]." ".$sort_item[1];
			}

			$order_by = implode(",", $multi_order_by);
        }

        if (isset($id)) {
            $where[] = "ct.id_training = ?";
            $params[] = $id;
        }

        if (isset($trainings_list)) {
            $trainings_list = getArrayFromString($trainings_list);
            $where[] = "ct.id_training IN " . implode(',', array_fill(0, count($trainings_list), '?')) . ")";
            array_push($params, ...$trainings_list);
        }

        $sql = "SELECT ct.*
                FROM {$this->cr_trainings_table} ct";

        if(!empty($joins)){
            $sql .= implode(" ", $joins);
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY $order_by ";

        if(isset($start,$per_p)){
            $start = (int) $start;
            $per_p = (int) $per_p;
			$sql .= " LIMIT {$start}, {$per_p}";
		}

        return $this->db->query_all($sql, $params);
    }

    function count_trainings($conditions) {
        $where = array();
        $params = array();
        $joins = array();
        extract($conditions);

        if(isset($start_date_to)) {
            $where[] = "ct.training_start_date <= ? ";
            $params[] = $start_date_to;
        }

        if(isset($start_date_from)) {
            $where[] = "ct.training_start_date >= ?  ";
            $params[] = $start_date_from;
        }

        if(isset($finish_date_to)) {
            $where[] = "ct.training_finish_date <= ? ";
            $params[] = $finish_date_to;
        }

        if(isset($finish_date_from)) {
            $where[] = "ct.training_finish_date >= ?  ";
            $params[] = $finish_date_from;
        }

        if(isset($date_to)) {
            $where[] = "ct.training_date <= ? ";
            $params[] = $date_to;
        }

        if(isset($date_from)) {
            $where[] = "ct.training_date >= ?  ";
            $params[] = $date_from;
        }

        if(isset($type_filter)) {
            $where[] = "ct.training_type = ?";
            $params[] = $type_filter;
        }

        if(isset($id_user)) {
            $joins[] = " INNER JOIN {$this->cr_trainings_cr_users_table} ctu ON ct.id_training = ctu.id_training ";
            $where[] = "ctu.id_user = ?";
            $params[] = $id_user;
        }

        if(!empty($keywords)){
            $words = explode(" ", $keywords);
            foreach($words as $word){
                if(mb_strlen($word) > 3) {
                    $s_word[] =  " (ct.training_title LIKE ? OR ct.training_description LIKE ?) ";
                    array_push($params, ...array_fill(0, 2, '%' . $word . '%'));
                }
            }

            if(!empty($s_word)) {
                $where[] = " (". implode(" AND ", $s_word).")";
            }
        }

        if (isset($id)) {
            $where[] = "ct.id_training = ?";
            $params[] = $id;
        }

        if (isset($trainings_list)) {
            $trainings_list = getArrayFromString($trainings_list);
            $where[] = "ct.id_training IN (" . implode(',', array_fill(0, count($trainings_list), '?')) . ")";
            array_push($params, ...$trainings_list);
        }

        $sql = "SELECT
                COUNT(ct.id_training) as counter
                FROM {$this->cr_trainings_table} ct";

        if(!empty($joins)){
            $sql .= implode(" ", $joins);
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $rez = $this->db->query_one($sql, $params);

        return $rez["counter"];
    }

    public function get_assigned_users($id_training) {
        $sql = "SELECT * FROM {$this->cr_trainings_cr_users_table} WHERE id_training = ?";
        return $this->db->query_all($sql, array($id_training));
    }

    public function assign_users(array $users, $trainingId) {
        if (empty($users)) {
            return false;
        }

        $insertData = array_map(
            function ($userId) use ($trainingId) {
                return [
                    'id_training'   => $trainingId,
                    'id_user'       => (int) $userId
                ];
            },
            $users
        );

        return $this->db->insert_batch($this->cr_trainings_cr_users_table, $insertData);
    }

    public function un_assign_users($users, $id_training) {
        if (empty($users)) {
            return false;
        }

        $this->db->in('id_user', $users);
        $this->db->where('id_training', $id_training);

        return $this->db->delete($this->cr_trainings_cr_users_table);
    }
}
