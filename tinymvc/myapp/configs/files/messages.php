<?php

return array(
    'attach' => array(
        'limit'            => 10,
        'rules'            => array(
            'format'           => 'jpg,jpeg,png,pdf,doc,docx',
            'size'             => 10 * 1000 * 1000,
            'size_placeholder' => '10MB',
        ),
    ),
);
