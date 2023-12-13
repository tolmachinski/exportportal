<?php

$modules = array_slice($argv, 2);
require("blogs.php");
require("company.php");
require("items.php");
require("questions.php");
require("item_category.php");
require("badwords.php");
require("topics.php");
require("faq.php");
require("user_guide.php");
require("ep_events.php");
require("b2b_requests.php");
require("countries.php");
require("states.php");
require("cities.php");
require("users.php");

require 'help.php';


$mappings = [
    'item_category'     => $itemsCategoriesMapping,
    'b2b_requests'      => $b2bRequestsMapping,
    'user_guide'        => $user_guideMapping,
    'questions'         => $questionsMapping,
    'bad_words'         => $badWordsMapping,
    'ep_events'         => $epEventsMapping,
    'countries'         => $countriesMapping,
    'ep_events'         => $epEventsMapping,
    'company'           => $companyMapping,
    'states'            => $statesMapping,
    'cities'            => $citiesMapping,
    'topics'            => $topicsMapping,
    'items'             => $itemsMapping,
    'users'             => $usersMapping,
    'blogs'             => $blogsMapping,
    'faq'               => $faqMapping,
    'help'              => $helpMapping,
];

$elasticUrl = !empty($tmvc->my_config['env']['ELASTIC_SEARCH_API_HOST']) ? $tmvc->my_config['env']['ELASTIC_SEARCH_API_HOST'] . '/' . $tmvc->my_config['env']['ELASTIC_SEARCH_INDEX'] : 'http://localhost/' . $tmvc->my_config['env']['ELASTIC_SEARCH_INDEX'];

if (empty($modules)) {
    $modules = array_keys($mappings);
}

