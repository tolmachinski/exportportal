<?php
/**
 * items_model.php
 * items system model
 * @author Andrew Litra
 */

class Elasticsearch_Badwords_Model extends TinyMVC_Model {

    // hold the current controller instance
    var $obj;
    private $type = "bad_words";

    public $records = array();
    public $records_total = 0;
    public $aggregates = array();

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
        $this->obj->load->library("elasticsearch");
    }

    public function index($id = 0) {
        if (!empty($id)) {
            $this->db->where('id', $id);
        }

        $rows = $this->db->get('bad_words');

        $badWords = array();
        foreach ($rows as $row) {
            $badWords[$row['language']][] = $row['word'];
        }

        foreach($badWords as $language => $row) {
            $this->obj->elasticsearch->index($this->type, $language, array('language' => $language, 'words' => $row));
        }
    }

    public function is_clean($text) {
        $result = $this->obj->elasticsearch->get($this->type, array(
            "query" => array(
                "bool" => array(
                    "should" => array(
                        "match" => array(
                            "words" => $text
                        )
                    )
                )
            ),
        ));

        return $result['hits']['total']['value'] == 0;
    }

    function delete($ids) {
        $this->obj->elasticsearch->delete($this->type, $ids);
    }
}
