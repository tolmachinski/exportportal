<?php
$companyMapping = array(
    "properties" => array(
        "id_company" => array("type" => "long"),
        "parent_company" => array("type" => "long"),
        "index_name" => array("type" => "text"),
        "parent_index_name" => array("type" => "text"),
        "parent_name_company" => array("type" => "text"),
        "parent_type_company" => array("type" => "text"),
        "index_name_temp" => array("type" => "text"),
        "id_user" => array("type" => "long"),
        "business_number" => array("type" => "text"),
        "user_acces" => array("type" => "text"),
        "name_company" => array(
            "fielddata" => true,
            "type" => "text",
            "fields" => array(
                "ngrams" => array(
                    "type" => "text",
                    "analyzer" => "2gramanalyzer",
                    "search_analyzer" => "standard"
                ),
                "keyword" => array(
                    "fielddata" => true,
                    "type" => "text",
                    "analyzer" => "case_insensitive_sort"
                )
            )
        ),
        "type_company" => array("type" => "text"),
        "id_country" => array("type" => "long"),
        "country" => array("type" => "text"),
        "id_country_country" => array(
            "type" => "keyword",
        ),
		"company_industries" => array(
            "type" => "text",
            "analyzer" => "pattern",
            "fielddata" => true,
            "fields" => array(
                "path" => array(
                    "type" =>  "text",
                    "analyzer" => "path-analyzer",
                    "search_analyzer" => "keyword",
                    "fielddata" => true
                )
            )
        ),
		"company_categories" => array(
            "type" => "text",
            "analyzer" => "pattern",
            "fielddata" => true,
            "fields" => array(
                "path" => array(
                    "type" =>  "text",
                    "analyzer" => "path-analyzer",
                    "search_analyzer" => "keyword",
                    "fielddata" => true
                )
            )
        ),
        "id_state" => array("type" => "long"),
        "id_city" => array("type" => "long"),
        "id_type" => array("type" => "long"),
        "id_type_name" => array(
            "type" => "keyword",
        ),
        "address_company" => array("type" => "text"),
        "longitude" => array("type" => "text"),
        "latitude" => array("type" => "text"),
        "zip_company" => array("type" => "text"),
        "phone_code_company" => array("type" => "text"),
        "phone_company" => array("type" => "text"),
        "fax_code_company" => array("type" => "text"),
        "fax_company" => array("type" => "text"),
        "email_company" => array("type" => "text"),
        "description_company" => array(
            "type" => "text",
            "fields" => array(
                "ngrams" => array(
                    "type" => "text",
                    "analyzer" => "2gramanalyzer",
                    "search_analyzer" => "standard"
                )
            )
        ),
        "video_company" => array("type" => "text"),
        "video_company_code" => array("type" => "text"),
        "video_company_source" => array("type" => "text"),
        "video_company_image" => array("type" => "text"),
        "employees_company" => array("type" => "long"),
        "revenue_company" => array("type" => "text"),
        "logo_company" => array("type" => "text"),
        "visible_company" => array("type" => "long"),
        "rating_count_company" => array("type" => "long"),
        "rating_company" => array("type" => "long"),
        "registered_company" => array(
            "format" => "strict_date_optional_time||epoch_millis||yyyy-MM-dd HH:mm:ss",
            "type" => "date"
        ),
        "last_indexed_date" => array(
            "format" => "strict_date_optional_time||epoch_millis",
            "type" => "date"
        ),
        "blocked" => array("type" => "long"),
        "accreditation" => array("type" => "long"),
        "profile_completed" => array("type" => "long"),
        "company_profile_completion" => array("type" => "text"),
        "user_name" => array("type" => "text"),
        "status" => array("type" => "text"),
        "registration_date" => array(
            "format" => "strict_date_optional_time||epoch_millis||yyyy-MM-dd HH:mm:ss",
            "type" => "date"
        ),
        "user_group_name" => array("type" => "text"),
        "user_group_name_sufix" => array("type" => "text"),
        "user_group" => array("type" => "long"),
        "is_featured" => array("type" => "long"),
        "is_verified" => array("type" => "byte")
    )
);
