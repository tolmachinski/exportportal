<?php
/**
 * user_statistic_model.php
 * users statistic model
 * @author  Litra Andrei
 * 
 * @deprecated v2.39.0 use User_statistics_Model
 */

class User_Statistic_Model extends TinyMVC_Model {

    private $statistic_table = 'user_statistic';
    private $user_groups = 'user_groups';

    //operation with statistic table
    public function add_statistic_column($column_name, $column_comments){
        $sql = "ALTER TABLE {$this->statistic_table}
                ADD COLUMN `" . $column_name . "`
                SMALLINT(5) UNSIGNED NOT NULL
                DEFAULT 0 COMMENT ?";
        return $this->db->query($sql, [$column_comments]);
    }

    public function update_statistic_column($column_name, $column_comments){
        $sql = "ALTER TABLE {$this->statistic_table}
                MODIFY `" . $column_name . "`
                SMALLINT(5) UNSIGNED NOT NULL
                DEFAULT 0 COMMENT ?";

        return $this->db->query($sql, [$column_comments]);
    }

    public function delete_statistic_column($column_name){
        $sql = "ALTER TABLE {$this->statistic_table}
                DROP COLUMN `" . $column_name . "`";
        $this->clear_statistic_column($column_name);
        return $this->db->query($sql);
    }

    public function init_user_statistic($id_user){
        $this->db->select('COUNT(*) as total_records');
        $this->db->where('id_user', $id_user);
        $this->db->limit(1);

        $statistic = $this->db->get_one($this->statistic_table);

        if(!$statistic['total_records']){
            return $this->db->insert($this->statistic_table, array('id_user' => $id_user));
        }
    }

    public function set_user_statistic($id_user, $statistic_array){
        $sql = "UPDATE {$this->statistic_table} SET ";

        foreach($statistic_array as $column => $value){
            if ($value < 0) {
                $updates[] = "{$column} = IF({$column} < - {$value}, 0, {$column} + {$value})";
            } else {
                $updates[] = "{$column} = {$column} + {$value}";
            }
        }

        $sql .= implode(', ', $updates);
        $sql .= " WHERE id_user = ? ";

        return $this->db->query($sql, array($id_user));
    }

    public function getStatistiColumns() {
        $rez = $this->db->getConnection()->executeQuery("SHOW FULL COLUMNS FROM {$this->statistic_table}")->fetchAllAssociative();//$this->db->query_all($sql);

        unset($rez[0]);
        return $rez;
    }

    public function get_statistic_columns($conditions)
    {
        extract($conditions);

        $params = [];
        $where = [
            " a.TABLE_NAME = 'user_statistic' ",
            " a.COLUMN_NAME <> 'id_user' "
        ];

        $sql = "SELECT a.COLUMN_NAME as Field, a.COLUMN_COMMENT as Comment
                FROM  information_schema.COLUMNS a";

        if(!empty($keywords)){
            $where[] = ' ( a.COLUMN_COMMENT LIKE ? OR a.COLUMN_NAME LIKE ? ) ';
            array_push($params, ...['%' . $keywords . '%', '%' . $keywords . '%']);
        }

        $sql .= " WHERE " . implode(" AND", $where);

        if (isset($order_by)) {
            $sql .= " ORDER BY {$order_by}";
        }

        if (isset($start, $per_p)) {
            $start = (int) $start;
            $per_p = (int) $per_p;

            $sql .= " LIMIT {$start}, {$per_p}";
        }

        return $this->db->query_all($sql, $params);
    }

    public function get_statistic_columns_count($conditions) {
        $params = [];
        $where = array(" a.TABLE_NAME = 'user_statistic' ", " a.COLUMN_NAME <> 'id_user' ");

        extract($conditions);

        $sql = "SELECT COUNT(*) as counter FROM  information_schema.COLUMNS a ";

        if(!empty($keywords)){
            $where[] = ' ( a.COLUMN_COMMENT LIKE ? OR a.COLUMN_NAME LIKE ?) ';
            array_push($params, ...['%' . $keywords . '%', '%' . $keywords . '%']);
        }

        $sql .= " WHERE " . implode(" AND ", $where);

        return $this->db->query_one($sql, $params)['counter'];
    }

    //operation with statistic columns in user's groups table
    public function disable_statistic_column($user_group,$column_name) {
        $sql = "UPDATE {$this->user_groups}
                SET statistic_columns = (
                    CASE
                        WHEN LOCATE('," . $column_name . "' , statistic_columns) THEN REPLACE(statistic_columns, '," . $column_name . "', '')
                        WHEN LOCATE('" . $column_name . ",' , statistic_columns) THEN REPLACE(statistic_columns, '" . $column_name . ",', '')
                        WHEN LOCATE('" . $column_name . "' , statistic_columns) THEN REPLACE(statistic_columns, '" . $column_name . "', '')
                        ELSE statistic_columns
                    END)
                WHERE idgroup = ?
                LIMIT 1";
        return $this->db->query($sql, array($user_group));
    }

    public function enable_statistic_column($user_group,$column_name) {
        $sql = "UPDATE " .$this->user_groups . "
                SET statistic_columns = (IF (statistic_columns = '', '" . $column_name . "', (CONCAT_WS(',', statistic_columns, '" . $column_name . "'))))
                WHERE idgroup = ?
                LIMIT 1";
        return $this->db->query($sql, array($user_group));
    }

