<?php
/**
 * items_model.php
 * items system model
 * @author Andrew Litra
 */


class Elasticsearch_Items_Suggestions_Model extends TinyMVC_Model
{
    // hold the current controller instance
    var $obj;
    private $type = "item_suggestion";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
        $this->obj->load->library("elasticsearch");
    }


    public function index_bulk($suggestions) {
        $queries = array();
        foreach($suggestions as $suggestion) {
            //$this->index_suggest($suggestion["suggestion_id"], $suggestion["suggestion"]);
            $queries[] = array("index" => array("_id" => $suggestion["suggestion_id"], "_type" => $this->type, "_routing" => $this->type));
            $queries[] = array(
                "suggest" => $suggestion["suggestion"],
                "category_id" => (int)$suggestion["category_id"],
                "global_priority" => (int)$suggestion["global_priority"],
                "local_priority" => (int)$suggestion["local_priority"],
                "category_depth" => (int)$suggestion["category_depth"],
                "children_count" => (int)$suggestion["children_count"],
                "attribute_count" => (int)$suggestion["attribute_count"],
                "scoring" => (int)$suggestion["scoring"]
            );
        }

        $this->obj->elasticsearch->type = $this->type;
        return $this->obj->elasticsearch->bulk($queries);
    }

    public function suggest($query) {
        $query = trim($query);

        $queryExploded = explode(" ", $query);
        $prefix1 = array_pop($queryExploded);
        $queryWithoutPrefix1 = empty($queryExploded) ? "" : implode(" ", $queryExploded)." ";
        $prefix2 = array_pop($queryExploded);
        $queryWithoutPrefix2 = empty($queryExploded) ? "" : implode(" ", $queryExploded)." ";

        $query = array(
            "bool" => array(
                "must" => array(
                    array(
                        "match" => array(
                            "suggest.ngrams" => array(
                                "query" => $query
                            )
                        )
                    )
                ),
                "should" => array(
                    //array(
                    //    "match_phrase" => array(
                    //        "suggest" => array(
                    //            "query" => $query, //$queryWithoutPrefix1,
                    //            "slop" => 5,
                    //            "boost" => 1000
                    //        )
                    //    ),
                    //),
                    array(
                        "match" => array(
                            "suggest.ngrams" => array(
                                "query" => $query,
                                "operator" => "and"

                            )
                        )
                    )
                )
            )
        );

        $size = 11;
        $rez = $this->obj->elasticsearch->get($this->type, array(
            "query" => $query,
            "rescore" => array(
                array(
                    "window_size" => $size,
                    "query" => array(
                        "rescore_query" => array(
                            "bool" => array(
                                "should" => array(
                                    array(
                                        "function_score" => array(
                                            "script_score" => array(
                                                "script" => "doc['scoring'].value",
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            ),
            "size" => $size
        ));

        $hits = $rez['hits']['hits'];
        if(empty($hits)) return array();

        $hit = array_shift($hits);
        $output = array();
        //$tmp[] = $hit["_source"]["suggest"]."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;cat_depth={$hit['_source']['category_depth']}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;child_cnt={$hit['_source']['children_count']}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;attr_cnt={$hit['_source']['attribute_count']}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$hit['_score'];
        $tmp[] = array("suggest" => $hit["_source"]["suggest"], "category_id" => $hit["_source"]["category_id"]);
        $_score = $hit["_score"];
        foreach($hits as $hit) {
            if($hit["_score"] == $_score) {
                //$tmp[] = $hit["_source"]["suggest"]."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;cat_depth={$hit['_source']['category_depth']}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;child_cnt={$hit['_source']['children_count']}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;attr_cnt={$hit['_source']['attribute_count']}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$hit['_score'];
                $tmp[] = array("suggest" => $hit["_source"]["suggest"], "category_id" => $hit["_source"]["category_id"]);
            } else {
                $_score = $hit["_score"];

                asort($tmp);
                $output = array_merge($output, $tmp);
                $tmp = array();
            }
        }
        $output = array_merge($output, $tmp);

        return $output;
    }
}
