<?php

/**

 * site_model.php

 *

 * model for site's options and config from database

 *

 * @author  Litra Andrei

 */

class Site_Model extends TinyMVC_Model
{
    private $site_options_tb = 'site_options';

    public function getOption($option_name){
        $this->db->select('option_value');
        $this->db->where('option_name', $option_name);
        $this->db->limit(1);

        return $this->db->get_one($this->site_options_tb)['option_value'];
	}

	public function setOption($option_name, $option_value){
		return $this->db->insert($this->site_options_tb, array('option_name' => $option_name,'option_value' => $option_value));
	}

	public function updateOption($option_name, $option_value){
        $this->db->where('option_name', $option_name);
        return $this->db->update($this->site_options_tb, array('option_value' => $option_value));
    }
}

