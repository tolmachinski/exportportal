<?php

/** @deprecated in favor of \Product_Descriptions_Model */
class Items_Descriptions_Model extends TinyMVC_Model
{

    var $obj;
    private $items_descriptions_table = "items_descriptions";
    private $translations_languages_table = "translations_languages";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

    public function insert_descriptions($data) {
        $this->db->insert($this->items_descriptions_table, $data);
        return $this->db->last_insert_id();
    }

    function change_descriptions_by_item($id_item, $data){
        $this->db->where('id_item', $id_item);
        return $this->db->update($this->items_descriptions_table, $data);
    }

    public function get_descriptions_by_item($id_item = 0, $params = array()){
        extract($params);

        $this->db->select("idt.*, tlt.lang_name");
        $this->db->from($this->items_descriptions_table. " idt ");
        $this->db->join("{$this->translations_languages_table} tlt", 'idt.descriptions_lang = tlt.id_lang', 'left');
        $this->db->where("idt.id_item = ?", $id_item);

        if(isset($status)){
            $this->db->where("idt.status = ?", $status);
        }

        if(isset($not_status)){
            $this->db->where("idt.status != ?", $not_status);
        }

        $record = $this->db->get_one();

        return !empty($record) ? $record : array();
    }

    public function exist_descriptions($id_item){
        $this->db->select('COUNT(*) as counter');
        $this->db->from($this->items_descriptions_table);
        $this->db->where("id_item = ?", $id_item);
        $record = $this->db->query_one();

        return (int) $record['counter'];
    }

}
