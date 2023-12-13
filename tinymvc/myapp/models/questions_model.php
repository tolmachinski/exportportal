<?php

/**
 * questions_Model.php
 * model for questions
 * @author Cravciuc Andrei
 */

class Questions_Model extends TinyMVC_Model
{
    private $questions_table = "questions";
    private $questions_cats_table = "questions_categories";
    private $questions_cats_i18n_table = "questions_categories_i18n";
    private $answers_table = "questions_answers";
    private $question_helpful_table = "questions_answers_help";
    private $comments_table = "questions_answers_comments";

    private $port_countries = 'port_country';

    /* QUESTIONS */
    public function setQuestion($data = array()) {
        return empty($data) ? false : $this->db->insert($this->questions_table, $data);
    }

    public function getQuestion($id_question = 0) {
        $sql = "SELECT  q.*, pcnt.country,
						u.fname, u.lname, u.email, u.user_photo,
                        IF(q.lang != 'en' AND cat_i18n.id_category_i18n IS NOT NULL, cat_i18n.title_cat , cat.title_cat) as title_cat
				FROM {$this->questions_table} q
				INNER JOIN users u ON q.id_user = u.idu
			    INNER JOIN {$this->questions_cats_table} cat ON q.id_category = cat.idcat
                LEFT JOIN {$this->questions_cats_i18n_table} cat_i18n on cat.idcat = cat_i18n.id_category AND cat_i18n.lang_category = q.lang
				INNER JOIN port_country pcnt ON q.id_country = pcnt.id
				WHERE id_question = ?";
        return $this->db->query_one($sql, array($id_question));
    }

    public function updateQuestion($id_question = 0, $data = array()) {
        $this->db->where('id_question', $id_question);
        return empty($data) ? false : $this->db->update($this->questions_table, $data);
    }

    public function deleteQuestion($ids = '') {
        if (empty($ids)) {
            return false;
        }

        $ids = getArrayFromString($ids);

        $this->db->in('id_question', $ids);
        return $this->db->delete($this->questions_table);
    }

    public function modifyCounterAnswer($id_question = 0, $value = 0) {
        $sql = "UPDATE {$this->questions_table}
				SET count_answers = count_answers + ?
				WHERE id_question = ? ";

        return $this->db->query($sql, array($value, $id_question));
    }

    public function modifySearched($ids = '', $value = 1) {
        if (empty($ids)) {
            return;
        }

        $params = $ids = getArrayFromString($ids);
        array_unshift($params, $value);

        $sql = "UPDATE {$this->questions_table}
				SET was_searched = was_searched + ?
				WHERE id_question IN(" . implode(',', array_fill(0, count($ids), '?')) . ")";

        return $this->db->query($sql, $params);
    }

    public function moderateQuestion($ids = '') {
        if (empty($ids)) {
            return false;
        }

        $ids = getArrayFromString($ids);

        $sql = "UPDATE {$this->questions_table}
                SET moderated = 1
                WHERE id_question IN (" . implode(',', array_fill(0, count($ids), '?')) . ")";

        return $this->db->query($sql, $ids);
    }

    function get_questions_last_id() {
        $sql = "SELECT id_question
				FROM {$this->questions_table}
				ORDER BY id_question DESC
				LIMIT 0,1";

        return $this->db->query_one($sql)['id_question'] ?: 0;
    }

    function get_count_new_questions($id_question = 0) {
        $sql = "SELECT COUNT(*) as counter
				FROM {$this->questions_table}
				WHERE id_question > ? ";

        return $this->db->query_one($sql, [$id_question])['counter'];
    }

    public function getSimpleQuestions($conditions = array()) {
        $limit = 0;
        $columns = " * ";

        extract($conditions);

        $where = $params = [];

        if (isset($id_question)) {
            $where[] = " id_question = ? ";
            $params[] = $id_question;
            $limit = 1;
        }

        if (isset($questions_list)) {
            $questions_list = getArrayFromString($questions_list);
            $where[] = " id_question IN (" . implode(',', array_fill(0, count($questions_list), '?')) . ")";
            array_push($params, ...$questions_list);
            $limit = count($questions_list);
        }

        $sql = "SELECT {$columns} FROM {$this->questions_table} ";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " LIMIT " . $limit;

        return $this->db->query_all($sql, $params);
    }

