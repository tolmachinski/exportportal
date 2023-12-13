<?php

/**
 * itemquestion_model.php
 * item's questions system model
 * @author Litra Andrei
 */
class ItemQuestions_Model extends TinyMVC_Model {

    // hold the current controller instance
    private $obj;
    private $items_table = "items";
    private $questions_table = "item_questions";
    private $users_table = "users";
    private $questions_category_table = "item_questions_category";
    private $questions_helpful_table = "item_questions_user_helpful";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

    public function setQuestion($data) {
		if (!count($data))
			return false;
		$this->db->insert($this->questions_table, $data);
		return $this->db->last_insert_id();
    }

	function get_items_questions_last_id(){
		$sql = "SELECT id_q
				FROM item_questions
				ORDER BY id_q DESC
				LIMIT 0,1";

		$rez = $this->db->query_one($sql);

		if(!empty($rez['id_q']))
			return $rez['id_q'];
		else
			return 0;
	}

	function get_count_new_items_questions($id_question){
		$sql = "SELECT COUNT(*) as counter
				FROM item_questions
				WHERE id_q > ? ";

		$rez = $this->db->query_one($sql, array($id_question));
		return $rez['counter'];
	}

	function get_simple_question($id_question) {
		$this->db->select("iq.*, i.id_seller");
		$this->db->from("{$this->questions_table} iq");
		$this->db->join("{$this->items_table} i", "iq.id_item = i.id", 'inner');
		$this->db->where('iq.id_q', $id_question);

		$question = $this->db->get_one();

		return empty($question) ? array() : $question;
	}

    public function getQuestion($id_q) {
		$sql = "SELECT  q.*,
						CONCAT(u.fname, ' ', u.lname) as questionername, u.logged, u.user_photo,
						ug.gr_name as quest_group,
						pc.country,
						it.title, it.id_seller,
						ic.name, ic.p_or_m, qc.name_category
				FROM $this->questions_table q
				LEFT JOIN users u ON q.id_questioner = u.idu
				LEFT JOIN user_groups ug ON ug.idgroup = u.user_group
				LEFT JOIN port_country pc ON u.country = pc.id
				INNER JOIN $this->items_table it ON q.id_item = it.id
				INNER JOIN $this->questions_category_table qc ON qc.id_category = q.id_category
				INNER JOIN item_category ic ON it.id_cat = ic.category_id
				WHERE id_q = ?";
		return $this->db->query_one($sql, array($id_q));
    }

    public function isMyQuestion($id_q, $id_user) {
	    $sql = "SELECT COUNT(*) as counter
                FROM " . $this->questions_table . "
                WHERE id_q = ? AND id_questioner = ?";
	    $counter = $this->db->query_one($sql, array($id_q, $id_user));

        return $counter['counter'];
    }

    public function isQuestionForUser($id_q, $id_user) {
	    $sql = "SELECT COUNT(*) as counter
                FROM " . $this->questions_table . " iq
                LEFT JOIN " . $this->items_table . " it ON iq.id_item = it.id
                WHERE iq.id_q = ? AND it.id_seller = ?";
	    $counter = $this->db->query_one($sql, array($id_q, $id_user));

        return $counter['counter'];
    }

    public function getQuestionForReply($idQuestion)
    {
        $this->db->select("u.idu, u.email, CONCAT(u.fname, ' ', u.lname) as user_name, q.notify, q.title_question, q.question");
        $this->db->from($this->questions_table . " q");
        $this->db->join("{$this->users_table} u", "q.id_questioner = u.idu", 'left');
        $this->db->where("q.id_q", $idQuestion);

        return $this->db->query_one();
    }

