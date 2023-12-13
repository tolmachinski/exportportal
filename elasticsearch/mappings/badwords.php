<?php
$badWordsMapping = array(
    "properties" => array(
        "language" => array("type" => "text"),
        "words" => array(
            "type" => "text",
            "fields" => array(
                "keyword" => array(
                    "type" => "keyword"
                )
            )
        )
    )
);
