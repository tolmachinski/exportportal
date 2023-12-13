<?php


class Bad_Words_Model extends TinyMVC_Model{

    private $bad_words_table = 'bad_words';

    public function get_bad_words($conditions) {
        $where = array();
        $params = array();
        $select = '*';
        $order_by = 'language ASC';
        $group_by = 'id';
        $start = 0;
        $per_p = 10;
        extract($conditions);

        if (isset($sort_by)) {
            $order_by = implode(',', $sort_by);
        }

        if (isset($id)) {
            $where[] = 'id = ?';
            $params[] = $id;
        }

        if (isset($keywords)) {
            $where[] = " word LIKE ? ";
            $params[] = '%' . $keywords . '%';
        }

        if (isset($language)) {
            $where[] = " language = ? ";
            $params[] = strtolower($language);
        }

        $sql = "
            SELECT $select
            FROM {$this->bad_words_table} bw
            LEFT JOIN translations_languages tl ON tl.lang_iso2 = bw.`language`
        ";

        if (count($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " GROUP BY $group_by ";
        $sql .= " ORDER BY $order_by ";

        if ($per_p) {
            $start = (int) $start;
            $per_p = (int) $per_p;
            $sql .= " LIMIT $start, $per_p ";
        }

        return isset($id) ? $this->db->query_one($sql, $params) : $this->db->query_all($sql, $params);
    }


    public function get_bad_words_count($conditions) {
        $where = array();
        $params = array();

        extract($conditions);

        if (isset($id)) {
            $where[] = 'id = ?';
            $params[] = $id;
        }

        if (isset($keywords)) {
            $where[] = " (word LIKE ? OR language LIKE ?) ";
            array_push($params, ...array_fill(0, 2, '%' . $keywords . '%'));
        }

        if (isset($language)) {
            $where[] = " language = ? ";
            $params[] = strtolower($language);
        }

        $sql = "
            SELECT COUNT(DISTINCT `language`) as counter
            FROM {$this->bad_words_table}
        ";

        if (count($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $temp = $this->db->query_one($sql, $params);

        return $temp['counter'];
    }


    public function delete_by_language_and_word($language, $word) {
        $this->db->where('language', $language);
        $this->db->where('word', $word);
        return $this->db->delete($this->bad_words_table);
    }


    public function insert($data) {
        return $this->db->insert($this->bad_words_table, $data);
    }


    public function is_clean($text, $language, $strict = false) {
        return count($this->check_bad_words($text, $language, $strict)) == 0;
    }


    public function check_bad_words($text, $language, $strict = false) {
        $dictionary = $this->get_bad_words(array(
            'language' => $language,
            'per_p' => false
        ));

        $words = explode(' ', $text);

        return array_map(function ($value) {
            return array(
                'language' => $value['language'],
                'word' => strtolower($value['word']),
            );
        }, array_filter($dictionary, function ($value) use ($words, $text, $strict) {
            if ($strict) {
                return substr(strtolower($text), strtolower($value['word'])) !== false;
            }

            return in_array($value['word'], $words);
        }));
    }
}
