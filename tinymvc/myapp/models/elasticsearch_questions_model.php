<?php

class Elasticsearch_Questions_Model extends TinyMVC_Model
{
    private $type = "questions";
    private $questions_table = "questions";

    /** @var TinyMVC_Library_Elasticsearch $elasticsearchLibrary */
    private $elasticsearchLibrary;

    public $inner_answers_records = array();
    public $question_answers_records = array();
    public $question_answers_count = array();

    public $questions_records = array();
    public $questions_count = 0;
    public $aggregates = array();

    private function _get_answers_fields($answer){
        return array(
            'id_answer'         => (int) $answer['id_answer'],
            'id_question'       => (int) $answer['id_question'],
            'id_user'           => (int) $answer['id_user'],
            'text_answer'       => $answer['text_answer'],
            'date_answer'       => $answer['date_answer'],
            'count_plus'        => (int) $answer['count_plus'],
            'count_minus'       => (int) $answer['count_minus'],
            'count_comments'    => (int) $answer['count_comments'],
            'moderated'         => (int) $answer['moderated'],
            'fname'             => $answer['fname'],
            'lname'             => $answer['lname'],
            'user_photo'        => $answer['user_photo'],
            'user_group'        => $answer['user_group'],
        );
    }

    private function _get_question_fields(array $rawQuestion){
        $question = [
            'id_question'       => (int) $rawQuestion['id_question'],
            'title_question'    => $rawQuestion['title_question'],
            'text_question'     => $rawQuestion['text_question'],
            'count_answers'     => (int) $rawQuestion['count_answers'],
            'id_user'           => (int) $rawQuestion['id_user'],
            'fname'             => $rawQuestion['fname'],
            'lname'             => $rawQuestion['lname'],
            'user_group'        => $rawQuestion['user_group'],
            'user_photo'        => $rawQuestion['user_photo'],
            'id_country'        => (int) $rawQuestion['id_country'],
            'country'           => $rawQuestion['country'],
            'date_question'     => $rawQuestion['date_question'],
            'was_searched'      => (int) $rawQuestion['was_searched'],
            'moderated'         => (int) $rawQuestion['moderated'],
            'views'             => (int) $rawQuestion['views'],
            'lang'              => $rawQuestion['lang'],
            'id_category'       => (int) $rawQuestion['id_category'],
            'id_category_lang'  => $rawQuestion['id_category'],
            'category'          => [
                'id'    => (int) $rawQuestion['id_category'],
                'title' => $rawQuestion['categoryTitle'],
                'slug'  => $rawQuestion['categorySlug'],
            ],
        ];

        return array_filter($question, fn ($field) => null !== $field);
    }

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->elasticsearchLibrary = library(TinyMVC_Library_Elasticsearch::class);
    }

    public function countQuestions($conditions)
    {
        extract($conditions);

        if(isset($id_user)){
            $filter_user =  $this->elasticsearchLibrary->get_term("id_user", $id_user);
            $full_query[] = array(
                "query" =>
                    $filter_user
            );
        }
        $melastic_results = $this->elasticsearchLibrary->count($this->type, $full_query);
        $this->questions_count = $melastic_results['count'];
    }

    public function getQuestions($conditions){
		extract($conditions);

        $lang = __SITE_LANG;
        $page = $page ?? 1;
        $perPage = $perPage ?? ($per_p ?? 20);

        $filter_user = $filter_category = $filter_country = [];

        $filter_must = array();

        $should = array();
        $must = array();
        $must_not = array();

        $filter_must[] = $filter_lang = $this->elasticsearchLibrary->get_term("lang", $lang);

		if(isset($id_question)){
            $filter_must[] = $this->elasticsearchLibrary->get_term("id_question", $id_question);
		} elseif(isset($questions_list)){
            $filter_must[] = array(
                array(
                    "terms" => array(
                        "id_question" => $questions_list
                    )
                )
            );
            //$this->elasticsearchLibrary->get_match("id_question", $questions_list, "or");
        }

		if(isset($id_user)){
            $filter_user = $this->elasticsearchLibrary->get_term("id_user", $id_user);
            $filter_must[] = $filter_user;
		}

		if (isset($id_category)) {
            $filter_must[] = $filter_category = $this->elasticsearchLibrary->get_term("id_category", $id_category);
		}

        if (isset($categoriesIds)) {
            $filter_must[] = $filter_category = $this->elasticsearchLibrary->get_terms('id_category', $categoriesIds);
        }

		if(isset($id_country)){
            $filter_country = $this->elasticsearchLibrary->get_term("id_country", $id_country);
            $filter_must[] = $filter_country;
		}

		if(isset($moderated)){
            $filter_must[] = $this->elasticsearchLibrary->get_term("moderated", $moderated);
        }

		if(isset($related_categories)){
            $should[] = $this->elasticsearchLibrary->get_terms("id_category", $related_categories);
        }

		if(isset($related_countries)){
            $should[] = $this->elasticsearchLibrary->get_terms("id_country", $related_countries);
        }

		if(isset($excluded_ids)){
            $must_not[] = $this->elasticsearchLibrary->get_term("id_question", $excluded_ids);
		}

		if(isset($answered)){
             if($answered) {
                 $filter_must[] = array(
                     "range" => array(
                        "count_answers" => array(
                            "gt"=> 0
                        )
                    )
                 );
             } else {
                 $filter_must[] = $this->elasticsearchLibrary->get_term("count_answers", "0");
             }
		}

		if (isset($added_start)) {
            $filter_must[] = array(
                "range" => array(
                    "date_question" => array(
                        "gte" => $added_start
                    )
                )
            );
		}

		if (isset($added_finish)) {
            $filter_must[] = array(
                "range" => array(
                    "date_question" => array(
                        "lte" => $added_finish
                    )
                )
            );
		}

		if (isset($keywords)) {
            $must[] = $this->elasticsearchLibrary->get_multi_match(array("title_question.ngrams", "text_question.ngrams", "fname", "lname", "title_cat", "country"), $keywords, "most_fields");
            $should[] = $this->elasticsearchLibrary->get_multi_match(array("title_question", "text_question"), $keywords, "most_fields");
		}

        $sort = array();
    	if (isset($order_by)) {
            $explode = explode("-", $order_by);
            if($explode[0] == 'popular'){
                $sort = array(
                    'views' => $explode[1],
                    'was_searched' => $explode[1]
                );
            }else{
                $sort[$explode[0]] = $explode[1];
            }

        }

        $aggregates = array();
        if(isset($aggregates_answered_counter) ) {
            $aggregates["answered_counter"] = array(
                "sum" =>   array(
                    "script" => "doc['count_answers'].value > 0 ? 1 : 0"
                )
            );
        }

        if(isset($answers_limit) && isset($id_question)){
            $should[] = array(
                "nested" => array(
                    "path" => 'answers',
                    "query" => array(
                        "bool" => array(
                            "must" => array(
                                "match" => array(
                                    "answers.id_question" => $id_question
                                )
                            )
                        )

                    ),
                    "inner_hits" => array(
                        "name" => 'inner_answers',
                        "size" => $answers_limit
                    )
                )
            );
        }

        $elastic_query =  array(
            "query" => array(
                "bool" => array(
                    "must" => $must,
                    "must_not" => $must_not,
                    "should" => $should,
                    "filter" => array(
                        "bool" => array(
                            "must" => $filter_must
                        )
                    )
                )
            ),
            "sort" => $sort
        );

        if(!isset($show_answers) || !$show_answers)
        {
            $elastic_query["_source"] = array(
                "excludes" => array("answers")
            );
        }

        if(! empty($aggregates)) {
            $elastic_query['aggs'] = $aggregates;
        }

        if(isset($start, $limit)) {
            $elastic_query['size'] = $limit;
            $elastic_query['from'] = $start;
        } else {
            $elastic_query['size'] = $perPage;
            $elastic_query['from'] = $perPage * ($page > 0 ? ($page - 1) : $page);
        }

        $full_queries = array(
            $elastic_query
        );

        if(isset($aggregate_counter_category)){
            $aggregate_counter_category_filter = array($filter_lang);
            if(!empty($filter_country)){
                $aggregate_counter_category_filter[] = $filter_country;
            }

            $full_queries[] = array(
                "query" => array(
                    "bool" => array(
                        "filter" => array(
                            "bool" => array(
                                "must" => $aggregate_counter_category_filter
                            )
                        )
                    )
                ),
                "aggs" => array(
                    "counter_category" => array(
                        "terms" => array(
                            "field" => "id_category",
                            "size"  => 50000
                        )
                    )
                )
            );
        }

        if(isset($aggregates_counter_user)){
            if(!empty($id_user)){
                $aggregate_counter_user_filter[] = $filter_user;
            }

            $full_queries[] = array(
                "query" => array(
                    "bool" => array(
                        "filter" => array(
                            "bool" => array(
                                "must" => $aggregate_counter_user_filter
                            )
                        )
                    )
                ),
                "aggs" => array(
                    "counter_user" => array(
                        "terms" => array(
                            "field" => "id_user",
                            "size"  => 300
                        )
                    )
                ),
                "size" => 0
            );
        }

        if(isset($aggregate_counter_country)){
            $aggregate_counter_country_filter = array($filter_lang);
            if(!empty($filter_category)){
                $aggregate_counter_country_filter[] = $filter_category;
            }

            $full_queries[] = array(
                "query" => array(
                    "bool" => array(
                        "filter" => array(
                            "bool" => array(
                                "must" => $aggregate_counter_country_filter
                            )
                        )
                    )
                ),
                "aggs" => array(
                    "counter_country" => array(
                        "terms" => array(
                            "field" => "id_country",
                            "size"  => 300
                        )
                    )
                ),
                "size" => 0
            );
        }

        $melastic_results = $this->elasticsearchLibrary->mget($this->type, $full_queries);
        $elastic_results = array_shift($melastic_results['responses']);

        if(isset($elastic_results['hits']['hits'])) {
            $this->questions_records = array_map(function($ar) { return $ar['_source']; }, $elastic_results['hits']['hits']);
            $this->questions_count = $elastic_results['hits']['total']['value'];
        }

        if(isset($aggregates_answered_counter)) {
            $this->aggregates['answered_counter'] = $elastic_results['aggregations']['answered_counter']['value'];
            //$this->aggregates['answered_counter'] = $melastic_results['responses'][0]['aggregations']['answered_counter']['value'];
        }

        if(isset($aggregates_counter_user)) {
            $this->aggregates['counter_user'] = $melastic_results['responses'][0]['aggregations']['counter_user']['buckets'][0]['doc_count'];
        }

        if(isset($answers_limit)) {
            $this->aggregates['answers'] = array_map(function($ar) { return $ar['_source']; }, $elastic_results['hits']['hits'][0]['inner_hits']['inner_answers']['hits']['hits']);
        }

        if(isset($aggregate_counter_category)){
            $results_counter_category = array_shift($melastic_results['responses']);
            foreach($results_counter_category['aggregations']['counter_category']['buckets'] as $aggregation_category) {
                $temp = explode('_', $aggregation_category['key'], 2);
                $this->aggregates['counter_category'][(int)$temp[0]] = $aggregation_category['doc_count'];
            }
        }

        if(isset($aggregate_counter_country)){
            $results_counter_country = array_shift($melastic_results['responses']);
            foreach($results_counter_country['aggregations']['counter_country']['buckets'] as $aggregation_country) {
                $this->aggregates['counter_country'][(int)$aggregation_country['key']] = $aggregation_country['doc_count'];
            }
        }

        return $this->questions_records;
    }

    function getAnswers($conditions)
    {
		$page = 1;
        $per_p = 20;

        extract($conditions);
        if(isset($start, $limit)) {
            $size = $limit;
            $from = $start;
        } else {
            $size = $per_p;
            $from = $per_p * ($page > 0 ? ($page - 1) : $page);
        }

        if(isset($by_id_user)){
            $must[] = [
                "nested" => array(
                    "path" => 'answers',
                    "query" => array(
                        "match" => array("answers.id_user" => $by_id_user)
                    ),
                    "inner_hits" => array(
                        "name" => 'inner_by_user',
                    )
                )
            ];
        }

        if(isset($by_id_question)){
            $must[] = [
                "nested" => array(
                    "path" => 'answers',
                    "query" => array(
                        "match" => array("answers.id_question" => $by_id_question)
                    ),
                    "inner_hits" => array(
                        "name" => 'inner_by_question',
                        "size" => $size,
                        "from" => $from
                    )
                )
            ];

            $no_main_size = true;
        }

        $sort = array();
    	if (isset($order_by)) {
            $explode = explode("-", $order_by);
            if(strpos($explode[0], 'answers') !== false){
                $sort =  array(
                    "$explode[0]" => array(
                        "order" => $explode[1],
                        "nested_path" => "answers"
                    )
                );
            }else{
                $sort[$explode[0]] = $explode[1];
            }
        }

        $elastic_query = array(
            "query" => array(
                "bool" => array(
                   "must" => $must
                 )
            ),
            "sort" => $sort
        );

        if(!isset($no_main_size)){
            $elastic_query["size"] = $size;
            $elastic_query["from"] = $from;
        }

        $full_queries = array(
            $elastic_query
        );

        $melastic_results = $this->elasticsearchLibrary->mget($this->type, $full_queries);
        $elastic_results = $melastic_results['responses'][0];

        if(isset($elastic_results['hits']['hits'])) {
            $this->question_answers_records = array_map(function($ar) { return $ar['_source']; }, $elastic_results['hits']['hits']);
            if($by_id_question){
                $type = 'inner_by_question';
                $this->question_answers_count = $this->question_answers_records[0]['count_answers'];
            }else{
                $type = 'inner_by_user';
                $this->question_answers_count = $elastic_results['hits']['total']['value'];
            }

            foreach($elastic_results['hits']['hits'] as $hit){
                $this->inner_answers_records[$hit['_id']] = array_map(function($ar) {return $ar['_source']; }, $hit['inner_hits'][$type]['hits']['hits']);
            }

        }

    }

    function delete($ids)
    {
        /** @var ElasticSearch_Help_Model $elasticsearchHelpModel */
        $elasticsearchHelpModel = model(ElasticSearch_Help_Model::class);

        $ids = is_array($ids) ? $ids : explode(',', $ids);
        foreach ($ids as $id) {
            $elasticsearchHelpModel->syncItem((int) $id, 'getCommunityHelpItems', true);
        }

        $this->elasticsearchLibrary->delete($this->type, $ids);
    }

    function updateQuestion($id_question = 0, $record = array()) {
        if (empty($record)) {
            $sql = "SELECT  q.*,
                            pcnt.country,
                            u.fname, u.lname, u.email, u.user_photo, u.user_group,
                            CONCAT(q.id_category, '_', q.lang) as id_category_lang,
                            qCat.title_cat as categoryTitle, qCat.url as categorySlug
                    FROM {$this->questions_table} q
                    LEFT JOIN questions_categories qCat ON qCat.idcat = q.id_category
                    INNER JOIN users u ON q.id_user = u.idu
                    INNER JOIN port_country pcnt ON q.id_country = pcnt.id
                    WHERE id_question = ?";

            $record = $this->db->query_one($sql, $id_question);
        }

        if(!empty($record)){
            $to_insert = $this->_get_question_fields($record);
            $this->elasticsearchLibrary->update($this->type, $id_question, $to_insert);

            /** @var ElasticSearch_Help_Model $elasticsearchHelpModel */
            $elasticsearchHelpModel = model(ElasticSearch_Help_Model::class);
            $elasticsearchHelpModel->syncItem((int) $id_question, 'getCommunityHelpItems', false);
            
        }
    }

    function index($id_question = 0) {
        $this->db->select("q.*, pcnt.country, u.fname, u.lname, u.email, u.user_photo, u.user_group, CONCAT(q.id_category, '_', q.lang) as id_category_lang, qCat.title_cat as categoryTitle, qCat.url as categorySlug");
        $this->db->from($this->questions_table . ' q');
        $this->db->join('users u', 'q.id_user = u.idu', 'inner');
        $this->db->join('port_country pcnt', 'q.id_country = pcnt.id', 'inner');
        $this->db->join('questions_categories qCat', 'qCat.idcat = q.id_category', 'left');

        if (!empty($id_question)) {
            $this->db->where('id_question', $id_question);
        }

        $rows = $this->db->get();

        /** @var Questions_Model $questionsModel */
        $questionsModel = model(Questions_Model::class);

        foreach($rows as $row) {
            $to_insert = $this->_get_question_fields($row);

            $answers = $questionsModel->getAnswers(array(
                "id_question" => $row['id_question']
            ));

            $to_insert['answers'] = array();
            if(!empty($answers)){
                foreach ($answers as $answer) {
                    $to_insert['answers'][] = $this->_get_answers_fields($answer);
                }
            }

            $this->elasticsearchLibrary->index($this->type, $row['id_question'], $to_insert);
        }
    }

    function indexAnswer($id_answer, $id_question, $type = 'add')
    {
        /** @var Questions_Model $questionsModel */
        $questionsModel = model(Questions_Model::class);

        $answer = $this->_get_answers_fields($questionsModel->getAnswer($id_answer));

        if($type == 'update'){
            $this->deleteAnswer($id_answer, $id_question);
        }

        $script_array =  array(
            "inline"    => "if (ctx._source.containsKey('answers')) { ctx._source.answers.add(params.answer) } else { ctx._source.answers = params.answer }",
            "lang"      => "painless",
            "params"    => array("answer" => $answer)
        );

        $this->elasticsearchLibrary->update_by_script($this->type, $id_question, $script_array);
    }

    function deleteAnswer($id_answer, $id_question) {
        $this->elasticsearchLibrary->update_by_script($this->type, $id_question, "ctx._source.answers.removeIf(answers -> answers.id_answer == $id_answer)");
    }

    function counter_question_field_change($id_question, $field, $value) {

        $script_array =  array(
            "inline"    => "ctx._source.$field += params.value",
            "lang"      => "painless",
            "params"    => array("value" => (int) $value)
        );
        $this->elasticsearchLibrary->update_by_script($this->type, $id_question, $script_array);
    }

    function answer_counter_change($id_question, $id_answer, $type_count, $value)
    {
        $script = "def targets = ctx._source.answers.findAll(answer -> answer.id_answer == params.id_answer); for(answer in targets) { answer.$type_count += params.value  }";
        //$script = "for(int i = 0; i < ctx._source.answers.length; i++){ if(ctx._source.answers[i].id_answer == params.id_answer) { ctx._source.answers[i].$type_count += params.value; } }";

        $script_array =  array(
            "inline"    => $script,
            "lang"      => "painless",
            "params"    => array("id_answer" => (int) $id_answer, "value" => (int) $value)
        );

        $this->elasticsearchLibrary->update_by_script($this->type, $id_question, $script_array);
    }

    function increment_searched($ids) {
        $this->elasticsearchLibrary->update_by_query(
            $this->elasticsearchLibrary->get_terms('id_question', $ids),
            'ctx._source.was_searched++',
            $this->type
        );
    }
}