    public function clear_statistic_column($column_name){
        $sql = "UPDATE {$this->user_groups}
                SET statistic_columns = (
                    CASE
                        WHEN LOCATE('," . $column_name . "' , statistic_columns) THEN REPLACE(statistic_columns, '," . $column_name . "', '')
                        WHEN LOCATE('" . $column_name . ",' , statistic_columns) THEN REPLACE(statistic_columns, '" . $column_name . ",', '')
                        WHEN LOCATE('" . $column_name . "' , statistic_columns) THEN REPLACE(statistic_columns, '" . $column_name . "', '')
                        ELSE statistic_columns
                    END)";
        return $this->db->query($sql);
    }

    public function get_user_statistic_column($group) {
        $sql = "SELECT ug.statistic_columns
                FROM user_groups ug
                WHERE ug.idgroup = ?";
        return $this->db->query_one($sql, array($group))['statistic_columns'];
    }

    public function get_user_statistic($user, $group) {
        $columns = $this->get_user_statistic_column($group);

        if (empty($columns)) {
            return;
        }

        $sql = "SELECT {$columns}
                FROM {$this->statistic_table}
                WHERE id_user = ? " ;
        return $this->db->query_one($sql, array($user));
    }

    public function get_user_statistic_simple($user, $columns = "*") {
        $sql = "SELECT $columns
                FROM $this->statistic_table
                WHERE id_user = ? " ;
        return $this->db->query_one($sql, array($user));
    }

    function get_detail_stat($column){
        if (empty($column)) {
            return [];
        }

        $sql = "SELECT a.COLUMN_NAME as col, a.COLUMN_COMMENT as com
                FROM  information_schema.COLUMNS a
                WHERE a.TABLE_NAME = 'user_statistic' AND a.COLUMN_NAME = ?";

        return $this->db->query_one($sql, array($column));
    }

    public function get_detail_statistic($user, $group)
    {
        $columns = $this->get_user_statistic_column($group);

        if (empty($columns)) {
            return [];
        }

        $columnList = getArrayFromString($columns);

        $sql = "SELECT a.COLUMN_NAME as col, a.COLUMN_COMMENT as com
                FROM  information_schema.COLUMNS a
                WHERE a.TABLE_NAME = 'user_statistic' AND a.COLUMN_NAME IN(" . implode(',', array_fill(0, count($columnList), '?')) . ')';

        $table_info = arrayByKey($this->db->query_all($sql, $columnList), 'col');

        $sql = "SELECT {$columns}
                FROM {$this->statistic_table}
                WHERE id_user = ?";
        $user_stat = $this->db->query_one($sql, [$user]);

        if (empty($user_stat)) {
            return [];
        }

        $result = [];
        foreach ($user_stat as $row_name => $one_stat) {
            $result[] = ['description' => $table_info[$row_name]['com'], 'value' => $one_stat];
        }

        return $result;
    }

    function exist_user_statistic($id_user){
        $this->db->select('COUNT(*) as counter');
        $this->db->where('id_user', $id_user);
        return $this->db->get_one($this->statistic_table)['counter'];
    }

    public function set_users_statistic($users = array()){
        $sql_prefix = "UPDATE " . $this->statistic_table . " SET ";

        $users_ids = $columns = array();
        foreach ($users as $id_user => $data_user) {
            $users_ids[] = $id_user;

            foreach ($data_user as $key => $value) {
                $columns[$key] = $key;
            }
        }

        $params = array();
        $sql_cond = " WHERE id_user IN (" . implode(',', $users_ids) . ") LIMIT " . count($users_ids);
        foreach($columns as $column){
            $cases = array();
            $sql_columns_prefix = "  `{$column}` = ";

            foreach ($users as $user => $cols) {
                if (isset($cols[$column])) {
                    $cases[] = " WHEN id_user = ? THEN IF(({$cols[$column]}) <= 0 AND `{$column}` <= ABS({$cols[$column]}), 0, ({$cols[$column]}) + `{$column}`)";
                    $params[] = $user;
                }
            }

            $cases[] = " ELSE `{$column}`";
            $sql_columns[] =  "{$sql_columns_prefix} CASE " . implode(' ', $cases) . " END ";
        }
        $sql = $sql_prefix . implode(', ',$sql_columns) . $sql_cond;

        return $this->db->query($sql, $params);
    }
    /**
     * $array - array for search the users
     * $key - search the users_id in array by key
     * $statistics_formula - array  with key => values (column => operation(-1 or +1))
     * sample:
     * $this->statistic->prepare_user_array(
     *        $questions_array,
     *        'id_user',
     *        array('ep_questions_wrote' => -1, 'ep_answers_wrote' => 1)
     * );
     */
    public function prepare_user_array($array = array(), $key = '', $statistics_formula = array()){
        $return_array = array();
        foreach($array as $element){
            if(!isset($return_array[$element[$key]]))
                $return_array[$element[$key]] = array();

            foreach($statistics_formula as $column => $operation){
                if(!isset($return_array[$element[$key]][$column])){
                    $return_array[$element[$key]][$column] = $operation;
                }

                $return_array[$element[$key]][$column] += $operation;

                if($return_array[$element[$key]][$column] < 0){
                    $return_array[$element[$key]][$column] = 0;
                }
            }
        }
        return $return_array;
    }

}
