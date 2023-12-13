<?php
/**
 * Campaigns_Model.php
 *
 * model for campaigns
 *
 * @author Cravciuc Andrei
 * 
 * @deprecated 2.39.0 use Campaign_Model
 */

class Campaigns_Model extends TinyMVC_Model {

    var $obj;
    private $campaigns = "campaigns";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

	function set_campaign($insert = array()){
		if(empty($insert)){
			return FALSE;
		}

		$this->db->insert($this->campaigns, $insert);
		return $this->db->last_insert_id();
	}

	function get_campaign($id_campaign = 0){
		$sql = "SELECT 	*
				FROM {$this->campaigns}
				WHERE id_campaign = ?";
		return $this->db->query_one($sql, array($id_campaign));
	}

	function get_campaign_by_alias($campaign_alias = ''){
		$campaign_alias = trim($campaign_alias);
		if (empty($campaign_alias)) {
			return false;
		}

		$sql = "SELECT 	*
				FROM {$this->campaigns}
				WHERE campaign_alias = ?";
		return $this->db->query_one($sql, array($campaign_alias));
	}

	function get_campaigns(){
		$sql = "SELECT 	*
				FROM {$this->campaigns}";
		return $this->db->query_all($sql);
	}
}
