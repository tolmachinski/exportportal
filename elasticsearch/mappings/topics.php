<?php

$topicsMapping = [
    "properties" => [
        "id_topic" => ["type" => "integer"],
        "title_topic" => [
            "type" => "text",
            "fields" => [
                "ngrams" => [
                    "type" => "text",
                    "analyzer" => "search_items_analyzer"
                ]
            ]
        ],
        "text_topic_small" => [
            "type" => "text",
            "fields" => [
                "ngrams" => [
                    "type" => "text",
                    "analyzer" => "search_items_analyzer"
                ]
            ]
        ],
        "text_topic" => [
            "type" => "text",
            "fields" => [
                "ngrams" => [
                    "type" => "text",
                    "analyzer" => "search_items_analyzer"
                ]
            ]
        ],
        "date_topic" => [
            "type" => "date",
            "format" => "strict_date_optional_time||epoch_millis||yyyy-MM-dd HH:mm:ss"
        ],
        "topics_i18n" => [
            "type" => "nested"
        ],
    ]
];
