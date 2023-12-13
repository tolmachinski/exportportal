<?php

$questionsMapping = array(
    "properties"            => array(
        "id_question"           => array("type" => "long"),
        "id_user"               => array("type" => "long"),
        "id_category"           => array("type" => "long"),
        "id_category_lang"      => array("type" => "keyword"),
        "id_country"        => array( "type" => "long"),
        "title_question"    => array(
            "type"              => "text",
            "fields"            => array(
                "ngrams"            => array(
                    "type"              => "text",
                    "analyzer"          => "search_items_analyzer",
                )
            )
        ),
        "text_question"     => array(
            "type"              => "text",
            "fields"            => array(
                "ngrams"            => array(
                    "type"              => "text",
                    "analyzer"          => "search_items_analyzer",
                )
            )
        ),
        "date_question"     => array(
            "type"              => "date",
            "format"            => "strict_date_optional_time||epoch_millis||yyyy-MM-dd HH:mm:ss"
        ),
        "moderated"         => array("type" => "byte"),
        "count_answers"     => array("type" => "short"),
        "views"             => array("type" => "integer"),
        "was_searched"      => array("type" => "integer"),
        "fname"             => array("type" => "text"),
        "lname"             => array("type" => "text"),
        "user_photo"        => array("type" => "text"),
        "user_group"        => array("type" => "text"),
        "country"           => array("type" => "text"),
        "lang"              => array("type" => "text"),
        "answers"           => array(
            "type"              => "nested",
            "properties"        => array(
                "id_answer"         => array("type" => "long"),
                "id_question"       => array("type" => "long"),
                "id_user"           => array("type" => "long"),
                "text_answer"       => array("type" => "text"),
                "date_answer"       => array(
                    "type"              => "date",
                    "format"            => "strict_date_optional_time||epoch_millis||yyyy-MM-dd HH:mm:ss"
                ),
                "moderated"         => array("type" => "byte"),
                "count_plus"        => array("type" => "short"),
                "count_minus"       => array("type" => "short"),
                "count_comments"    => array("type" => "short"),
                "fname"             => array("type" => "text"),
                "lname"             => array("type" => "text"),
                "user_photo"        => array("type" => "text"),
                "user_group"        => array("type" => "text"),
            )
        ),
        'category'        => [
            'properties'    => [
                'id'    => ['type' => 'integer'],
                'title' => ['type' => 'text'],
                'slug'  => ['type' => 'keyword'],
            ],
        ],
    )
);
