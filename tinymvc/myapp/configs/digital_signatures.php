<?php

use App\Common\DigitalSignature\Provider\NullProvider;
use App\DigitalSignature\Provider\DocuSign\DocuSign;
use App\Envelope\SigningMecahisms;

return [
    'services' => [
        SigningMecahisms::NATIVE   => [
            'type' => NullProvider::class,
        ],

        SigningMecahisms::DOCUSIGN => [
            'type'    => DocuSign::class,
            'options' => [
                'base_path'              => config('env.DOCUSIGN_API_URL'),
                'account_id'             => config('env.DOCUSIGN_ACCOUNT_ID'),
                'auth_client'            => 'docusign',
                'templates'              => [
                    'email_subject' => 'New Document for Signature | Export Portal',
                    'email_message' => <<<BODY
                    You have a new document to review and sign from Export Portal.

                    Please contact us with any questions or concerns.
                    Best,
                    The Export Portal Support team
                    support@exportportal.com
                    BODY,
                ],
            ],
        ],
    ],
];
