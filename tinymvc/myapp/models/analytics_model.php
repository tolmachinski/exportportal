<?php
/**
 * analytics_model.php
 *
 * Analytics Model
 *
 * @author Cravciuc Andrei
 */


class Analytics_Model extends TinyMVC_Model {

	private $obj;
	private $analytics_targets 				= "analytics_targets";
	private $analytics_google 				= "analytics_google";
	private $analytics_google_countries 	= "analytics_google_countries";
	private $analytics_google_referrals 	= "analytics_google_referrals";
	private $analytics_pageview 			= "analytics_pageview";
	private $analytics_forms 				= "analytics_forms";
	private $countries 						= "port_country";

	public $target_types = array(
		'page' => array(
			'name' => 'Page'
		),
		'form' => array(
			'name' => 'Form'
		),
		'link' => array(
			'name' => 'Link'
		)
	);

	public $target_operators = array(
		'REGEXP',
		'BEGINS_WITH',
		'ENDS_WITH',
		'PARTIAL',
		'EXACT',
		'NUMERIC_EQUAL',
		'NUMERIC_GREATER_THAN',
		'NUMERIC_LESS_THAN',
		'IN_LIST'
	);

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

	// ANALYTIC TARGETS
	public function insert_target($insert = array()){
		if (empty($insert)) {
			return false;
		}

		$this->db->insert($this->analytics_targets, $insert);
		return $this->db->last_insert_id();
	}

	public function update_target($id_target = 0, $update = array()){
		if (empty($update)) {
			return false;
		}

		$this->db->where('id_target', $id_target);
		return $this->db->update($this->analytics_targets, $update);
	}

	public function delete_target($id_target = 0){
		$this->db->where('id_target', $id_target);
		return $this->db->delete($this->analytics_targets);
	}

	public function get_target($id_target = 0){
		$this->db->from($this->analytics_targets);
        $this->db->where('id_target', $id_target);

		return $this->db->get_one();
	}

	private function _prepare_target_params($conditions = array()){
		extract($conditions);

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			if(!empty($multi_order_by)){
				$this->db->orderby(implode(',', $multi_order_by));
			}
		}

		if(isset($target_type)){
			$this->db->where('target_type', $target_type);
		}

		if(isset($target_active_ga)){
			$this->db->where('target_active_ga', $target_active_ga);
		}

