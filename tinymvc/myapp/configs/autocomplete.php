<?php

declare(strict_types=1);

return [
    // The algorithm used for creating the hashes
    'has_algo'          => 'sha512',

    // The cookies key that is used to store completion metadata
    'cookie_key'        => '_ep_cmpl',

    // The TTL of the cookies
    'cookie_ttl'        => 60 * 60 * 24 * 365 * 50,

    // User analitics tracking reference
    'user_track_cookie' => 'ANALITICS_CT_SUID',

    // The cookie that contains the user reference
    'user_ref_cookie'   => '_ep_ucmpl',

    // The TTL of the user ref cookie
    'user_ref_ttl'      => 60 * 60 * 24 * 365 * 50,

    // Url mapping for subdomains
    'url_mapping'     => [
        '_b'  => __BLOG_URL,
        '_e'  => __SHIPPER_URL,
        '_c'  => __COMMUNITY_URL,
        '_bl' => __BLOGGERS_URL,
    ],
];
