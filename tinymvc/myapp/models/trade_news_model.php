<?php

class Trade_news_Model extends TinyMVC_Model
{
    private $trade_news_table = "trade_news";

	public function set_trade_news($data){
        return empty($data) ? false : $this->db->insert($this->trade_news_table, $data);
	}

	public function exist_trade_news($id){
		$this->db->select('COUNT(*) as exist');
		$this->db->from("{$this->trade_news_table} tn");
		$this->db->where('tn.id_trade_news', $id);
		return $this->db->query_one()['exist'];
	}

	public function update_trade_news($id, $data){
        $this->db->where('id_trade_news', $id);
        return $this->db->update($this->trade_news_table, $data);
    }

	public function delete_trade_news($id){
		$this->db->where('id_trade_news', $id);
		return $this->db->delete($this->trade_news_table);
	}

	public function get_one_trade_news($id){
		$this->db->select('tn.*');
		$this->db->from("{$this->trade_news_table} tn");
		$this->db->where('tn.id_trade_news', $id);
		return $this->db->query_one();
    }

	public function get_trade_news($conditions = array()){
		$this->db->select('tn.*');
		$this->db->from("{$this->trade_news_table} tn");
		$order_by = 'tn.id_trade_news DESC';

		extract($conditions);

		if(isset($id_trade_news)){
            $this->db->where('tn.id_trade_news', $id_trade_news);
		}

		if(isset($not_trade_news)){
            $this->db->where('tn.id_trade_news != ?', $not_trade_news);
		}

		if(isset($visible)){
            $this->db->where('tn.is_visible', $visible);
		}

		if(isset($date_from)){
            $this->db->where('tn.date >= ?', formatDate($date_from, 'Y-m-d H:i:s'));
		}

		if(isset($date_to)){
            $this->db->where('tn.date <= ?', formatDate($date_to, 'Y-m-d H:i:s'));
		}

		if(isset($date_update_from)){
            $this->db->where('tn.date_update >= ?', formatDate($date_update_from, 'Y-m-d H:i:s'));
		}

		if(isset($date_update_to)){
            $this->db->where('tn.date_update <= ?', formatDate($date_update_to, 'Y-m-d H:i:s'));
		}

		if(isset($keywords)){
			$words = explode(" ", $keywords);
            $keywordsParams = [];
			foreach($words as $word){
				if(strlen($word) > 3){
					$s_word[] = " (tn.title LIKE ? OR tn.short_description  LIKE ? OR tn.content LIKE ?) ";
                    array_push($keywordsParams, ...array_fill(0, 3, '%' . $word . '%'));
                }
			}

			if(!empty($s_word)){
                $this->db->where_raw(" (" . implode(" AND ", $s_word) . ") ", $keywordsParams);
            }
		}

		if(isset($sort_by)){
            $multi_order_by = array();
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

            if(!empty($multi_order_by)){
                $order_by = implode(',', $multi_order_by);
            }
        }

		$this->db->orderby($order_by);

		if(isset($start) && isset($limit)) {
            $this->db->limit((int) $limit, (int) $start);
		}

		return $this->db->query_all();
    }

	public function count_trade_news($conditions = array()){
		$this->db->select('COUNT(*) as counter');
		$this->db->from("{$this->trade_news_table} tn");

		extract($conditions);

		if(isset($id_trade_news)){
            $this->db->where('tn.id_trade_news', $id_trade_news);
		}

		if(isset($not_trade_news)){
            $this->db->where('tn.id_trade_news != ?', $not_trade_news);
		}

		if(isset($visible)){
            $this->db->where('tn.is_visible', $visible);
		}

		if(isset($date_from)){
            $this->db->where('tn.date >= ?', formatDate($date_from, 'Y-m-d H:i:s'));
		}

		if(isset($date_to)){
            $this->db->where('tn.date <= ?', formatDate($date_to, 'Y-m-d H:i:s'));
		}

		if(isset($date_update_from)){
            $this->db->where('tn.date_update >= ?', formatDate($date_update_from, 'Y-m-d H:i:s'));
		}

		if(isset($date_update_to)){
            $this->db->where('tn.date_update <= ?', formatDate($date_update_to, 'Y-m-d H:i:s'));
		}

		if(isset($keywords)){
			$words = explode(" ", $keywords);
            $keywordsParams = [];
			foreach($words as $word){
				if(strlen($word) > 3){
					$s_word[] = " (tn.title LIKE ? OR tn.short_description  LIKE ? OR tn.content LIKE ?) ";
                    array_push($keywordsParams, ...array_fill(0, 3, '%' . $word . '%'));
                }
			}

			if(!empty($s_word)){
                $this->db->where_raw(" (" . implode(" AND ", $s_word) . ") ", $keywordsParams);
            }
		}

		return $this->db->query_one()['counter'];
    }
}
