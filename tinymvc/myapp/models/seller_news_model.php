<?php

/**
 * seller_news_model.php
 * model for seller news
 * @author Cravciuc Andrei
 */


class Seller_News_Model extends TinyMVC_Model
{
	private $news_table = "seller_news";
	private $comment_news_table = "seller_news_comments";

	/*Methods for news*/

	public function setSellerNews($data){
		return $this->db->insert($this->news_table, $data);
	}

	public function getNews($id_news){
        $this->db->where('id_news', $id_news);
        $this->db->limit(1);
        return $this->db->get_one('seller_news');
	}

	public function getSellerNews($conditions){
		$page = 0;
		$per_p = 20;
		$order_by = "sn.date_news DESC";

		extract($conditions);

		$where = $params = [];

        switch($sort_by){
			case 'title_asc': $order_by = 'sn.title_news ASC'; break;
			case 'title_desc': $order_by = 'sn.title_news DESC'; break;
			case 'date_asc': $order_by = 'sn.date_news ASC'; break;
			case 'date_desc': $order_by = 'sn.date_news DESC'; break;
			case 'rand': $order_by = ' RAND()'; break;
		}

		if(isset($id_seller)){
			$where[] = ' sn.id_seller = ?';
			$params[] = $id_seller;
		}

		if(isset($id_company)){
			$where[] = ' sn.id_company = ?';
			$params[] = $id_company;
		}

		if(isset($news_list)){
            $news_list = getArrayFromString($news_list);
			$where[] = ' id_news IN (' . implode(',', array_fill(0, count($news_list), '?')) . ') ';
            array_push($params, ...$news_list);
		}

		$sql .= "SELECT sn.* FROM {$this->news_table} sn";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(" AND", $where);
        }

		$sql .= " ORDER BY " . $order_by;

		$start = ($page-1)*$per_p;

		if($start < 0) $start = 0;

		$sql .= " LIMIT " . $start ;

		if($per_p > 0)
			$sql .= "," . $per_p;

		return $this->db->query_all($sql, $params);
	}

	public function getSimpleSellerNews($conditions){
        $columns = '*';
		extract($conditions);

		$where = $params = [];

		if(isset($id_seller)){
			$where[] = ' id_seller = ? ';
			$params[] = $id_seller;
		}

		if(isset($id_company)){
			$where[] = ' id_company = ? ';
			$params[] = $id_company;
		}

		if(isset($news_list)){
            $news_list = getArrayFromString($news_list);
			$where[] = ' id_news IN (' . implode(',', array_fill(0, count($news_list), '?')) . ') ';
            array_push($params, ...$news_list);
		}

		$sql .= "SELECT {$columns} FROM {$this->news_table}";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(" AND", $where);
        }

		return $this->db->query_all($sql, $params);
	}

	public function countSellerNews($conditions){
        extract($conditions);

		$where = $params = [];

		if(isset($id_seller)){
			$where[] = ' sn.id_seller = ?';
			$params[] = $id_seller;
		}

		if(isset($id_company)){
			$where[] = ' sn.id_company = ?';
			$params[] = $id_company;
		}

		if(isset($news_list)){
            $news_list = getArrayFromString($news_list);
			$where[] = ' id_news IN (' . implode(',', array_fill(0, count($news_list), '?')) . ') ';
            array_push($params, ...$news_list);
		}

		$sql .= "SELECT COUNT(*) as counter FROM {$this->news_table} sn";

		if(!empty($where)) {
			$sql .= " WHERE " . implode(" AND", $where);
        }

		return $this->db->query_one($sql, $params)['counter'];
	}

	function updateSellerNews($id_news, $data){
		$this->db->where('id_news', $id_news);
		return $this->db->update($this->news_table, $data);
	}

	public function moderateNews($id_news){
		$sql = "UPDATE {$this->news_table}
				  SET moderated = 1
				  WHERE id_news = ?";
		  return $this->db->query($sql, array($id_news));
	}

	public function exist_news($id_news){
        $this->db->select('COUNT(*) as counter');
        $this->db->where('id_news', $id_news);
        return $this->db->get_one($this->news_table)['counter'];
	}

	public function iPostedNews($data){
        $this->db->select('COUNT(*) as counter');
        $this->db->where('id_news', $data['id_news']);
        $this->db->where('id_company', $data['id_company']);
        return $this->db->get_one($this->news_table)['counter'];
	}

	public function deleteNews($id) {
        $id = getArrayFromString($id);
        $this->db->in('id_news', $id);
        return $this->db->delete($this->news_table);
	}

	/**
	* News Comment operation
	*/

	public function setComment($data){
		return empty($data) ? false : $this->db->insert($this->comment_news_table, $data);
	}

	public function updateNewsCommentCounter($id_news, $increment){
		$sql = "UPDATE {$this->news_table}
				SET comments_count = comments_count + ?
				WHERE id_news = ?";

		return $this->db->query($sql, [$increment, $id_news]);
	}

	public function getComment($id_comment){
		$sql = "SELECT 	cn.*,
					   	u.idu, u.fname, u.lname, u.email, u.`status`, u.user_group, u.logged, u.registration_date, u.user_photo,
						pc.country,
						ug.gr_name
				FROM $this->comment_news_table cn
				LEFT JOIN users u ON cn.id_user = u.idu
				LEFT JOIN user_groups ug ON ug.idgroup = u.user_group
				LEFT JOIN port_country pc ON u.country = pc.id
				WHERE id_comment = ?";

		 return $this->db->query_one($sql, array($id_comment));
	}

	public function getComments($id_news){
		$sql = "SELECT 	cn.*,
					   	u.idu, u.fname, u.lname, u.email, u.`status`, u.user_group, u.logged, u.registration_date, u.user_photo,
						pc.country,
						ug.gr_name
				FROM $this->comment_news_table cn
				LEFT JOIN users u ON cn.id_user = u.idu
				LEFT JOIN user_groups ug ON ug.idgroup = u.user_group
				LEFT JOIN port_country pc ON u.country = pc.id
				WHERE id_news = ?
				ORDER BY cn.date_comment ASC";

		 return $this->db->query_all($sql, array($id_news));
	}

	public function iCommented($id_comment, $id_user){
        $this->db->select('COUNT(*) as counter');
        $this->db->where('id_comment', $id_comment);
        $this->db->where('id_user', $id_user);
        $this->db->limit(1);

        return $this->db->get_one($this->comment_news_table)['counter'];
	}

	public function isModerated($id_comment){
        $this->db->select('moderated');
        $this->db->where('id_comment', $id_comment);
        $this->db->limit(1);

        return $this->db->get_one($this->comment_news_table)['moderated'];
	}

	public function update_comment($id_comment, $data){
		$this->db->where('id_comment', $id_comment);
		return $this->db->update($this->comment_news_table, $data);
	}

	public function moderateComment($id_comment){
		$sql = "UPDATE ".$this->comment_news_table."
				  SET moderated = 1
				  WHERE id_comment = ?";
		  return $this->db->query($sql, array($id_comment));
	}

	public function deleteComment($id_comment){
        $this->db->where('id_comment', $id_comment);
        return $this->db->delete($this->comment_news_table);
	}

	public function deleteNewsComments($id) {
        $id = getArrayFromString($id);
        $this->db->in('id_news', $id);

        return $this->db->delete($this->comment_news_table);
	}
}
