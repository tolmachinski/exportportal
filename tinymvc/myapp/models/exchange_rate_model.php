<?php
/**
*
*
* model lalal
*
* @author Tabaran Vadim
*/

class Exchange_Rate_Model extends TinyMVC_Model
{
    public $exchange_rate_table = 'exchange_rate';

	function insert_ex_rate($insert){
		$this->db->insert_batch($this->exchange_rate_table, $insert);
		return $this->db->getAffectableRowsAmount();
	}
}
