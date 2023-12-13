<?php

use App\Common\Database\BaseModel;
class Complains_Model extends BaseModel
{
    public $complainsTable = 'complains';
    public $complainsTypesTable = 'complains_types';
    public $complainsTypesThemesTable = 'complains_types_themes';
    public $complainsTypesThemesTie = 'complains_types_themes_tie';

    public function getComplains($conditions)
    {
        $orderBy = "update_op DESC";

        $this->db->select("c.*, ct.type as type_compl, CONCAT(u.fname, ' ', u.lname) as user_name, u.logged as user_logged, u.status as user_status,
                            CONCAT(u2.fname, ' ', u2.lname) as reported_user, u2.logged as reported_user_logged, u2.status as reported_status");
        $this->db->from("$this->complainsTable" . " AS c");
        $this->db->join("{$this->complainsTypesTable} ct", "c.id_type=ct.id_type", 'left');
        $this->db->join("users u", "u.idu=c.id_from", 'left');
        $this->db->join("users u2", "u2.idu=c.id_to", 'left');

        if(isset($conditions['status'])){
            $this->db->where('c.status', $conditions['status']);
		}

		if(isset($conditions['type'])){
            $this->db->where('c.id_type', $conditions['type']);
        }

		if(isset($conditions['online'])){
            $this->db->where('u.logged', $conditions['online']);
        }

		if(isset($conditions['user'])){
            $this->db->where('c.id_from', $conditions['user']);
        }

		if(isset($conditions['reported_user'])){
            $this->db->where('c.id_to', $conditions['reported_user']);
        }

		if(isset($conditions['reported_online'])){
            $this->db->where('u2.logged', $conditions['reported_online']);
        }

		if(isset($conditions['date_to'])){
            $this->db->where('DATE(c.date_time) <=', $conditions['date_to']);
        }

		if(isset($conditions['date_from'])){
            $this->db->where('DATE(c.date_time) >=', $conditions['date_from']);
		}

        if (isset($conditions['keywords'])) {
            $keywords = $conditions['keywords'];
            $this->db->where_raw("MATCH (c.text,c.search_info) AGAINST (?)", $keywords);
        }

        if(isset($conditions['sort_by'])){
			foreach($conditions['sort_by'] as $sortItem){
				$sortItem = explode("-", $sortItem);
				$multiOrderBy[] = $sortItem[0]." ".$sortItem[1];
			}

            if(!empty($multiOrderBy)){
                $orderBy = implode(",", $multiOrderBy);
            }
        }

        $this->db->orderby($orderBy);
        $this->db->limit((int) $conditions['per_p'], (int) $conditions['start']);

        return $this->db->query_all();
    }

    function getCountComplains($conditions)
    {
        $this->db->select("COUNT(*) as counter");

        $this->db->from("$this->complainsTable" . " AS c");
        $this->db->join("users u", "u.idu=c.id_from", 'left');
        $this->db->join("users u2", "u2.idu=c.id_to", 'left');

        if(isset($conditions['status'])){
            $this->db->where('c.status', $conditions['status']);
        }

        if(isset($conditions['type'])){
            $this->db->where('c.id_type', $conditions['type']);
        }

        if(isset($conditions['online'])){
            $this->db->where('u.logged', $conditions['online']);
        }

        if(isset($conditions['user'])){
            $this->db->where('c.id_from', $conditions['user']);
        }

        if(isset($conditions['reported_user'])){
            $this->db->where('c.id_to', $conditions['reported_user']);
        }

        if(isset($conditions['reported_online'])){
            $this->db->where('u2.logged', $conditions['reported_online']);
        }

        if(isset($conditions['date_to'])){
            $this->db->where('DATE(c.date_time) <=', $conditions['date_to']);
        }

        if(isset($conditions['date_from'])){
            $this->db->where('DATE(c.date_time) >=', $conditions['date_from']);
        }

        if (isset($conditions['keywords'])) {
            $keywords = $conditions['keywords'];
            $this->db->where_raw("MATCH (c.text,c.search_info) AGAINST (?)", $keywords);
        }

        $temp = $this->db->query_one();
        return $temp['counter'];
    }

    function updateComplain($id, $update)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->complainsTable, $update);
    }

    function deleteComplain($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->complainsTable);
    }

    function deleteExpiredComplains($days)
    {
        $this->db->in('status', array('declined', 'confirmed'));
        $this->db->where('TIMESTAMPDIFF(DAY, NOW() ,date_change) > ', $days);
        return $this->db->delete($this->complainsTable);
    }

    function getDetails($id)
    {
        $this->db->from($this->complainsTable);
        $this->db->where('id', $id);
        return $this->db->query_one();
    }

    function insertComplain($data)
    {
        return $this->db->insert($this->complainsTable, $data);
    }

    function setNotice($id, $notice)
    {
        $sql = "UPDATE " . $this->complainsTable . "
                SET notice = CONCAT_WS(',', ?, notice)
                WHERE id = ?";
        return $this->db->query($sql, array($notice, $id));
    }

    function getTypes()
    {
        $this->db->from($this->complainsTypesTable);
        $this->db->orderby('type');
        return $this->db->query_all();
    }

    function getThemes()
    {
        $this->db->select('*');
        $this->db->from($this->complainsTypesThemesTable);

        return $this->db->query_all();
    }

    function getTypeByKey($idType)
    {
        $this->db->select('id_type');
        $this->db->from($this->complainsTypesTable);
        $this->db->where('type_key', $idType);

        return $this->db->query_one();
    }

    function getComplainsThemes($conditions = array())
    {
        $select = "cttt.*";
        if(isset($conditions['joins'])){
            $select .= ", tie.id_tie, GROUP_CONCAT(ctt.id_type SEPARATOR ',') as type";
        }

        $this->db->select($select);
        $this->db->from("$this->complainsTypesThemesTable" . " AS cttt");

        if(isset($conditions['joins'])){
            $this->db->join("$this->complainsTypesThemesTie AS tie", "tie.id_theme = cttt.id_theme", 'left');
            $this->db->join("$this->complainsTypesTable AS ctt", "ctt.id_type = tie.id_type", 'left');
            $this->db->groupby("cttt.id_theme");
        }

        if(isset($conditions['type'])){
            $this->db->where('tie.id_type', $conditions['type']);
		}

		if(isset($conditions['theme'])){
            $this->db->where('tie.id_theme', $conditions['theme']);
		}

        if (isset($conditions['keywords'])) {
            $keywords = $conditions['keywords'];
            $this->db->where_raw("(ctt.type LIKE ? OR cttt.theme LIKE ?)", array_fill(0, 2, '%' . $keywords . '%'));
        }

        if(isset($conditions['sort_by'])){
            $orderBy = "tie.id_theme DESC";
			foreach($conditions['sort_by'] as $sortItem){
				$sortItem = explode("-", $sortItem);
				$multiOrderBy[] = $sortItem[0]." ".$sortItem[1];
			}

            if(!empty($multiOrderBy)){
                $orderBy = implode(",", $multiOrderBy);
            }
        }

        if(isset($orderBy)){
            $this->db->orderby($orderBy);
        }

        if(isset($conditions['per_p'])){
            $this->db->limit((int) $conditions['per_p'], (int) $conditions['start']);
        }

        return $this->db->query_all();

    }

    function getCountComplainsThemes($conditions)
    {
        $this->db->select("COUNT(*) over() as counter");
        $this->db->from("$this->complainsTypesThemesTable" . " AS cttt");
        $this->db->join("$this->complainsTypesThemesTie AS tie", "tie.id_theme = cttt.id_theme", 'left');
        $this->db->join("$this->complainsTypesTable AS ctt", "ctt.id_type = tie.id_type", 'left');

        if(isset($conditions['type'])){
            $this->db->where('tie.id_type', $conditions['type']);
		}

		if(isset($conditions['theme'])){
            $this->db->where('tie.id_theme', $conditions['theme']);
		}

        if (isset($conditions['keywords'])) {
            $keywords = $conditions['keywords'];
            $this->db->where_raw("(ctt.type LIKE ? OR cttt.theme LIKE ?)", array_fill(0, 2, '%' . $keywords . '%'));
        }

        $this->db->groupby("cttt.id_theme");

        $rez = $this->db->query_one();
		return $rez['counter'];
	}

    public function getTheme($idTheme, $details = false)
    {
        $select = "cttt.*";
        $this->db->from("{$this->complainsTypesThemesTable} as cttt");

        if($details){
            $select = "cttt.id_theme, cttt.theme, tie.id_tie, GROUP_CONCAT(ctt.id_type SEPARATOR ',') as types";
            $this->db->join("{$this->complainsTypesThemesTie} as tie", "tie.id_theme = cttt.id_theme", "left");
            $this->db->join("{$this->complainsTypesTable} as ctt", "ctt.id_type = tie.id_type", "left");
        }

        $this->db->select($select);
        $this->db->where("cttt.id_theme = ?", (int) $idTheme);
        return $this->db->query_one();
    }

    public function existTypes($idType)
    {
        $this->db->select("COUNT(*) as counter");
        $this->db->where("id_type = ?", (int) $idType);
        $rez = $this->db->get_one($this->complainsTypesTable);
        return $rez['counter'];
    }

    public function existThemeByType($idType, $idTheme)
    {
        $this->db->select("COUNT(*) as counter");
        $this->db->from("{$this->complainsTypesThemesTie} as tie");
        $this->db->join("$this->complainsTypesThemesTable AS cttt", "tie.id_theme = cttt.id_theme", 'left');
        $this->db->where("tie.id_type", $idType);
        $this->db->where("cttt.id_theme", $idTheme);

        $rez = $this->db->query_one();
        return $rez['counter'];
    }

    function addComplainTheme($insert)
    {
        if($this->db->insert($this->complainsTypesThemesTable, $insert)){
            return $this->db->last_insert_id();
        }
        return false;
    }

    function editComplainTheme($id, $update)
    {
        $this->db->where('id_theme', $id);
        return $this->db->update($this->complainsTypesThemesTable, $update);
    }

    function deleteTheme($id)
    {
        $this->db->where('id_theme', $id);
        return $this->db->delete($this->complainsTypesThemesTable);
    }

    function addRelComplainsTypes($data)
    {
        return $this->db->insert_batch($this->complainsTypesThemesTie, $data);
    }

    function clearRelations($idTheme)
    {
        $this->db->where('id_theme', $idTheme);
        return $this->db->delete($this->complainsTypesThemesTie);
    }

    function getThemesByType($idType)
    {
        $this->db->select("cttt.id_theme, cttt.theme");
        $this->db->from("{$this->complainsTypesThemesTie} as tie");
        $this->db->join("$this->complainsTypesThemesTable AS cttt", "tie.id_theme = cttt.id_theme", 'left');
        $this->db->where("tie.id_type", $idType);

        return $this->db->query_all();
    }
}