    public function getQuestions($conditions = array()) {
        $page = 1;
        $per_p = 20;
        $order_by = "date_question DESC";
        extract($conditions);

        $where = $params = [];

		if(!empty($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
        }

        if (isset($id_question)) {
            $where[] = "id_question = ?";
            $params[] = $id_question;
        } elseif (isset($questions_list)) {
            $questions_list = getArrayFromString($questions_list);
            $where[] = "id_question IN (" . implode(',', array_fill(0, count($questions_list), '?')) . ")";
            array_push($params, ...$questions_list);
        }

        if (isset($id_user)) {
            $where[] = "id_user = ?";
            $params[] = $id_user;
        }

        if (isset($id_category)) {
            $where[] = "id_category = ?";
            $params[] = $id_category;
        }

        if (isset($id_country)) {
            $where[] = "id_country = ?";
            $params[] = $id_country;
        }

        if (isset($moderated)) {
            $where[] = "q.moderated = ?";
            $params[] = $moderated;
        }

        if (isset($answered)) {
            $where[] = $answered ? "count_answers > 0" : "count_answers = 0";
        }

        if (isset($added_start)) {
            $where[] = "DATE(date_question) >= ?";
            $params[] = $added_start;
        }

        if (isset($added_finish)) {
            $where[] = "DATE(date_question) <= ?";
            $params[] = $added_finish;
        }

        if (isset($lang_question)) {
            $where[] = " q.lang = ?";
            $params[] = $lang_question;
        }

        if (!empty($keywords)) {
            $words = explode(' ', $keywords);
            $s_word = [];

            foreach ($words as $word) {
                if (strlen($word) > 3) {
                    $searchParam = '%' . $word . '%';
                    $s_word[] = "(q.title_question LIKE ? OR q.text_question LIKE ? OR u.fname LIKE ? OR u.lname LIKE ? OR pcnt.country LIKE ? OR cat.title_cat LIKE ?)";

                    array_push($params, ...array_fill(0, 6, $searchParam));
                }
            }

            if (!empty($s_word)) {
                $where[] = '(' . implode(' AND ', $s_word) . ')';
            }
        }

        $sql = "SELECT  q.*,
						u.fname, u.lname, u.user_group, u.user_type, u.`status`, CONCAT(u.fname, ' ', u.lname) as full_name, u.user_photo,
						pcnt.country,
						cat.title_cat ";

        if (isset($with_last_answer) && $with_last_answer === true) {
            $sql .= ",
            (SELECT qa.date_answer FROM questions_answers as qa WHERE qa.id_question = q.id_question ORDER BY qa.date_answer DESC LIMIT 1) as last_date_answer,
	        (SELECT CONCAT(au.fname, ' ', au.lname)
		        FROM questions_answers as qau
                INNER JOIN users au ON au.idu = qau.id_user
		        WHERE qau.id_question = q.id_question
		        ORDER BY qau.id_answer DESC
		        LIMIT 1
	        ) as last_answerer_full_name ";
        }

        if (isset($count_comments)) {
            $sql .= ", (SELECT SUM(count_comments) FROM questions_answers as qa WHERE qa.id_question=q.id_question) as nr ";
        }

        $sql .= "
            FROM {$this->questions_table} q
            INNER JOIN users u ON q.id_user = u.idu
            INNER JOIN {$this->questions_cats_table} cat ON q.id_category = cat.idcat
            INNER JOIN port_country pcnt ON q.id_country = pcnt.id
        ";


        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= " ORDER BY $order_by ";

        if (isset($limit)) {
            $sql .= " LIMIT $limit";
        } else {
            if (!isset($count)) {
                $count = $this->countAnswers($conditions);
            }

            $pages = ceil($count / $per_p);
            if ($page > $pages) {
                $page = $pages;
            }

            $start = ($page - 1) * $per_p;
            if ($start < 0) {
                $start = 0;
            }

            $sql .= " LIMIT $start, $per_p";
        }

        return $this->db->query_all($sql, $params);
    }

    public function countQuestions($conditions = array()) {
        extract($conditions);

        $where = $params = [];

        if (isset($id_question)) {
            $where[] = " id_question = ? ";
            $params[] = $id_question;
        }

        if (isset($id_user)) {
            $where[] = " id_user = ? ";
            $params[] = $id_user;
        }

        if (isset($id_category)) {
            $where[] = " id_category = ? ";
            $params[] = $id_category;
        }

        if (isset($id_country)) {
            $where[] = " id_country = ? ";
            $params[] = $id_country;
        }

        if (isset($moderated)) {
            $where[] = " q.moderated = ? ";
            $params[] = $moderated;
        }

        if (isset($answered)) {
            $where[] = $answered ? " count_answers > 0 " : " count_answers = 0 ";
        }

        if (isset($added_start)) {
            $where[] = " DATE(date_question) >= ?";
            $params[] = $added_start;
        }

        if (isset($added_finish)) {
            $where[] = " DATE(date_question) <= ?";
            $params[] = $added_finish;
        }

        if (isset($country)) {
            $where[] = " pcnt.country = ?";
            $params[] = $country;
        }

        if (isset($category)) {
            $where[] = " cat.title_cat = ?";
            $params[] = $category;
        }

        if (isset($lang_question)) {
            $where[] = " q.lang = ?";
            $params[] = $lang_question;
        }

        /* simple search by keywords */
        if (!empty($keywords)) {
            $words = explode(' ', $keywords);
            $s_word = [];

            foreach ($words as $word) {
                if (strlen($word) > 3) {
                    $searchParam = '%' . $word . '%';
                    $s_word[] = "(q.title_question LIKE ? OR q.text_question LIKE ? OR u.fname LIKE ? OR u.lname LIKE ? OR pcnt.country LIKE ? OR cat.title_cat LIKE ?)";

                    array_push($params, ...array_fill(0, 6, $searchParam));
                }
            }

            if (!empty($s_word)) {
                $where[] = '(' . implode(' AND ', $s_word) . ')';
            }
        }

        $sql = "SELECT COUNT(*) as counter
			    FROM {$this->questions_table} q
			    INNER JOIN users u ON q.id_user = u.idu
			    INNER JOIN {$this->questions_cats_table} cat ON q.id_category = cat.idcat
			    INNER JOIN port_country pcnt ON q.id_country = pcnt.id";

        if (count($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        return $this->db->query_one($sql, $params)['counter'];
    }

    public function countAnsweredQuestions() {
        $sql = "SELECT COUNT(*) as counter
			    FROM {$this->questions_table}
			    WHERE count_answers > 0";

        return $this->db->query_one($sql)['counter'];
    }

    /* QUESTIONS ANSWERS */
    public function setAnswer($data = array()) {
        if (empty($data)) {
            return false;
        }

        $id_answer = $this->db->insert($this->answers_table, $data);

        if ($id_answer) {
            $this->modifyCounterAnswer($data['id_question'], 1);
            return $id_answer;
        }

        return false;
    }

    public function updateAnswer($id_answer = 0, $data = array()) {
        if (empty($data)) {
            return false;
        }

        $this->db->where('id_answer', $id_answer);
        return $this->db->update($this->answers_table, $data);
    }

    public function deleteAnswer($id_answer = 0) {
        $answer = $this->getAnswer($id_answer);
        if (empty($answer)) {
            return false;
        }

        $this->modifyCounterAnswer($answer['id_question'], -1);

        $this->db->where('id_answer', $id_answer);
        return $this->db->delete($this->answers_table);
    }

    public function deleteAnswers($ids = '') {
        if ($ids == '') {
            return false;
        }

        $this->db->in('id_answer', $ids);
        return $this->db->delete($this->answers_table);
    }

    public function moderateAnswer($ids = '') {
        if ($ids == '') {
            return false;
        }

        $ids = getArrayFromString($ids);

        $sql = "UPDATE {$this->answers_table}
				SET moderated = 1
				WHERE id_answer IN (" . implode(',', array_fill(0, count($ids), '?')) . ")";

        return $this->db->query($sql, $ids);
    }

    public function modifyCounterComments($id_answer = 0, $value = 0) {
        $sql = "UPDATE {$this->answers_table}
				SET count_comments = count_comments + ?
				WHERE id_answer = ?";

        return $this->db->query($sql, array($value, $id_answer));
    }

    public function set_helpful($data = array()) {
        return empty($data) ? false : $this->db->insert($this->question_helpful_table, $data);
    }

    public function exist_helpful($id_answer = 0, $id_user = 0) {
        $sql = "SELECT COUNT(*) as counter
				FROM {$this->question_helpful_table}
				WHERE id_answer = ? AND id_user = ?";

        return $this->db->query_one($sql, [$id_answer, $id_user])['counter'];
    }

    public function update_helpful($id_answer = 0, $data = array(), $id_user = 0) {
        if (empty($data)) {
            return false;
        }

        $this->db->where('id_answer', $id_answer);
        $this->db->where('id_user', $id_user);
        return $this->db->update($this->question_helpful_table, $data);
    }

    public function remove_helpful($id_answer, $id_user) {
        $this->db->where('id_answer', $id_answer);
        $this->db->where('id_user', $id_user);

        return $this->db->delete($this->question_helpful_table);
    }

    public function modify_counter_helpfull($id_answer = 0, $columns) {
        $sql = "UPDATE {$this->answers_table}
                SET ";
        $set = array();
        foreach ($columns as $column => $sign){
            $set[] = $column . " = " . $column . " " . $sign . " 1 ";
        }

        if(empty($set)){
            return false;
        }

        $sql .= implode(',', $set) . " WHERE id_answer = ?";
        return $this->db->query($sql, array($id_answer));
    }

    public function get_helpful_by_answer($list_answer = '', $id_user = 0) {
        if($list_answer == ''){
            return false;
        }

        $list_answer = getArrayFromString($list_answer);

        $this->db->select('id_answer, help');
        $this->db->where('id_user', $id_user);
        $this->db->in('id_answer', $list_answer);

        $records = $this->db->get($this->question_helpful_table);

        return array_column((array) $records, 'help', 'id_answer');
    }

    function get_answers_last_id() {
        $sql = "SELECT id_answer
				FROM {$this->answers_table}
				ORDER BY id_answer DESC
				LIMIT 0,1";

        return $this->db->query_one($sql)['id_answer'] ?: 0;
    }

    function get_count_new_answers($id_answer = 0) {
        $sql = "SELECT COUNT(*) as counter
				FROM {$this->answers_table}
				WHERE id_answer > ? ";

        return $this->db->query_one($sql, array($id_answer))['counter'];
    }

    public function getAnswer($id_answer = 0) {
        $sql = "SELECT  a.*,
						u.user_photo, u.fname, u.lname, u.email, u.user_group
				FROM {$this->answers_table} a
				INNER JOIN users u ON a.id_user = u.idu
				WHERE id_answer = ?";
        return $this->db->query_one($sql, array($id_answer));
    }

    public function get_simple_answer($id_answer = 0) {
        $sql = "SELECT *
				FROM {$this->answers_table}
				WHERE id_answer = ?";

        return $this->db->query_one($sql, array($id_answer));
    }

    public function getSimpleAnswers($conditions = array()) {
        $limit = 0;
        $columns = " * ";

        extract($conditions);

        $where = $params = [];

        if (isset($id_answer)) {
            $where[] = " id_answer = ? ";
            $params[] = $id_answer;
        } elseif (isset($answers_list)) {
            $answers_list = getArrayFromString($answers_list);
            $where[] = " id_answer IN (" . implode(',', array_fill(0, count($answers_list), '?')) . ")";
            array_push($params, ...$answers_list);
            $limit = count($answers_list);
        }

        if (isset($id_question)) {
            $where[] = " id_question = ? ";
            $params[] = $id_question;
        }

        if (isset($id_user)) {
            $where[] = " id_user = ? ";
            $params[] = $id_user;
        }

        $sql = "SELECT {$columns} FROM {$this->answers_table}";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        if ($limit > 0) {
            $sql .= " LIMIT " . $limit;
        }

        return $this->db->query_all($sql, $params);
    }

    public function getAnswers($conditions = array()) {
        $order_by = "date_answer DESC";
        extract($conditions);

        $where = $params = [];

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
        }

        if (isset($id_answer)) {
            $where[] = " a.id_answer = ? ";
            $params[] = $id_answer;
        }

        if (isset($id_user)) {
            $where[] = " a.id_user = ? ";
            $params[] = $id_user;
        }

        if (isset($id_question)) {
            $where[] = " a.id_question = ? ";
            $params[] = $id_question;
        }

        if (isset($id_questions)) {
            $id_questions = getArrayFromString($id_questions);
            $where[] = " a.id_question IN( " . implode(',', array_fill(0, count($id_questions), '?')) . ")";
            array_push($params, ...$id_questions);
        }

        if (isset($moderated)) {
            $where[] = " a.moderated = ? ";
            $params[] = $moderated;
        }

        if (isset($id_category)) {
            $where[] = " id_category = ? ";
            $params[] = $id_category;
        }


        /* simple search by keywords */
        if (!empty($keywords)) {
            $words = explode(" ", $keywords);
            foreach ($words as $word) {
                if (strlen($word) > 3) {
                    $s_word[] = " (a.title_answer LIKE ? OR a.text_answer LIKE ? OR u.fname LIKE ? OR u.lname LIKE ?)";
                    array_push($params, ...array_fill(0, 4, '%' . $word . '%'));
                }
            }

            if (!empty($s_word)) {
                $where[] = " (" . implode(" AND ", $s_word) . ")";
            }
        }

        if (isset($added_start)) {
            $where[] = " DATE(date_answer)>=?";
            $params[] = $added_start;
        }

        if (isset($added_finish)) {
            $where[] = " DATE(date_answer)<=?";
            $params[] = $added_finish;
        }

        $sql = "SELECT  a.*,
						u.fname, u.lname, u.user_group, u.user_type,
						u.user_photo, CONCAT(u.fname, ' ', u.lname) as full_name, u.`status`, u.idu ";


        if (isset($details_question)) {
            $sql .= " ,q.title_question, cat.title_cat, pcnt.country ";
        }

        $sql .= "FROM $this->answers_table a
			    INNER JOIN users u ON a.id_user = u.idu";

        if (isset($details_question)) {
            $sql .= " LEFT JOIN " . $this->questions_table . " as q ON q.id_question=a.id_question " . " LEFT JOIN port_country pcnt ON q.id_country = pcnt.id" . " LEFT JOIN " . $this->questions_cats_table . " cat ON q.id_category = cat.idcat ";
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND", $where);
        }

        $sql .= ' ORDER BY ' . $order_by;

        if (!empty($limit)) {
            $sql .= ' LIMIT ' . $limit;
        }

        return $this->db->query_all($sql, $params);
    }

    public function countAnswers($conditions = array()) {
        extract($conditions);

        $where = $params = [];

        if (isset($id_answer)) {
            $where[] = " id_answer = ? ";
            $params[] = $id_answer;
        }

        if (isset($id_user)) {
            $where[] = " a.id_user = ? ";
            $params[] = $id_user;
        }

        if (isset($id_question)) {
            $where[] = " id_question = ? ";
            $params[] = $id_question;
        }

        if (isset($moderated)) {
            $where[] = " a.moderated = ? ";
            $params[] = $moderated;
        }

        /* simple search by keywords */
        if (!empty($keywords)) {
            $words = explode(" ", $keywords);
            foreach ($words as $word) {
                if (strlen($word) > 3) {
                    $s_word[] = " (a.title_answer LIKE ? OR a.text_answer LIKE ? OR u.fname LIKE ? OR u.lname LIKE ?)";
                    array_fill($params, ...array_fill(0, 4, '%' . $word . '%'));
                }
            }

            if (!empty($s_word)) {
                $where[] = " (" . implode(" AND ", $s_word) . ")";
            }
        }

        if (isset($added_start)) {
            $where[] = " DATE(date_answer)>=?";
            $params[] = $added_start;
        }

        if (isset($added_finish)) {
            $where[] = " DATE(date_answer)<=?";
            $params[] = $added_finish;
        }


        if (isset($category)) {
            $where[] = " cat.title_cat=?";
            $params[] = $category;
        }

        $sql = "SELECT COUNT(*) as counter
			    FROM $this->answers_table a
			    INNER JOIN users u ON a.id_user = u.idu";
        if (isset($details_question)) {
            $sql .= " LEFT JOIN $this->questions_table as q ON q.id_question=a.id_question
							  LEFT JOIN port_country pcnt ON q.id_country = pcnt.id
							  LEFT JOIN $this->questions_cats_table cat ON q.id_category = cat.idcat ";
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND", $where);
        }

        return $this->db->query_one($sql, $params)['counter'];
    }

    /* QUESTIONS ANSWERS COMMENTS */
    public function setComment($data = array()) {
        if (empty($data)) {
            return false;
        }

        if ($this->db->insert($this->comments_table, $data)) {
            $id = $this->db->last_insert_id();
            $this->modifyCounterComments($data['id_answer'], 1);
            return $id;
        }

        return false;
    }

    public function updateComment($id_comment = 0, $data = array()) {
        if (empty($data)) {
            return false;
        }

        $this->db->where('id_comment', $id_comment);
        return $this->db->update($this->comments_table, $data);
    }

    public function deleteComment($id_comment = 0) {
        $comment = $this->getComment($id_comment);
        if(empty($comment)){
            return false;
        }

        $this->modifyCounterComments($comment['id_answer'], -1);

        $this->db->where('id_comment', $id_comment);
        return $this->db->delete($this->comments_table);
    }

    public function deleteComments($ids = '') {
        if ($ids == '') {
            return false;
        }

        $this->db->in('id_comment', $ids);
        return $this->db->delete($this->comments_table);
    }

    public function deleteCommentsByAnswer($id_answers = '') {
        if ($id_answers == '') {
            return false;
        }

        $this->db->in('id_answer', $id_answers);
        return $this->db->delete($this->comments_table);
    }

    public function moderateComment($ids = '') {
        if ($ids == '') {
            return false;
        }

        $this->db->in('id_comment', $ids);
        return $this->db->update($this->comments_table, array('moderated' => 1));
    }

    function get_comments_last_id() {
        $sql = "SELECT id_comment
				FROM {$this->comments_table}
				ORDER BY id_comment DESC
				LIMIT 1";

        return $this->db->query_one($sql)['id_comment'] ?: 0;
    }

    function get_count_new_comments($id_comment = 0) {
        $sql = "SELECT COUNT(*) as counter
				FROM {$this->comments_table}
				WHERE id_comment > ? ";

        return $this->db->query_one($sql, array($id_comment))['counter'];
    }

    public function getComment($id_comment = 0) {
        $sql = "SELECT 	c.*,
						u.fname, u.lname, u.email, u.user_group, u.user_type, u.user_photo, u.`status`, CONCAT(u.fname, ' ', u.lname) as full_name,
						pc.country
				FROM {$this->comments_table} c
				INNER JOIN users u ON c.id_user = u.idu
				LEFT JOIN port_country pc ON u.country = pc.id
				WHERE id_comment = ?";
        return $this->db->query_one($sql, array($id_comment));
    }

    public function get_simple_comment($id, $columns = '*') {
        $sql = "SELECT {$columns}
				FROM {$this->comments_table}
				WHERE id_comment = ?";
        return $this->db->query_one($sql, array($id));
    }

    public function getSimpleComments($conditions = array()) {
        $limit = 0;
        $columns = " * ";

        extract($conditions);

        $where = $params = [];

        if(isset($id_comment)) {
            $where[] = " id_comment = ? ";
            $params[] = $id_comment;
        }

        if(isset($comments_list)) {
            $comments_list = getArrayFromString($comments_list);
            $where[] = " id_comment IN (" . implode(',', array_fill(0, count($comments_list), '?')) . ")";
            array_push($params, ...$comments_list);
        }

        $sql = "SELECT {$columns} FROM {$this->comments_table} ";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        if (!empty($limit)) {
            $sql .= " LIMIT {$limit} ";
        }

        return $this->db->query_all($sql, $params);
    }

    public function getComments($conditions) {
        $page = 1;
        $per_p = 20;
        $start = 0;
        $order_by = "date_comment ASC";

        extract($conditions);

        $where = $params = [];

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
        }

        if (isset($id_comment)) {
            $where[] = " id_comment = ? ";
            $params[] = $id_comment;
        }

        if (isset($id_answer)) {
            if (is_array($id_answer)) {
                $where[] = ' c.id_answer IN (' . implode(',', array_fill(0, count($id_answer), '?')) . ')';
                array_push($params, ...$id_answer);
            } else{
                $where[] = " c.id_answer = ? ";
                $params[] = $id_answer;
            }
        }

        if (isset($id_user)) {
            $where[] = " c.id_user = ? ";
            $params[] = $id_user;
        }

        if (isset($reply_to_comment)) {
            $where[] = " reply_to_comment = ? ";
            $params[] = $reply_to_comment;
        }

        if (isset($moderated)) {
            $where[] = " c.moderated = ? ";
            $params[] = $moderated;
        }

        /* simple search by keywords */
        if (!empty($keywords)) {
            $words = explode(" ", $keywords);
            foreach ($words as $word) {
                $s_word[] = " (c.text_comment LIKE ? OR u.fname LIKE ? OR u.lname LIKE ?)";
                array_push($params, ...array_fill(0, 3, '%' . $word . '%'));
            }

            $where[] = " (" . implode(" AND ", $s_word) . ")";
        }

        if (isset($added_start)) {
            $where[] = " DATE(date_comment)>=?";
            $params[] = $added_start;
        }

        if (isset($added_finish)) {
            $where[] = " DATE(date_comment)<=?";
            $params[] = $added_finish;
        }

        $sql = "SELECT 	c.*,
						u.fname, u.lname, u.user_group, u.user_type, u.user_photo,u.`status`, CONCAT(u.fname, ' ', u.lname) as full_name,
						pc.country ";

        if (isset($details_answer)) {
            $sql .= " ,a.title_answer, a.id_question ";
        }

        $sql .= "FROM $this->comments_table c
                INNER JOIN users u ON c.id_user = u.idu
                LEFT JOIN port_country pc ON u.country = pc.id";


        if (isset($details_answer)) {
            $sql .= " LEFT JOIN " . $this->answers_table . " as a ON a.id_answer=c.id_answer";
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND", $where);
        }

        if ($page != 1) {
            if (!isset($count))
                $count = $this->countAnswers($conditions);

            $pages = ceil($count / $per_p);
            if ($page > $pages)
                $page = $pages;

            $start = ($page - 1) * $per_p;

            if ($start < 0)
                $start = 0;
        }

        $sql .= ' ORDER BY ' . $order_by;

        if (isset($limit)) {
            $sql .= ' LIMIT ' . $limit;
        } else {
            $sql .= " LIMIT " . $start . "," . $per_p;
        }

        return $this->db->query_all($sql, $params);
    }

    public function countComments($conditions) {
        extract($conditions);

        $where = $params = [];

        if (isset($id_comment)) {
            $where[] = " id_comment = ? ";
            $params[] = $id_comment;
        }

        if (isset($id_answer)) {
            $where[] = " c.id_answer = ? ";
            $params[] = $id_answer;
        }

        if (isset($id_question)) {
            $where[] = " id_question = ? ";
            $params[] = $id_question;
        }

        if (isset($id_user)) {
            $where[] = " c.id_user = ? ";
            $params[] = $id_user;
        }

        if (isset($reply_to_comment)) {
            $where[] = " reply_to_comment = ? ";
            $params[] = $reply_to_comment;
        }

        if (isset($moderated)) {
            $where[] = " c.moderated = ? ";
            $params[] = $moderated;
        }

        /* simple search by keywords */
        if (!empty($keywords)) {
            $words = explode(" ", $keywords);
            foreach ($words as $word) {
                $s_word[] = " (c.text_comment LIKE ? OR u.fname LIKE ? OR u.lname LIKE ?)";
                array_push($params, ...array_fill(0, 3, '%' . $word . '%'));
            }
            $where[] = " (" . implode(" AND ", $s_word) . ")";
        }

        if (isset($added_start)) {
            $where[] = " DATE(date_comment)>=?";
            $params[] = $added_start;
        }

        if (isset($added_finish)) {
            $where[] = " DATE(date_comment)<=?";
            $params[] = $added_finish;
        }

        $sql = "SELECT COUNT(*) as counter
				FROM $this->comments_table c
			    INNER JOIN users u ON c.id_user = u.idu";


        if (isset($details_answer)) {
            $sql .= " LEFT JOIN " . $this->answers_table . " as a ON a.id_answer=c.id_answer";
        }

        if (isset($id_question)) {
            $sql .= " INNER JOIN " . $this->answers_table . " as a ON a.id_answer=c.id_answer";
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND", $where);
        }

        return $this->db->query_one($sql, $params)['counter'];
    }

    // QUESTIONS CATEGORIES
    public function setCategory($data = array()) {
        return empty($data) ? false : $this->db->insert($this->questions_cats_table, $data);
    }

    public function setCategory_i18n($data = array()) {
        return empty($data) ? false : $this->db->insert($this->questions_cats_i18n_table, $data);
    }

    public function getCategory($id_category = 0) {
        $sql = "SELECT *
                FROM {$this->questions_cats_table}
                WHERE idcat = ? ";
        return $this->db->query_one($sql, array($id_category));
    }

    public function getCategory_i18n($conditions = array()){
		if (empty($conditions)) {
			return false;
		}

		extract($conditions);

        $where = $params = [];

		if(isset($id_category_i18n)){
			$where[] = " c_i18n.id_category_i18n = ? ";
			$params[] = $id_category_i18n;
		}

		if(isset($id_category)){
			$where[] = " c_i18n.id_category = ? ";
			$params[] = $id_category;
		}

		if(isset($lang_category)){
			$where[] = " c_i18n.lang_category = ? ";
			$params[] = $lang_category;
		}

		$sql = "SELECT c_i18n.*, c.idcat, c.translations_data, c.visible_cat
				FROM {$this->questions_cats_i18n_table} c_i18n
				LEFT JOIN {$this->questions_cats_table} c ON c_i18n.id_category = c.idcat
				WHERE " . implode(" AND ", $where);

		return $this->db->query_one($sql, $params);
	}

    public function existCategory($condition = array()) {
        extract($condition);

        $where = $params = [];

        if (isset($idcat)) {
            $where[] = " idcat = ? ";
            $params[] = $idcat;
        }

        if (isset($title_cat)) {
            $where[] = " LOWER(title_cat) = ? ";
            $params[] = strtolower($title_cat);
        }

        $sql = "SELECT idcat
                FROM {$this->questions_cats_table}
                WHERE " . implode(" AND ", $where);

        return $this->db->query_one($sql)['idcat'] ?: false;
    }

    public function getCategories($conditions = array()) {
        $where = $params = [];

        if (isset($visible)) {
            $where[] = " visible_cat = ?";
            $params[] = $visible;
        }

        $sql = "SELECT * FROM {$this->questions_cats_table}";

        if (!empty($where)){
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY title_cat ASC";
        return $this->db->query_all($sql, $params);
    }

    public function getCategories_i18n($conditions = array()) {
        $order_by = " {$this->questions_cats_i18n_table}.title_cat ASC ";
		$lang_category = __SITE_LANG;
        $use_lang = true;

        extract($conditions);

        $where = $params = [];

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		if($use_lang){
			$where[] = " {$this->questions_cats_i18n_table}.lang_category = ?";
			$params[] = $lang_category;
		}

		if(isset($id_category)){
			$where[] = " {$this->questions_cats_i18n_table}.id_category = ?";
			$params[] = $id_category;
		}

        if (isset($visible)) {
            $where[] = " {$this->questions_cats_table}.visible_cat = ?";
            $params[] = $visible;
        }

        $sql = "SELECT {$this->questions_cats_i18n_table}.*, {$this->questions_cats_table}.idcat, {$this->questions_cats_table}.translations_data, {$this->questions_cats_table}.visible_cat
                FROM {$this->questions_cats_i18n_table}
                INNER JOIN {$this->questions_cats_table} ON {$this->questions_cats_table}.idcat = {$this->questions_cats_i18n_table}.id_category";

        if (!empty($where)){
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY {$order_by}";
        return $this->db->query_all($sql, $params);
    }

    public function get_categories($conditions = array()) {
        extract($conditions);

        $where = $params = [];

        if (isset($sort_by)) {
            foreach ($sort_by as $sort_item) {
                $sort_item = explode('-', $sort_item);
                $multi_order_by[] = $sort_item[0] . ' ' . $sort_item[1];
            }

            $order_by = implode(',', $multi_order_by);
        }

        if (isset($visible)) {
            $where[] = " visible_cat = ? ";
            $params[] = $visible;
        }

        if (isset($keywords)) {
            $where[] = " MATCH (title_cat) AGAINST (?) ";
            $params[] = $keywords;
        }

        $sql = "SELECT * FROM {$this->questions_cats_table} ";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY {$order_by} LIMIT {$limit}";

        return $this->db->query_all($sql, $params);
    }

    public function get_categories_count($conditions = array()) {
        extract($conditions);

        $where = $params = [];

        if (isset($visible)) {
            $where[] = " visible_cat = ? ";
            $params[] = $visible;
        }

        if (isset($keywords)) {
            $where[] = " MATCH (title_cat) AGAINST (?) ";
            $params[] = $keywords;
        }

        if (isset($on_main_page)) {
            $where[] = "on_main_page = ? ";
            $params[] = $on_main_page;
        }

        $sql = "SELECT COUNT(*) as counter FROM {$this->questions_cats_table} ";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        return $this->db->query_one($sql, $params)['counter'];
    }

    public function updateCategory($id_category = 0, $data = array()) {
        $this->db->where('idcat', $id_category);
        return $this->db->update($this->questions_cats_table, $data);
    }

    public function updateCategory_i18n($id_category_i18n = 0, $data = array()){
		$this->db->where('id_category_i18n', $id_category_i18n);
		return $this->db->update($this->questions_cats_i18n_table, $data);
	}

    public function deleteCategory($id_category = 0) {
        $this->db->where('idcat', $id_category);
        return $this->db->delete($this->questions_cats_table);
    }

    public function deleteCategory_i18n($conditions = array()) {
        if(empty($conditions)){
            return false;
        }

        if(isset($id_category)){
            $this->db->where('id_category', $id_category);
        }

        if(isset($id_category_i18n)){
            $this->db->where('id_category_i18n', $id_category_i18n);
        }

        if(isset($lang_category)){
            $this->db->where('lang_category', $lang_category);
        }

        return $this->db->delete($this->questions_cats_i18n_table);
    }

    public function get_countries_by_questions() {
        $sql = "SELECT DISTINCT id_country, country
                FROM {$this->questions_table} as q
                INNER JOIN {$this->port_countries} as c ON q.id_country = c.id";
        return $this->db->query_all($sql);
    }


    function increment_views($id, $views) {
        $this->db->where('id_question', $id);
        return $this->db->update($this->questions_table, array('views' => $views));
    }

    public function get_countries_langs() {
        $this->db->select("lang_category");
        $this->db->from("{$this->questions_cats_i18n_table}");
        $this->db->groupby("lang_category");
        $result = $this->db->get();

        if(!empty($result)){
            $result = array_column($result, 'lang_category');
            $result[] = 'en';
        } else{
            $result = array('en');
        }

        return $result;
    }
}
