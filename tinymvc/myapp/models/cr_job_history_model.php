<?php

class Cr_Job_History_Model extends TinyMVC_Model {
    private $user_job_history_table = 'user_job_history';

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);
    }

    public function delete_job($id_job){
		$this->db->where('id_job', $id_job);
		return $this->db->delete($this->user_job_history_table);
	}

    function my_job($id_job, $id_user){
        $sql = "SELECT *
                FROM $this->user_job_history_table
                WHERE id_job = ?";

        return $this->db->query_one($sql, array($id_job));
    }

    function get_job_histoy($id_job){
        $sql = "SELECT *
                FROM $this->user_job_history_table
                WHERE id_job = ?";

        return $this->db->query_one($sql, array($id_job));
    }

    function get_jobs_histoy($conditions){
        $order_by = " date_from ASC";
		$where = array();
		$params = array();
		$limit = 50;

		extract($conditions);

		if(isset($id_user)){
			$where[] = " id_user = ? ";
			$params[] = $id_user;
        }

        if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

        $sql = "SELECT *
                FROM $this->user_job_history_table";

        if(count($where))
            $sql .= " WHERE " . implode(" AND ", $where);

        $sql .= " ORDER BY " . $order_by;

        if(isset($limit)){
            $sql .= " LIMIT " . $limit ;
        } else{
            if(!isset($count))
                $count = $this->count_jobs_histoy($conditions);

            $pages = ceil($count/$per_p);

            if(!isset($start)){
                if ($page > $pages) $page = $pages;
                $start = ($page-1)*$per_p;

                if($start < 0) $start = 0;
            }

            $sql .= " LIMIT " . $start ;

            if($per_p > 0)
                $sql .= "," . $per_p;
        }

        return $this->db->query_all($sql, $params);
    }

    function count_jobs_histoy($conditions){
        $where = array();
        $params = array();

        extract($conditions);

		if(isset($id_user)){
			$where[] = " id_user = ? ";
			$params[] = $id_user;
		}

        $sql = "SELECT COUNT(*) as counter
                FROM $this->user_job_history_table";

            if(count($where))
            $sql .= " WHERE " . implode(" AND ", $where);

        $rez = $this->db->query_one($sql, $params);
        return $rez['counter'];
    }

    function insert_job_histoy($data){
        $this->db->insert($this->user_job_history_table, $data);
        return $this->db->last_insert_id();
    }


    function update_job_histoy($id_job, $data){
        $this->db->where('id_job', $id_job);
        return $this->db->update($this->user_job_history_table, $data);
    }
}