    public function get_questions($conditions = array()) {
		$page = 1;
		$status = "";
		$order_by = "q.question_date DESC";
		$where = array();
		$params = array();
		$rel = "";

		extract($conditions);

		if (isset($item)) {
			$where[] = " q.id_item = ? ";
			$params[] = $item;
		}

		if (isset($added_after_time)) {
			$where[] = " q.question_date > ? ";
			$params[] = $added_after_time;
		}

		if (!empty($status)) {
			$where[] = " q.status = ? ";
			$params[] = $status;
		}

		if (isset($seller)) {
			$where[] = " it.id_seller = ? ";
			$params[] = $seller;
		}

		if (isset($questioner)) {
			$where[] = " q.id_questioner = ? ";
			$params[] = $questioner;
		}

		if (isset($added_start)) {
			$where[] = " DATE(question_date)>=?";
			$params[] = $added_start;
		}

		if (isset($added_finish)) {
			$where[] = " DATE(question_date)<=?";
			$params[] = $added_finish;
        }

		if (isset($replied_start)) {
			$where[] = " DATE(reply_date)>=?";
			$params[] = $replied_start;
		}

		if (isset($replied_finish)) {
			$where[] = " DATE(reply_date)<=?";
			$params[] = $replied_finish;
		}

		if (isset($moderated)) {
			$where[] = " q.status = ? ";
			$params[] = $moderated;
		}

		if (isset($question_number)) {
			$where[] = " q.id_q = ? ";
			$params[] = $question_number;
		}

		if (isset($replied)) {
			if($replied == 'yes')
			   $where[] = " q.reply != '' ";
			else
			   $where[] = " q.reply = '' ";
		}

		if (isset($sort_by)) {
			foreach ($sort_by as $sort_item) {
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0] . ' ' . $sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		if (isset($order)) {
			$str = explode("_", $order);
			$ord = $str[0];

			switch ($ord) {
				case 'questionername': $order_by = " questionername";break;
				case 'title': $order_by = " q.title_question";break;
				case 'item': $order_by = " it.title";break;
				case 'date': $order_by = " q.question_date";break;
				default: $order_by = " q.question_date";break;
			}
			$order_by .= " " . $str[1];
		}

		if (!empty($keywords)) {
			if(str_word_count_utf8($keywords) > 1){
				$order_by = $order_by . ", REL DESC";
				$where[] = " MATCH(q.title_question, q.question, q.reply) AGAINST ( ? ) ";
				$params[] = $keywords;
				$rel = " , MATCH (q.title_question, q.question, q.reply) AGAINST ( ? ) as REL ";
                array_unshift($params, $keywords);
			} else{
				$where[] = " (q.title_question LIKE ? OR q.question LIKE ? OR q.reply LIKE ?)";
                array_push($params, ...array_fill(0, 3, '%' . $keywords . '%'));
			}
		}

		$sql = "SELECT  q.*,
						CONCAT(uq.fname, ' ', uq.lname) as questionername, uq.logged, uq.user_photo, uq.status as user_status,
						ug.gr_name as quest_group, ug.idgroup,
						pc.country,
						it.title, iph.photo_name, it.id_seller,
						ic.name, ic.p_or_m, ic.breadcrumbs as item_breadcrumbs,
                        qc.name_category, cb.index_name, cb.name_company, cb.id_company, cb.type_company,
						CONCAT(u.fname, ' ', u.lname) as sell_fullname, u.status as sell_user_status $rel
				FROM $this->questions_table q
				LEFT JOIN users uq ON q.id_questioner = uq.idu
				LEFT JOIN user_groups ug ON ug.idgroup = uq.user_group
				LEFT JOIN port_country pc ON uq.country = pc.id
				INNER JOIN $this->items_table it ON q.id_item = it.id
				INNER JOIN $this->questions_category_table qc ON qc.id_category = q.id_category
				INNER JOIN item_category ic ON it.id_cat = ic.category_id
				LEFT JOIN item_photo iph ON q.id_item = iph.sale_id AND iph.main_photo = 1
				INNER JOIN company_base cb ON  cb.id_user=it.id_seller
				LEFT JOIN users u ON u.idu = it.id_seller";

		if (count($where))
			$sql .= " WHERE " . implode(" AND", $where);

		$sql .= " GROUP BY id_q ";
		$sql .= " ORDER BY " . $order_by;

		if (isset($per_p)) {
			if ( ! isset($count)) {
				$count = $this->count_questions($conditions);
			}

			$start = ($page - 1) * $per_p;

			$sql .= " LIMIT {$start},{$per_p}";
		}

		if(isset($limit))
			$sql .= " LIMIT " .$limit;

		return $this->db->query_all($sql, $params);
    }

    public function counter_answers_question($id_item) {

		$sql = "SELECT COUNT(*) as counter
					FROM " . $this->questions_table . " q
					INNER JOIN " . $this->items_table . " it ON q.id_item = it.id
					WHERE q.reply != '' AND q.id_item = ?";
		$res = $this->db->query_one($sql, [$id_item]);

		return $res['counter'];
    }

    public function count_questions($conditions = []) {
		extract($conditions);

        $where = $params = [];

		if (!empty($status)) {
			$where[] = " q.status = ? ";
			$params[] = $status;
		}

		if (isset($seller)) {
			$where[] = " it.id_seller = ? ";
			$params[] = $seller;
		}

		if (isset($questioner)) {
			$where[] = " q.id_questioner = ? ";
			$params[] = $questioner;
		}

		if (isset($item)) {
			$where[] = " q.id_item = ? ";
			$params[] = $item;
		}

		if (isset($added_start)) {
			$where[] = " DATE(question_date)>=?";
			$params[] = $added_start;
		}

		if (isset($added_finish)) {
			$where[] = " DATE(question_date)<=?";
			$params[] = $added_finish;
        }

        if (isset($replied_start)) {
			$where[] = " DATE(reply_date)>=?";
			$params[] = $replied_start;
		}

		if (isset($replied_finish)) {
			$where[] = " DATE(reply_date)<=?";
			$params[] = $replied_finish;
		}

		if (isset($moderated)) {
			$where[] = " q.status = ? ";
			$params[] = $moderated;
		}

		if (isset($question_number)) {
			$where[] = " q.id_q = ? ";
			$params[] = $question_number;
		}

		if (isset($replied)) {
			if($replied == 'yes')
			   $where[] = " q.reply != '' ";
			else
			   $where[] = " q.reply = '' ";
		}

		if (isset($keywords)) {

			$words = explode(' ', $keywords);
			if(count($words) > 1){
				$where[] = " MATCH(title_question,question, reply) AGAINST (?)";
                $params[] = $keywords;
			} else{
				$where[] = " (q.title_question LIKE ? OR q.question LIKE ? OR q.reply LIKE ?)";
                array_push($params, ...array_fill(0, 3, '%' . $keywords . '%'));
			}
		}

		$sql = "SELECT COUNT(*) as counter
					FROM " . $this->questions_table . " q
					INNER JOIN " . $this->items_table . " it ON q.id_item = it.id";
		if (count($where))
			$sql .= " WHERE " . implode(" AND", $where);

		$res = $this->db->query_one($sql, $params);

		return $res['counter'];
    }

    public function updateQuestion($id_q, $data) {
        $this->db->where('id_q', $id_q);

	    return $this->db->update($this->questions_table, $data);
    }

    public function modify_counter_helpfull($id, $columns) {
        $sql = "UPDATE " . $this->questions_table . " SET ";
        foreach ($columns as $column => $sign)
            $set[] = $column . " = " . $column . " " . $sign . " 1 ";

        $sql .= implode(',', $set) . " WHERE id_q = ?";

        return $this->db->query($sql, array($id));
    }

    public function set_helpful($data) {
        if (!count($data))
            return false;
        $this->db->insert($this->questions_helpful_table, $data);

        return $this->db->last_insert_id();
    }

    public function exist_helpful($id_question, $id_user) {
        $sql = "SELECT COUNT(*) as counter, help
                    FROM " . $this->questions_helpful_table . "
                    WHERE id_question = ? AND id_user = ?";

        return $this->db->query_one($sql, array($id_question, $id_user));
    }

    public function update_helpful($id_question, $data, $id_user) {
        $this->db->where('id_question = ? and id_user = ?', array($id_question, $id_user));

        return $this->db->update($this->questions_helpful_table, $data);
    }

    public function get_helpful_by_question($list_question, $id_user) {
        $params = $list_question = getArrayFromString($list_question);
        $params[] = $id_user;

        $sql = "	SELECT id_question, help
                        FROM " . $this->questions_helpful_table . "
                        WHERE id_question IN (" . implode(',', array_fill(0, count($list_question), '?')) . ") AND id_user = ?";

        $rez = $this->db->query_all($sql, $params);

        return array_column($rez ?: [], 'help', 'id_question');
    }

    public function delete_helpful($id_question) {
        $this->db->where('id_question', $id_question);

        return $this->db->delete($this->questions_helpful_table);
	}

	public function delete_user_helpful($id_question, $id_user) {
		$this->db->where('id_question', $id_question);
		$this->db->where('id_user', $id_user);

        return $this->db->delete($this->questions_helpful_table);
    }

    public function get_question_categories() {
        $sql = "SELECT * FROM " . $this->questions_category_table;

        return $this->db->query_all($sql);
    }

    public function delete_question($id_q) {
        $this->db->where('id_q', $id_q);

        return $this->db->delete($this->questions_table);
    }

    public function delete_questions($ids) {
        $ids = getArrayFromString($ids);
        $sql = "DELETE FROM " . $this->questions_table . " WHERE id_q IN (" . implode(',', array_fill(0, count($ids), '?')) . ")";

        return $this->db->query($sql, $ids);
    }

    public function moderateQuestions($ids) {
        $ids = getArrayFromString($ids);

        $sql = "UPDATE " . $this->questions_table . " SET status=\"moderated\" WHERE id_q IN(" . implode(',', array_fill(0, count($ids), '?')) . ")";

	    return $this->db->query($sql, $ids);
    }

	public function questions_owner($questions) {
        $questions = getArrayFromString($questions);

		$sql = "SELECT q.id_questioner, it.id_seller
				FROM item_questions q
				INNER JOIN " . $this->items_table . " it ON q.id_item = it.id
				WHERE id_q IN (" . implode(',', array_fill(0, count($questions), '?')) . ")";
		return $this->db->query_all($sql, $questions);
	}

	public function get_categories_question($conditions){
		$where = array();
        $params = array();
		$order_by = "id_category DESC";
		extract($conditions);

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		$sql = "SELECT *
                FROM " . $this->questions_category_table;

		if(count($where))
			$sql .= " WHERE " . implode(' AND ', $where);

		$sql .= " ORDER BY ".$order_by;

		$sql .= " LIMIT " . $limit ;

		return $this->db->query_all($sql, $params);
	}

	public function get_category_question($id_category){
		$sql = "SELECT *
                FROM {$this->questions_category_table}
                WHERE id_category = ?";

		return $this->db->query_one($sql, [$id_category]);
	}

	public function count_categories_question(){
		$sql = "SELECT COUNT(*) as counter
					FROM " . $this->questions_category_table;
		$rez = $this->db->query_one($sql);

		return $rez['counter'];
	}

	public function exist_category_question($condition){
        extract($condition);
        $where = $params = [];

        if(isset($id_category)){
            $where[] = " id_category = ? ";
            $params[] = $id_category;
        }

        if(isset($name_category)){
            $where[] = " LOWER(name_category) = ? ";
            $params[] = strtolower($name_category);
        }

        $sql = "SELECT id_category
                FROM " . $this->questions_category_table . "
                WHERE " . implode(" AND ", $where);

        $rez = $this->db->query_one($sql, $params);

        return $rez['id_category'] ?: false;
    }

	public function update_category_question($id_category, $data){
		$this->db->where('id_category', $id_category);
		return $this->db->update($this->questions_category_table, $data);
	}

	public function delete_category_question($id_category){
		$this->db->where('id_category', $id_category);
		return $this->db->delete($this->questions_category_table);
	}

	public function set_category_question($data){
        $this->db->insert($this->questions_category_table, $data);
        return $this->db->last_insert_id();
    }

    public function exists_all($ids)
    {
        $ids_list = array_filter($ids, function($id) { return null !== $id; });
        if(empty($ids_list)) {
            return false;
        }

        $this->db->select("COUNT(*) as `counter`");
        $this->db->from($this->questions_table);
        $this->db->in('id_q', $ids_list);
        $data = $this->db->query_one();

        return ($data['counter'] ?? 0) == count($ids_list);
    }

    public function exists($id)
    {
        if (null === $id) {
            false;
        }

        $this->db->where('id_q = ?', (int) $id);
        $this->db->limit(1);
        $question = $this->db->get_one($this->questions_table);

        return !empty($question);
    }
}
