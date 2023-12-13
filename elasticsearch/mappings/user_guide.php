<?php

$user_guideMapping = [
    "properties" => [
        "id_menu" => ["type" => "integer"],
        "id_parent" => ["type" => "integer"],
        "menu_title" => [
            "type" => "text",
            "fields" => [
                "ngrams" => [
                    "type" => "text"
                ]
            ]
        ],
        "menu_alias" => [
            "type" => "keyword",
        ],
        "menu_icon" => [
            "type" => "keyword",
        ],
        "menu_description" => [
            "type" => "text",
            "fields" => [
                "ngrams" => [
                    "type" => "text",
                    "analyzer" => "search_items_analyzer",
                ]
            ]
        ],
        "menu_intro" => [
            "type" => "text",
            "fields" => [
                "ngrams" => [
                    "type" => "text",
                    "analyzer" => "search_items_analyzer",
                ]
            ]
        ],
        "menu_video_buyer" => [
            "type" => "keyword",
        ],
        "menu_video_seller" => [
            "type" => "keyword",
        ],
        "menu_video_shipper" => [
            "type" => "keyword",
        ],
        "menu_breadcrumbs" => [
            "type" => "text",
            "fields" => [
                "ngrams" => [
                    "type" => "text"
                ]
            ]
        ],
        "menu_children" => [
            "type" => "text",
            "fields" => [
                "ngrams" => [
                    "type" => "text"
                ]
            ]
        ],
        "menu_position" => [
            "type" => "byte",
        ],
        "menu_actualized" => [
            "type" => "byte",
        ],
        "rel_user_types" => [
            "type" => "nested",
        ],
    ]
];
