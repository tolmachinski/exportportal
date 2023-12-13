<?php
/**
*
*
* model lalal
*
* @author Tabaran Vadim
*
* @deprecated in favor of \Currencies_Model
*/
class Currency_Model extends TinyMVC_Model {
    public $currency_table = 'currency';

    function get_all_cur(){
        return $this->db->query_all("SELECT * FROM $this->currency_table WHERE `enable` = '1'");
    }

    function get_main_cur(){
        $sql = "SELECT code
                FROM $this->currency_table
                WHERE main= '1'";
        $temp = $this->db->query_one($sql);
        return $temp['code'];
    }

    function get_details($code){
        return $this->db->query_one("SELECT * FROM $this->currency_table WHERE `code` = ?", [$code]);
    }
}
