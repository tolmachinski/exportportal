<?php
$default_modules = array(
    'user_guide' => array(
        'name' => 'user_guide',
        'model' => array(
            'name' => 'Elasticsearch_User_Guide_Model',
            'alias' => 'elasticsearch_user_guide'
        )
    ),
    'topics' => array(
        'name' => 'topics',
        'model' => array(
            'name' => 'Elasticsearch_Topics_Model',
            'alias' => 'elasticsearch_topics'
        )
    ),
    'faq' => array(
        'name' => 'faq',
        'model' => array(
            'name' => 'Elasticsearch_Faq_Model',
            'alias' => 'elasticsearch_faq'
        )
    ),
    'items' => array(
        'name' => 'items',
        'model' => array(
            'name' => 'Elasticsearch_Items_Model',
            'alias' => 'elasticsearch_items'
        )
    ),
    'blogs' => array(
        'name' => 'blogs',
        'model' => array(
            'name' => 'Elasticsearch_Blogs_Model',
            'alias' => 'elasticsearch_blogs'
        )
    ),
    'questions' => array(
        'name' => 'questions',
        'model' => array(
            'name' => 'Elasticsearch_Questions_Model',
            'alias' => 'elasticsearch_questions'
        )
    ),
    'item_category' => array(
        'name' => 'category',
        'model' => array(
            'name' => 'Elasticsearch_Category_Model',
            'alias' => 'elasticsearch_category'
        )
    ),
    'company' => array(
        'name' => 'company',
        'model' => array(
            'name' => 'Elasticsearch_Company_Model',
            'alias' => 'elasticsearch_company'
        )
    ),
    'bad_words' => array(
        'name' => 'bad_words',
        'model' => array(
            'name' => 'Elasticsearch_Badwords_Model',
            'alias' => 'elasticsearch_badwords'
        )
    ),
    'ep_events' => [
        'name' => 'ep_events',
        'model' => [
            'name' => 'Elasticsearch_Ep_Events_Model',
            'alias' => 'elasticsearch_ep_events'
        ]
    ],
    'b2b_requests' => [
        'name' => 'b2b_requests',
        'model' => [
            'name' => 'Elasticsearch_B2b_Model',
            'alias' => 'elasticsearch_b2b'
        ]
    ],
    'countries' => [
        'name' => 'countries',
        'model' => [
            'name' => 'Elasticsearch_Countries_Model',
            'alias' => 'elasticsearch_countries'
        ]
    ],
    'states' => [
        'name' => 'states',
        'model' => [
            'name' => 'Elasticsearch_States_Model',
            'alias' => 'elasticsearch_states'
        ]
    ],
    'cities' => [
        'name' => 'cities',
        'model' => [
            'name' => 'Elasticsearch_Cities_Model',
            'alias' => 'elasticsearch_cities'
        ]
    ],
    'help' => [
        'name'  => 'help',
        'model' => [
            'name'  => 'ElasticSearch_Help_Model',
            'alias' => 'elasticsearch_help_model'
        ]
    ],
    'users' => [
        'name' => 'users',
        'model' => [
            'name' => 'Elasticsearch_Users_Model',
            'alias' => 'elasticsearch_users'
        ]
    ],
);

$modules = array_slice($argv, 2);
if(empty($modules)){
    $modules = array_keys($default_modules);
}

echo "Indexing: ";
$log->info("Indexing ...");
echo PHP_EOL."got a cup of coffee?";
cup_of_coffee();

foreach ($modules as $module) {
    $tmvc->controller->load->model($default_modules[$module]['model']['name'], $default_modules[$module]['model']['alias']);
    echo "   -> {$default_modules[$module]['name']}".PHP_EOL;
    $log->info("   -> {$default_modules[$module]['name']}");
    $tmvc->controller->{$default_modules[$module]['model']['alias']}->index();
}

hand_shake();