$settings = [
    'index' => [
        'max_ngram_diff' => 10,
    ],
    'analysis' => [
        'filter' => [
            'edgeNGramFilter' => [
                'type'     => 'edgeNGram',
                'min_gram' => 2,
                'max_gram' => 50,
                'side'     => 'front',
            ],
            '2gramfilter' => [
                'type'     => 'ngram',
                'min_gram' => 2,
                'max_gram' => 10,
            ],
            '4gramfilter' => [
                'type'     => 'ngram',
                'min_gram' => 4,
                'max_gram' => 10,
            ],
            '3gramfilter' => [
                'type'     => 'ngram',
                'min_gram' => 3,
                'max_gram' => 3,
            ],
            'shingle_2' => [
                'type'             => 'shingle',
                'output_unigrams'  => false,
                'max_shingle_size' => 2,
                'min_shingle_size' => 2,
            ],
            'autocomplete_shingle_filter'=> [
                'type'             => 'shingle',
                'min_shingle_size' => 2,
                'max_shingle_size' => 5,
                'output_unigrams'  => false,
            ],
            'snowball' => [
                'type'     => 'snowball',
                'language' => 'English',
            ],
            'worddelimiter' => [
                'type' => 'word_delimiter',
            ],
            'stopwords' => [
                'type'        => 'stop',
                'stopwords'   => ['_english_'],
                'ignore_case' => true,
            ],
        ],
        'tokenizer' => [
            'path-tokenizer' => [
                'type'      => 'path_hierarchy',
                'delimiter' => ',',
            ],
            'coma_tokenizer' => [
                'type'    => 'pattern',
                'pattern' => ',',
            ],
        ],
        'analyzer' => [
            'categories_search_analyzer' => [
                'type'        => 'custom',
                'tokenizer'   => 'whitespace',
                'char_filter' => [
                    'html_strip',
                ],
                'filter' => [
                    'lowercase',
                    'porter_stem',
                ],
            ],
            'autocomplete_shigle_analyzer' => [
                'filter'=> [
                    'lowercase',
                    'asciifolding',
                    'autocomplete_shingle_filter',
                ],
                'tokenizer' => 'standard',
                'type'      => 'custom',
            ],
            'autocomplete_single_word_analyzer' => [
                'filter'=> [
                    'lowercase',
                    'asciifolding',
                ],
                'tokenizer' => 'standard',
                'type'      => 'custom',
            ],
            'autocomplete_analyzer'=> [
                'filter'=> [
                    'lowercase',
                ],
                'tokenizer' => 'keyword',
                'type'      => 'custom',
            ],
            'search_items_analyzer' => [
                'type'        => 'custom',
                'tokenizer'   => 'standard',
                'char_filter' => [
                    'html_strip',
                ],
                'filter' => [
                    'stopwords',
                    'asciifolding',
                    'lowercase',
                    'snowball',
                    'worddelimiter',
                ],
            ],
            'b2b_suggester_analyzer' => [
                'type'        => 'custom',
                'tokenizer'   => 'standard',
                'char_filter' => [
                    'html_strip',
                ],
                'filter' => [
                    'stopwords',
                    'asciifolding',
                    'lowercase',
                    'snowball',
                    'worddelimiter',
                ],
            ],
            'category_suggester_analyzer' => [
                'type'        => 'custom',
                'tokenizer'   => 'standard',
                'char_filter' => [
                    'html_strip',
                ],
                'filter' => [
                    'lowercase',
                    'asciifolding',
                    'worddelimiter',
                ],
            ],
            'tags_analyzer' => [
                'type'        => 'custom',
                'tokenizer'   => 'coma_tokenizer',
                'char_filter' => [
                    'html_strip',
                ],
                'filter' => [
                    'lowercase',
                    'porter_stem',
                ],
            ],
            "users_analyzer"    => [
                "type"          => "custom",
                "tokenizer"     => "standard",
                "char_filter"   => [
                    "html_strip"
                ],
                "filter"        => [
                    "kstem",
                    "lowercase",
                    "edgeNGramFilter"
                ],
            ],
            'items_tags_analyzer' => [
                'type'        => 'custom',
                'tokenizer'   => 'standard',
                'char_filter' => [
                    'html_strip',
                ],
                'filter' => [
                    'lowercase',
                    'asciifolding',
                    'porter_stem',
                ],
            ],
            'help_search_analyzer' => [
                'type'        => 'custom',
                'tokenizer'   => 'standard',
                'char_filter' => [
                    'html_strip',
                ],
                'filter' => [
                    'kstem',
                    'lowercase',
                    'edgeNGramFilter',
                ],
            ],
            'case_insensitive_sort' => [
                'tokenizer' => 'keyword',
                'filter'    => [
                    'trim',
                    'lowercase',
                ],
            ],
            'path-analyzer' => [
                'type'      => 'custom',
                'tokenizer' => 'path-tokenizer',
            ],
            '2gramanalyzer' => [
                'type'        => 'custom',
                'tokenizer'   => 'standard',
                'char_filter' => ['html_strip'],
                'filter'      => [
                    'lowercase',
                    '2gramfilter',
                ],
            ],
            '3gramanalyzer' => [
                'type'        => 'custom',
                'tokenizer'   => 'standard',
                'char_filter' => ['html_strip'],
                'filter'      => [
                    'lowercase',
                    '3gramfilter',
                ],
            ],
            '4gramanalyzer' => [
                'type'        => 'custom',
                'tokenizer'   => 'standard',
                'char_filter' => ['html_strip'],
                'filter'      => [
                    'lowercase',
                    '4gramfilter',
                ],
            ],
            'coma2gramanalyzer' => [
                'type'        => 'custom',
                'char_filter' => ['html_strip'],
                'tokenizer'   => 'coma_tokenizer',
                'filter'      => [
                    'lowercase',
                    '2gramfilter',
                ],
            ],
            'coma_analyzer' => [
                'type'        => 'custom',
                'char_filter' => ['html_strip'],
                'tokenizer'   => 'coma_tokenizer',
                'filter'      => [
                    'lowercase',
                ],
            ],
            'shingle_2' => [
                'type'        => 'custom',
                'tokenizer'   => 'standard',
                'char_filter' => ['html_strip'],
                'filter'      => [
                    'lowercase',
                    'shingle_2',
                ],
            ],
        ],
    ],
];

foreach ($modules as $module) {
    $mappings_array = [
        'mappings' => $mappings[$module],
        'settings' => $settings,
    ];

    mappings_put($elasticUrl . '_' . $module, $mappings_array);
}

function mappings_put($url, $mappings_array)
{
    $mappingJson = json_encode($mappings_array);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $mappingJson);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($mappingJson), ]
    );

    $curl_exec = curl_exec($ch);
    curl_close($ch);
    echo $url . ' - ' . $curl_exec . PHP_EOL;

    return '{"acknowledged":true}' == $curl_exec;
}
