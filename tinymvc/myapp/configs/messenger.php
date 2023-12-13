<?php

declare(strict_types=1);

use App\Messenger\Message;
use ExportPortal\Bridge\Symfony\Component\Messenger\Middleware\SendFailedAsyncMessageToFailureTransportMiddleware;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Mailer;
use Symfony\Component\Messenger\Retry\MultiplierRetryStrategy;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Symfony\Component\Notifier;

return function (ContainerInterface $container) {
    return [
        // Set default bus to command bus
        'default_bus'            => 'command.bus',

        // Set buses
        'buses'                  => [
            // Create command bus
            'command.bus' => [
                'middleware' => [
                    // Add failover middleware
                    SendFailedAsyncMessageToFailureTransportMiddleware::class => [
                        'failover',
                    ],
                ],
            ],

            // Create event bus
            'event.bus'   => [
                // Allow empty handlers for messages
                'allow_no_handlers'  => true,
                'middleware'         => [
                    // Add failover middleware
                    SendFailedAsyncMessageToFailureTransportMiddleware::class => [
                        'failover',
                    ],
                ],
            ],

            // Create query bus
            'query.bus'   => [
                'middleware'         => [
                    // Add failover middleware
                    SendFailedAsyncMessageToFailureTransportMiddleware::class => [
                        'failover',
                    ],
                ],
            ],
        ],

        // Set failure trasnport
        'failure_transport'      => 'failure',

        // Set transports
        'transports'             => [
            // region Base transports
            // Create sync trasnports
            'sync'                       => 'sync://',

            // Create failure transport
            'failure'                    => [
                'dsn'     => $container->getParameter('kernel.env.MESSENGER_DOCTRINE_TRANSPORT_DSN'),
                'options' => [
                    'queue_name' => 'failed',
                ],
            ],

            // Create failover transport
            'failover'                   => [
                'dsn'     => $container->getParameter('kernel.env.MESSENGER_DOCTRINE_TRANSPORT_DSN'),
                'options' => [
                    'queue_name' => 'failover',
                    'table_name' => 'messenger_messages_failover',
                ],
            ],

            // Create static transport
            'static'                     => [
                'dsn'            => $container->getParameter('kernel.env.MESSENGER_DOCTRINE_TRANSPORT_DSN'),
                'serializer'     => 'symfony_serializer',
                'retry_strategy' => 'fast',
                'options'        => [
                    'queue_name' => 'static',
                ],
            ],
            // endregion Base transports

            // region App transports
            'async_common'               => [
                'dsn'            => $container->getParameter('kernel.env.MESSENGER_AMPQ_TRANSPORT_DSN'),
                'serializer'     => 'symfony_serializer',
                'retry_strategy' => 'slow',
                'options'        => [
                    'exchange' => [
                        'name' => 'exportportal_messages_common',
                        'type' => 'topic',
                    ],
                    'queues'   => [
                        'exportportal_messages_users_processing'  => [
                            'binding_keys' => ['users.processing', 'users.#.processing'],
                        ],
                        'exportportal_messages_users_lifecycle' => [
                            'binding_keys' => ['users.lifecycle', 'users.#.lifecycle'],
                        ],
                        'exportportal_messages_companies_processing' => [
                            'binding_keys' => ['companies.processing', 'companies.#.processing'],
                        ],
                        'exportportal_messages_items_processing' => [
                            'binding_keys' => ['items.processing', 'items.#.processing'],
                        ],
                        'exportportal_messages_items_lifecycle' => [
                            'binding_keys' => ['items.lifecycle', 'items.#.lifecycle'],
                        ],
                    ],
                ],
            ],

            'async_users_processing'     => [
                'dsn'            => $container->getParameter('kernel.env.MESSENGER_AMPQ_TRANSPORT_DSN'),
                'serializer'     => 'symfony_serializer',
                'retry_strategy' => 'fast',
                'options'        => [
                    'exchange' => [
                        'default_publish_routing_key' => 'users.processing',
                        'name'                        => 'exportportal_messages_users_processing',
                        'type'                        => 'direct',
                    ],
                    'queues'   => [
                        'exportportal_messages_users_processing' => [
                            'binding_keys' => ['users.processing', 'users.#.processing'],
                        ],
                    ],
                ],
            ],

            'async_companies_processing' => [
                'dsn'            => $container->getParameter('kernel.env.MESSENGER_AMPQ_TRANSPORT_DSN'),
                'serializer'     => 'symfony_serializer',
                'retry_strategy' => 'fast',
                'options'        => [
                    'exchange' => [
                        'default_publish_routing_key' => 'companies.processing',
                        'name'                        => 'exportportal_messages_companies_processing',
                        'type'                        => 'direct',
                    ],
                    'queues'   => [
                        'exportportal_messages_companies_processing' => [
                            'binding_keys' => ['companies.processing', 'companies.#.processing'],
                        ],
                    ],
                ],
            ],

            'async_items_processing' => [
                'dsn'            => $container->getParameter('kernel.env.MESSENGER_AMPQ_TRANSPORT_DSN'),
                'serializer'     => 'symfony_serializer',
                'retry_strategy' => 'fast',
                'options'        => [
                    'exchange' => [
                        'default_publish_routing_key' => 'items.processing',
                        'name'                        => 'exportportal_messages_items_processing',
                        'type'                        => 'direct',
                    ],
                    'queues'   => [
                        'exportportal_messages_items_processing' => [
                            'binding_keys' => ['items.processing', 'items.#.processing'],
                        ],
                    ],
                ],
            ],

            'async_items_lifecycle'        => [
                'dsn'            => $container->getParameter('kernel.env.MESSENGER_AMPQ_TRANSPORT_DSN'),
                'serializer'     => 'symfony_serializer',
                'retry_strategy' => 'fast',
                'options'        => [
                    'exchange' => [
                        'default_publish_routing_key' => 'items.lifecycle',
                        'name'                        => 'exportportal_messages_items_lifecycle',
                        'type'                        => 'direct',
                    ],
                    'queues'   => [
                        'exportportal_messages_items_lifecycle' => [
                            'binding_keys' => ['items.lifecycle', 'items.#.lifecycle'],
                        ],
                    ],
                ],
            ],

            'async_users_lifecycle'      => [
                'dsn'            => $container->getParameter('kernel.env.MESSENGER_AMPQ_TRANSPORT_DSN'),
                'serializer'     => 'symfony_serializer',
                'retry_strategy' => 'fast',
                'options'        => [
                    'exchange' => [
                        'default_publish_routing_key' => 'users.lifecycle',
                        'name'                        => 'exportportal_messages_users_lifecycle',
                        'type'                        => 'direct',
                    ],
                    'queues'   => [
                        'exportportal_messages_users_lifecycle' => [
                            'binding_keys' => ['users.lifecycle', 'users.#.lifecycle'],
                        ],
                    ],
                ],
            ],

            'async_epdocs_all'           => [
                'dsn'            => $container->getParameter('kernel.env.MESSENGER_AMPQ_TRANSPORT_DSN'),
                'serializer'     => 'symfony_serializer',
                'retry_strategy' => 'fast',
                'options'        => [
                    'exchange' => [
                        'name' => 'exportportal_messages_epdocs_all',
                        'type' => 'topic',
                    ],
                    'queues'   => [
                        'exportportal_messages_epdocs_processing' => [
                            'binding_keys' => ['epdocs.processing', 'epdocs.#.processing'],
                        ],
                    ],
                ],
            ],

            'async_elastic_all'          => [
                'dsn'            => $container->getParameter('kernel.env.MESSENGER_AMPQ_TRANSPORT_DSN'),
                'serializer'     => 'symfony_serializer',
                'retry_strategy' => 'fast',
                'options'        => [
                    'exchange' => [
                        'name' => 'exportportal_messages_elastic_all',
                        'type' => 'topic',
                    ],
                    'queues'   => [
                        'exportportal_messages_elastic_index' => [
                            'binding_keys' => ['elastic.index', 'elastic.#.index'],
                        ],
                    ],
                ],
            ],

            'async_order_all'            => [
                'dsn'            => $container->getParameter('kernel.env.MESSENGER_AMPQ_TRANSPORT_DSN'),
                'serializer'     => 'symfony_serializer',
                'retry_strategy' => 'slow',
                'options'        => [
                    'exchange' => [
                        'name' => 'exportportal_messages_order_all',
                        'type' => 'topic',
                    ],
                    'queues'   => [
                        'exportportal_messages_order_documents' => [
                            'binding_keys' => ['order.documents', 'order.#.documents'],
                        ],
                    ],
                ],
            ],
            // endregion App transports

            // region Notifier transports
            'async_notifier_all'         => [
                'dsn'            => $container->getParameter('kernel.env.MESSENGER_AMPQ_TRANSPORT_DSN'),
                'retry_strategy' => 'normal',
                'options'        => [
                    'exchange' => [
                        'name' => 'exportportal_messages_notifier_all',
                        'type' => 'topic',
                    ],
                    'queues'   => [
                        'exportportal_messages_notifier_sms'  => [
                            'binding_keys' => ['sms', '#.sms'],
                        ],
                        'exportportal_messages_notifier_chat' => [
                            'binding_keys' => ['chat', '#.chat'],
                        ],
                        'exportportal_messages_notifier_email' => [
                            'binding_keys' => ['email', '#.email'],
                        ],
                        'exportportal_messages_notifier_push' => [
                            'binding_keys' => ['push', '#.push'],
                        ],
                        'exportportal_messages_notifier_stored' => [
                            'binding_keys' => ['stored', '#.stored'],
                        ],
                    ],
                ],
            ],

            'async_notifier_sms'         => [
                'dsn'            => $container->getParameter('kernel.env.MESSENGER_AMPQ_TRANSPORT_DSN'),
                'retry_strategy' => 'fast',
                'options'        => [
                    'exchange' => [
                        'default_publish_routing_key' => 'sms',
                        'name'                        => 'exportportal_messages_notifier_sms',
                        'type'                        => 'direct',
                    ],
                    'queues'   => [
                        'exportportal_messages_notifier_sms' => [
                            'binding_keys' => ['sms', '#.sms'],
                        ],
                    ],
                ],
            ],

            'async_notifier_chat'        => [
                'dsn'            => $container->getParameter('kernel.env.MESSENGER_AMPQ_TRANSPORT_DSN'),
                'retry_strategy' => 'fast',
                'options'        => [
                    'exchange' => [
                        'default_publish_routing_key' => 'chat',
                        'name'                        => 'exportportal_messages_notifier_chat',
                        'type'                        => 'direct',
                    ],
                    'queues'   => [
                        'exportportal_messages_notifier_chat' => [
                            'binding_keys' => ['chat', '#.chat'],
                        ],
                    ],
                ],
            ],

            'async_notifier_push'        => [
                'dsn'            => $container->getParameter('kernel.env.MESSENGER_AMPQ_TRANSPORT_DSN'),
                'retry_strategy' => 'fast',
                'options'        => [
                    'exchange' => [
                        'default_publish_routing_key' => 'push',
                        'name'                        => 'exportportal_messages_notifier_push',
                        'type'                        => 'direct',
                    ],
                    'queues'   => [
                        'exportportal_messages_notifier_push' => [
                            'binding_keys' => ['push', '#.push'],
                        ],
                    ],
                ],
            ],

            'async_notifier_stored'      => [
                'dsn'            => $container->getParameter('kernel.env.MESSENGER_AMPQ_TRANSPORT_DSN'),
                'retry_strategy' => 'fast',
                'options'        => [
                    'exchange' => [
                        'default_publish_routing_key' => 'stored',
                        'name'                        => 'exportportal_messages_notifier_stored',
                        'type'                        => 'direct',
                    ],
                    'queues'   => [
                        'exportportal_messages_notifier_stored' => [
                            'binding_keys' => ['stored', '#.stored'],
                        ],
                    ],
                ],
            ],

            'async_notifier_email'       => [
                'dsn'            => $container->getParameter('kernel.env.MESSENGER_AMPQ_TRANSPORT_DSN'),
                'retry_strategy' => 'fast',
                'options'        => [
                    'exchange' => [
                        'default_publish_routing_key' => 'email',
                        'name'                        => 'exportportal_messages_notifier_email',
                        'type'                        => 'direct',
                    ],
                    'queues'   => [
                        'exportportal_messages_notifier_email' => [
                            'binding_keys' => ['email', '#.email'],
                        ],
                    ],
                ],
            ],
            // endregion Notifier transports

            // region Matrix transports
            'async_matrix_all'           => [
                'dsn'            => $container->getParameter('kernel.env.MESSENGER_AMPQ_TRANSPORT_DSN'),
                'serializer'     => 'symfony_serializer',
                'retry_strategy' => 'slow',
                'options'        => [
                    'exchange' => [
                        'name' => 'exportportal_messages_matrix_all',
                        'type' => 'topic',
                    ],
                    'queues'   => [
                        'exportportal_messages_matrix_users'  => [
                            'binding_keys' => ['matrix.users', 'matrix.#.users'],
                        ],
                        'exportportal_messages_matrix_rooms' => [
                            'binding_keys' => ['matrix.rooms', 'matrix.#.rooms'],
                        ],
                        'exportportal_messages_matrix_lifecycle' => [
                            'binding_keys' => ['matrix.lifecycle', 'matrix.#.lifecycle'],
                        ],
                    ],
                ],
            ],

            'async_matrix_users'         => [
                'dsn'            => $container->getParameter('kernel.env.MESSENGER_AMPQ_TRANSPORT_DSN'),
                'serializer'     => 'symfony_serializer',
                'retry_strategy' => 'normal',
                'options'        => [
                    'exchange' => [
                        'default_publish_routing_key' => 'matrix.users',
                        'name'                        => 'exportportal_messages_matrix_users',
                        'type'                        => 'direct',
                    ],
                    'queues'   => [
                        'exportportal_messages_matrix_users' => [
                            'binding_keys' => ['matrix.users', 'matrix.#.users'],
                        ],
                    ],
                ],
            ],

            'async_matrix_rooms'         => [
                'dsn'            => $container->getParameter('kernel.env.MESSENGER_AMPQ_TRANSPORT_DSN'),
                'serializer'     => 'symfony_serializer',
                'retry_strategy' => 'normal',
                'options'        => [
                    'exchange' => [
                        'default_publish_routing_key' => 'matrix.rooms',
                        'name'                        => 'exportportal_messages_matrix_rooms',
                        'type'                        => 'direct',
                    ],
                    'queues'   => [
                        'exportportal_messages_matrix_rooms' => [
                            'binding_keys' => ['matrix.rooms', 'matrix.#.rooms'],
                        ],
                    ],
                ],
            ],

            'async_matrix_lifecycle'     => [
                'dsn'            => $container->getParameter('kernel.env.MESSENGER_AMPQ_TRANSPORT_DSN'),
                'serializer'     => 'symfony_serializer',
                'retry_strategy' => 'fast',
                'options'        => [
                    'exchange' => [
                        'default_publish_routing_key' => 'matrix.lifecycle',
                        'name'                        => 'exportportal_messages_matrix_lifecycle',
                        'type'                        => 'direct',
                    ],
                    'queues'   => [
                        'exportportal_messages_matrix_lifecycle' => [
                            'binding_keys' => ['matrix.lifecycle', 'matrix.#.lifecycle'],
                        ],
                    ],
                ],
            ],
            // endregion Matrix transports
        ],

        // Set default serializer
        'default_serializer'     => 'native_serializer',

        // Set serializers
        'serializers'            => [
            // Create native serializer
            'native_serializer'  => [
                'service' => PhpSerializer::class,
            ],

            // Create symfony-like serializer
            'symfony_serializer' => [
                'service' => Serializer::class,
                'format'  => 'json',
                'context' => [],
            ],
        ],

        // Set default retry strategy
        'default_retry_strategy' => 'slow',

        // Set retry strategies
        'retry_strategies'       => [
            // Create slow strategy
            'slow' => [
                'service' => MultiplierRetryStrategy::class,
                'options' => [
                    'delay' => 2000,
                ],
            ],

            // Create normal strategy
            'normal' => [
                'service' => MultiplierRetryStrategy::class,
                'options' => [
                    'delay' => 1000,
                ],
            ],

            // Create fast strategy
            'fast' => [
                'service' => MultiplierRetryStrategy::class,
                'options' => [
                    'delay' => 500,
                ],
            ],
        ],

        // Set the paths to the handler collections
        // This method is used due to fact that we don't have a proper DI
        'handlers_registry'      => [
            'add_classes/Messenger/handlers.php',
        ],

        // Set messages routing
        'routing'                => [
            // Test message
            Message\Command\SayHelloWorld::class                                => ['sync'],

            // Notifier messages
            Notifier\Message\ChatMessage::class                                 => ['async_notifier_chat'],
            Notifier\Message\SentMessage::class                                 => ['async_notifier_sms'],
            Notifier\Message\PushMessage::class                                 => ['async_notifier_push'],
            Mailer\Messenger\SendEmailMessage::class                            => ['async_notifier_email'],
            \ExportPortal\Contracts\Notifier\Message\StorageMessage::class      => ['async_notifier_stored'],

            // App event messages
            Message\Event\UserWasActiveEvent::class                             => ['async_users_lifecycle'],
            Message\Event\UserWasVerifiedEvent::class                           => ['async_users_lifecycle'],
            Message\Event\UserHasRegisteredEvent::class                         => ['async_users_lifecycle'],
            Message\Event\UserUpdatedProfileEvent::class                        => ['async_users_lifecycle'],
            Message\Event\UserUpdatedCompanyEvent::class                        => ['async_users_lifecycle'],
            Message\Event\UserWasUnrestrictedEvent::class                       => ['async_users_lifecycle'],
            Message\Event\UserWasRestrictedEvent::class                         => ['async_users_lifecycle'],
            Message\Event\UserWasUnblockedEvent::class                          => ['async_users_lifecycle'],
            Message\Event\UserGroupChangedEvent::class                          => ['async_users_lifecycle'],
            Message\Event\UserWasDeletedEvent::class                            => ['async_users_lifecycle'],
            Message\Event\UserWasBlockedEvent::class                            => ['async_users_lifecycle'],
            Message\Event\UserWasMutedEvent::class                              => ['async_users_lifecycle'],
            Message\Event\UserWasUnmutedEvent::class                            => ['async_users_lifecycle'],
            Message\Event\UserWasMarkedFakeEvent::class                         => ['async_users_lifecycle'],
            Message\Event\UserWasMarkedRealEvent::class                         => ['async_users_lifecycle'],
            Message\Event\Lifecycle\UserUpdatedCompanyEvent::class              => ['async_users_lifecycle'],
            Message\Event\Lifecycle\UserUpdatedCompanyLogoEvent::class          => ['async_users_lifecycle'],
            Message\Event\Lifecycle\UserGroupChangedEvent::class                => ['async_users_lifecycle'],
            Message\Event\Lifecycle\UserUpdatedSellerCompanyEvent::class        => ['async_users_lifecycle'],
            Message\Event\Lifecycle\UserUpdatedBuyerCompanyEvent::class         => ['async_users_lifecycle'],
            Message\Event\Lifecycle\UserUpdatedShipperCompanyEvent::class       => ['async_users_lifecycle'],
            Message\Event\Lifecycle\UserHasRegisteredEvent::class               => ['async_users_lifecycle'],
            Message\Event\Lifecycle\UserUpdatedAdditionalDataEvent::class       => ['async_users_lifecycle'],
            Message\Event\Lifecycle\UserUpdatedCompanyAddendumEvent::class      => ['async_users_lifecycle'],
            Message\Event\Lifecycle\UserUpdatedShipperCompanyLogoEvent::class   => ['async_users_lifecycle'],
            Message\Event\Lifecycle\UserUpdatedSellerCompanyLogoEvent::class    => ['async_users_lifecycle'],
            Message\Event\Lifecycle\UserUpdatedRelatedCompanyEvent::class       => ['async_users_lifecycle'],
            Message\Event\Lifecycle\UserUpdatedEmailEvent::class                => ['async_users_lifecycle'],
            Message\Event\Lifecycle\UserUpdatedLogoEvent::class                 => ['async_users_lifecycle'],
            Message\Event\Lifecycle\UserUpdatedPhotoEvent::class                => ['async_users_lifecycle'],
            Message\Event\Lifecycle\UserUpdatedProfileEvent::class              => ['async_users_lifecycle'],
            Message\Event\Lifecycle\UserUpdatedRightsEvent::class               => ['async_users_lifecycle'],
            Message\Event\Lifecycle\UserWasActiveEvent::class                   => ['async_users_lifecycle'],
            Message\Event\Lifecycle\UserWasBlockedEvent::class                  => ['async_users_lifecycle'],
            Message\Event\Lifecycle\UserWasDeletedEvent::class                  => ['async_users_lifecycle'],
            Message\Event\Lifecycle\UserWasMarkedFakeEvent::class               => ['async_users_lifecycle'],
            Message\Event\Lifecycle\UserWasMarkedRealEvent::class               => ['async_users_lifecycle'],
            Message\Event\Lifecycle\UserWasMutedEvent::class                    => ['async_users_lifecycle'],
            Message\Event\Lifecycle\UserWasRestrictedEvent::class               => ['async_users_lifecycle'],
            Message\Event\Lifecycle\UserWasUnblockedEvent::class                => ['async_users_lifecycle'],
            Message\Event\Lifecycle\UserWasUnmutedEvent::class                  => ['async_users_lifecycle'],
            Message\Event\Lifecycle\UserWasUnrestrictedEvent::class             => ['async_users_lifecycle'],
            Message\Event\Lifecycle\UserWasVerifiedEvent::class                 => ['async_users_lifecycle'],
            Message\Event\Lifecycle\UserWasRestoredEvent::class                 => ['async_users_lifecycle'],
            Message\Event\Lifecycle\UserWasRemovedEvent::class                  => ['async_users_lifecycle'],
            Message\Event\Profile\CreateEditRequestEvent::class                 => ['async_users_lifecycle'],
            Message\Event\Profile\AcceptedEditRequestEvent::class               => ['async_users_lifecycle'],
            Message\Event\Profile\DeclinedEditRequestEvent::class               => ['async_users_lifecycle'],
            Message\Event\Company\CreateEditRequestEvent::class                 => ['async_users_lifecycle'],
            Message\Event\Company\DeclinedEditRequestEvent::class               => ['async_users_lifecycle'],
            Message\Event\Company\AcceptedEditRequestEvent::class               => ['async_users_lifecycle'],

            // App command messages
            Message\Command\AddSampleOrderDocument::class                  => ['async_order_all'],
            Message\Command\SaveBuyerIndustryOfInterest::class             => ['async_users_lifecycle'],
            Message\Command\UpdateUserIdForBuyerIndustryStats::class       => ['async_users_lifecycle'],
            Message\Command\ElasticSearch\IndexAnswer::class               => ['async_elastic_all'],
            Message\Command\ElasticSearch\ReIndexAnswer::class             => ['async_elastic_all'],
            Message\Command\ElasticSearch\IndexQuestion::class             => ['async_elastic_all'],
            Message\Command\ElasticSearch\ReIndexQuestion::class           => ['async_elastic_all'],
            Message\Command\ElasticSearch\IndexB2bRequest::class           => ['async_elastic_all'],
            Message\Command\ElasticSearch\ReIndexB2bRequest::class         => ['async_elastic_all'],
            Message\Command\ElasticSearch\RemoveB2bRequest::class          => ['async_elastic_all'],
            Message\Command\ElasticSearch\IndexSellerCompany::class        => ['async_elastic_all'],
            Message\Command\ElasticSearch\ReIndexSellerCompany::class      => ['async_elastic_all'],
            Message\Command\ElasticSearch\RemoveSellerCompany::class       => ['async_elastic_all'],
            Message\Command\Profile\AcceptTemporaryFile::class             => ['async_users_processing'],
            Message\Command\Company\AcceptTemporaryFile::class             => ['async_companies_processing'],
            Message\Command\Company\UpdateCompanyVideo::class              => ['async_companies_processing'],
            Message\Command\Company\RemoveSellerLogoFiles::class           => ['async_companies_processing'],
            Message\Command\Company\RemoveSellerFiles::class               => ['async_companies_processing'],
            Message\Command\Company\UpdateSellerLogo::class                => ['async_companies_processing'],
            Message\Command\EPDocs\DeleteFile::class                       => ['async_epdocs_all'],
            Message\Command\Media\Legacy\ProcessImage::class               => ['async_users_processing'],
            Message\Command\SaveViewItemsLog::class                        => ['async_items_processing'],

            // Matrix command messages
            Message\Command\SyncMatrixUser::class                               => ['async_matrix_users'],
            Message\Command\CreateMatrixUser::class                             => ['async_matrix_users'],
            Message\Command\CreateMatrixRoom::class                             => ['async_matrix_rooms'],
            Message\Command\DeleteMatrixRoom::class                             => ['async_matrix_rooms'],
            Message\Command\CreateMatrixSpace::class                            => ['async_matrix_rooms'],
            Message\Command\JoinMatrixRoomWithId::class                         => ['async_matrix_users'],
            Message\Command\JoinMatrixRoomWithMxId::class                       => ['async_matrix_users'],
            Message\Command\LeaveMatrixRoomById::class                          => ['async_matrix_users'],
            Message\Command\LeaveMatrixRoomByMxId::class                        => ['async_matrix_users'],
            Message\Command\DeactivateKnownMatrixUser::class                    => ['async_matrix_users'],
            Message\Command\DeactivateUnknownMatrixUser::class                  => ['async_matrix_users'],
            Message\Command\CreateDirectMatrixChatRoom::class                   => ['async_matrix_rooms'],
            Message\Command\CreateDirectMatrixChatRoomNow::class                => ['sync'],
            Message\Command\CreateMatrixServerNoticesRoom::class                => ['async_matrix_rooms'],
            Message\Command\CreateMatrixPorfileRoom::class                      => ['async_matrix_rooms'],
            Message\Command\Matrix\UpdateServerNoticesRoom::class               => ['async_matrix_rooms'],
            Message\Command\Matrix\CreateCargoRoom::class                       => ['async_matrix_rooms'],
            Message\Command\Matrix\UpdateCargoRoom::class                       => ['async_matrix_rooms'],

            // Matrix event messages
            Message\Event\MatrixUserAddedEvent::class                           => ['async_matrix_lifecycle'],
            Message\Event\MatrixChatRoomAddedEvent::class                       => ['async_matrix_lifecycle'],
            Message\Event\MatrixChatSpaceAddedEvent::class                      => ['async_matrix_lifecycle'],
            Message\Event\MatrixUserKeysCreatedEvent::class                     => ['async_matrix_lifecycle'],
            Message\Event\MatrixUserProfileRoomAddedEvent::class                => ['async_matrix_lifecycle'],
            Message\Event\MatrixUserServerNoticesRoomAddedEvent::class          => ['async_matrix_lifecycle'],
            Message\Event\Matrix\UserCargoRoomAddedEvent::class                 => ['async_matrix_lifecycle'],
            Message\Event\Matrix\UserCargoRoomUpdatedEvent::class               => ['async_matrix_lifecycle'],

            // Files
            App\Messenger\Message\Command\Media\CopyFileToStorage::class             => ['async_items_lifecycle'],

            // Droplist Commands
            App\Messenger\Message\Command\DropList\ReplaceImage::class               => ['async_items_lifecycle'],

            // Droplist Events
            App\Messenger\Message\Event\Product\ProductInStockEvent::class             => ['async_items_lifecycle'],
            App\Messenger\Message\Event\Product\ProductOutOfStockEvent::class          => ['async_items_lifecycle'],
            App\Messenger\Message\Event\Product\ProductPendingRequestEvent::class      => ['async_items_lifecycle'],
            App\Messenger\Message\Event\Product\ProductPriceChangedEvent::class        => ['async_items_lifecycle'],
            App\Messenger\Message\Event\Product\ProductWasBlockedEvent::class          => ['async_items_lifecycle'],
            App\Messenger\Message\Event\Product\ProductWasDraftEvent::class            => ['async_items_lifecycle'],
            App\Messenger\Message\Event\Product\ProductWasModeratedEvent::class        => ['async_items_lifecycle'],
            App\Messenger\Message\Event\Product\ProductWasUnblockedEvent::class        => ['async_items_lifecycle'],
            App\Messenger\Message\Event\Product\ProductWasUpdatedEvent::class          => ['async_items_lifecycle'],
            App\Messenger\Message\Event\Droplist\DroplistEntryPriceChangedEvent::class => ['async_items_lifecycle'],
            App\Messenger\Message\Event\Product\ProductChangedVisibilityEvent::class   => ['async_items_lifecycle'],
        ],
    ];
};
