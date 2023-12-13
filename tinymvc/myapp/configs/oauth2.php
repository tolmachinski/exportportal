<?php

use App\Common\OAuth2\Client\Token\Storage\PhpFileStorage;
use App\OAuth2\Client\Provider\DocuSign;

return [
    'clients'   => [
        'docusign' => [
            'stateless'     => false,
            'grant_type'    => 'authorization_code',
            'client_scopes' => [
                DocuSign::SCOPE_EXTENDED,
                DocuSign::SCOPE_SIGNATURE,
            ],
            'provider'      => [
                'type'               => DocuSign::class,
                'client_id'          => config('env.DOCUSIGN_CLIENT_ID'),
                'client_secret'      => config('env.DOCUSIGN_CLIENT_OAUTH_SECRET'),
                'auth_server_url'    => config('env.DOCUSIGN_URL'),
                'default_account_id' => config('env.DOCUSIGN_ACCOUNT_ID'),
                'response_type'      => 'code', // Use 'token' for implicit grant and 'code' for authorization grant
                'redirect_uri'       => __SITE_URL . 'oauth2/process?type=docusign',
                'silent_auth'        => false,
            ],
            'storage'       => [
                'type'    => PhpFileStorage::class,
                'options' => [
                    'target'     => \App\Common\VAR_PATH . '/cache/auth/oauth2/docusign',
                    'visibility' => 'private'
                ],
            ],
        ],
    ],
];
