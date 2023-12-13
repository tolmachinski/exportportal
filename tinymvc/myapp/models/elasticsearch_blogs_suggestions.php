<?php
/**
 * items_model.php
 * items system model
 * @author Andrew Litra
 */
class Elasticsearch_Blogs_Suggestions_Model extends TinyMVC_Model
{
    // hold the current controller instance
    var $obj;
    private $type = "blog_suggestion";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
        $this->obj->load->library("elasticsearch");
    }

    public function index() {
        $this->db->select('tags');
        $tags = $this->db->get('blogs');

        $tags_for_elastic = array();
        $waterline = 1000;
        foreach($tags as $tag) {
            $splitted_tags = explode(",", $tag['tags']);
            foreach($splitted_tags as $splitted_tag) {
                $splitted_tag = trim($splitted_tag);
                if(empty($splitted_tag)) continue;

                $splitted_tag = strtolower($splitted_tag);

                $tags_for_elastic[] = array(
                    "suggest" => $splitted_tag,
                    '_id' => hash("sha256", $splitted_tag),
                );

                if(count($tags_for_elastic) >= $waterline) {
                    $this->index_bulk($tags_for_elastic);
                    $tags_for_elastic = array();
                }
            }
        }

        if(!empty($tags_for_elastic)) {
            $this->index_bulk($tags_for_elastic);
        }
    }

    public function index_bulk($suggestions) {
        $queries = array();
        foreach($suggestions as $suggestion) {
            $queries[] = array("index" => array("_id" => $suggestion["_id"], "_type" => $this->type, "_routing" => $this->type));
            $queries[] = array(
                "suggest" => $suggestion["suggest"],
            );
        }

        return $this->obj->elasticsearch->bulk($queries);
    }

    public function suggest($needle) {
        $query = array(
            "query" => array(
                "multi_match" => array(
                    "query" => trim($needle),
                    "fields" => array("suggest", "suggest.4grams^0.5"),
                    "type" => "most_fields"
                )
            )
        );

        $rez = $this->obj->elasticsearch->get($this->type, $query);
        $hits = $rez['hits']['hits'];
        if(empty($hits)) return array();

        $output = array();
        foreach($hits as $hit) {
            $output[] = $hit["_source"]["suggest"];
        }

        return $output;
    }
}