		if(isset($target_active_oa)){
			$this->db->where('target_active_oa', $target_active_oa);
		}
	}

	public function get_targets($conditions = array()){
        $this->db->from($this->analytics_targets);
		$this->_prepare_target_params($conditions);

		return $this->db->get();
	}

	public function count_targets($conditions = array()){
		$this->db->select('COUNT(*) as total_rows');
		$this->db->from($this->analytics_targets);
		$this->_prepare_target_params($conditions);

		$result = $this->db->get_one();
		return $result['total_rows'];
	}

	// GOOGLE ANALYTICS
	public function insert_ga($insert = array()){
		if (empty($insert)) {
			return false;
		}

		$this->db->insert_batch($this->analytics_google, $insert);
		return $this->db->last_insert_id();
	}

	public function insert_ga_countries($insert = array()){
		if (empty($insert)) {
			return false;
		}

		$this->db->insert_batch($this->analytics_google_countries, $insert);
		return $this->db->last_insert_id();
	}

	public function insert_ga_referrals($insert = array()){
		if (empty($insert)) {
			return false;
		}

		$this->db->insert_batch($this->analytics_google_referrals, $insert);
		return $this->db->last_insert_id();
	}

	private function _prepare_ga_params($conditions = array()){
		extract($conditions);

		if(isset($id_target)){
			$this->db->where(" id_target ", $id_target);
		}

		if(isset($analytic_date)){
			$this->db->where(" analytic_date ", $analytic_date);
		}
	}

	public function get_ga($conditions = array()){
        $this->db->from($this->analytics_google);
		$this->_prepare_ga_params($conditions);

		if(isset($conditions['sort_by'])){
			foreach($conditions['sort_by'] as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
            }

			if(!empty($multi_order_by)){
				$this->db->orderby(implode(',', $multi_order_by));
			}
        }

		if(isset($conditions['start'], $conditions['limit'])){
			$this->db->limit($conditions['limit'], $conditions['start']);
        }

        $result = $this->db->get();

		return $result;
	}

	public function count_ga($conditions = array()){
		$this->db->select('COUNT(*) as total_rows');
		$this->db->from($this->analytics_google);
		$this->_prepare_ga_params($conditions);

		$result = $this->db->get_one();
		return $result['total_rows'];
	}

	private function _prepare_ga_countries_params($conditions = array()){
		extract($conditions);

		if(isset($id_country)){
			$this->db->where(" id_country ", $id_country);
		}

		if(isset($ga_country)){
			$this->db->where(" ga_country ", $ga_country);
		}

		if(isset($analytic_date)){
			$this->db->where(" analytic_date ", $analytic_date);
		}
	}

	public function get_ga_countries($conditions = array()){
        $this->db->from($this->analytics_google_countries);
		$this->_prepare_ga_countries_params($conditions);

		extract($conditions);

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			if(!empty($multi_order_by)){
				$this->db->orderby(implode(',', $multi_order_by));
			}
		}

		if(isset($start, $limit)){
			$this->db->limit($limit, $start);
		}

		$records = $this->db->get();

		return !empty($records)?$records:array();
	}

	public function count_ga_countries($conditions = array()){
		$this->db->select('COUNT(*) as total_rows');
		$this->db->from($this->analytics_google_countries);
		$this->_prepare_ga_countries_params($conditions);

		$result = $this->db->get_one();
		return $result['total_rows'];
	}

	private function _prepare_ga_referrals_params($conditions = array()){
		extract($conditions);

		if(isset($referrer_source)){
			$this->db->where(" referrer_source ", $referrer_source);
		}

		if(isset($analytic_date)){
			$this->db->where(" analytic_date ", $analytic_date);
		}
	}

	public function get_ga_referrals($conditions = array()){
        $this->db->from($this->analytics_google_referrals);
		$this->_prepare_ga_referrals_params($conditions);

		extract($conditions);

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			if(!empty($multi_order_by)){
				$this->db->orderby(implode(',', $multi_order_by));
			}
		}

		if(isset($start, $limit)){
			$this->db->limit($limit, $start);
		}

		$records = $this->db->get();

		return !empty($records)?$records:array();
	}

	public function count_ga_referrals($conditions = array()){
		$this->db->select('COUNT(*) as total_rows');
		$this->db->from($this->analytics_google_referrals);
		$this->_prepare_ga_referrals_params($conditions);

		$result = $this->db->get_one();
		return $result['total_rows'];
	}

	// PAGEVIEWS ANALYTICS
	public function insert_pageview($insert = array()){
		if (empty($insert)) {
			return false;
		}

		$this->db->insert_batch($this->analytics_pageview, $insert);
		return $this->db->last_insert_id();
	}

	private function _prepare_pageview_params($conditions = array()){
		extract($conditions);

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			if(!empty($multi_order_by)){
				$this->db->orderby(implode(',', $multi_order_by));
			}
		}

		if(isset($id_target)){
			$this->db->where(" id_target ", $id_target);
		}

		if(isset($analytic_date)){
			$this->db->where(" analytic_date ", $analytic_date);
		}

		if(isset($start, $limit)){
			$this->db->limit($limit, $start);
		}
	}

	public function get_pageviews($conditions = array()){
        $this->db->from($this->analytics_pageview);
		$this->_prepare_pageview_params($conditions);

		return $this->db->get();
	}

	public function count_pageviews($conditions = array()){
		$this->db->select('COUNT(*) as total_rows');
		$this->db->from($this->analytics_pageview);
		$this->_prepare_pageview_params($conditions);

		$result = $this->db->get_one();
		return $result['total_rows'];
	}

	// FORMS ANALYTICS
	public function insert_forms($insert = array()){
		if (empty($insert)) {
			return false;
		}

		$this->db->insert_batch($this->analytics_forms, $insert);
		return $this->db->last_insert_id();
	}

	private function _prepare_forms_params($conditions = array()){
		extract($conditions);

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			if(!empty($multi_order_by)){
				$this->db->orderby(implode(',', $multi_order_by));
			}
		}

		if(isset($id_target)){
			$this->db->where(" id_target ", $id_target);
		}

		if(isset($analytic_date)){
			$this->db->where(" analytic_date ", $analytic_date);
		}

		if(isset($start, $limit)){
			$this->db->limit($limit, $start);
		}
	}

	public function get_forms($conditions = array()){
        $this->db->from($this->analytics_forms);
		$this->_prepare_forms_params($conditions);

		return $this->db->get();
	}

	public function count_forms($conditions = array()){
		$this->db->select('COUNT(*) as total_rows');
		$this->db->from($this->analytics_forms);
		$this->_prepare_forms_params($conditions);

		$result = $this->db->get_one();
		return $result['total_rows'];
	}

	public function get_countries(){
		$this->db->select("*");
		$this->db->from($this->countries);

		$records = $this->db->get();
        return empty($records) ? array() : $records ;
	}

	public function get_google_countries(){
		$this->db->select("*");
		$this->db->from($this->analytics_google_countries);
		$this->db->groupby("ga_country");

		$records = $this->db->get();
        return empty($records) ? array() : $records ;
	}

	public function get_google_unique_referrals(){
		$this->db->select("*");
		$this->db->from($this->analytics_google_referrals);
		$this->db->groupby("referrer_source");

		$records = $this->db->get();
        return empty($records) ? array() : $records ;
	}
}
