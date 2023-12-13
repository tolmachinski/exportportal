<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\ContainerInterface;

return function (ContainerInterface $container) {
    return [
        'links'    => [
            'default' => [
                'link'   => "{$container->getParameter('kernel.project_dir')}/public/storage",
                'target' => "{$container->getParameter('kernel.project_dir')}/var/app/public",
            ],

            'temp' => [
                'link'   => "{$container->getParameter('kernel.project_dir')}/public/temp",
                'target' => "{$container->getParameter('kernel.project_dir')}/var/temp",
            ],
        ],

        'storages' => [
            // Default storage
            'default.storage' => [
                'adapter'              => 'local',
                'visibility'           => 'public',
                'directory_visibility' => 'public',
                'options'              => [
                    'directory'   => "{$container->getParameter('kernel.project_dir')}/var/storage/default",
                    'permissions' => [
                        'file' => [
                            'public' => 0664,
                            'private'=> 0600,
                        ],
                        'dir' => [
                            'public'  => 0775,
                            'private' => 0700,
                        ],
                    ],
                ],
            ],

            'public.storage' => [
                // old name `public`
                'adapter'              => 'local',
                'visibility'           => 'public',
                'directory_visibility' => 'public',
                'options'              => [
                    'url'         => "{$container->getParameter('app.base_uri')}/public/storage",
                    'directory'   => "{$container->getParameter('kernel.project_dir')}/var/app/public",
                    'permissions' => [
                        'file' => [
                            'public' => 0664,
                            'private'=> 0600,
                        ],
                        'dir' => [
                            'public'  => 0775,
                            'private' => 0700,
                        ],
                    ],
                ],
            ],

            'configs.storage' => [
                // old name `configs`
                'adapter'    => 'local',
                'visibility' => 'private',
                'options'    => [
                    'directory' => "{$container->getParameter('kernel.project_dir')}/tinymvc/myapp/configs",
                ],
            ],

            'root.storage' => [
                // old name `root`
                'adapter'    => 'local',
                'visibility' => 'private',
                'options'    => [
                    'directory' => $container->getParameter('kernel.project_dir'),
                ],
            ],

            'temp.storage' => [
                'adapter'              => 'local',
                'visibility'           => 'public',
                'directory_visibility' => 'public',
                'options'              => [
                    'url'         => "{$container->getParameter('app.base_uri')}/public/temp",
                    'directory'   => "{$container->getParameter('kernel.project_dir')}/var/temp",
                    'permissions' => [
                        'file' => [
                            'public' => 0664,
                            'private'=> 0600,
                        ],
                        'dir' => [
                            'public'  => 0775,
                            'private' => 0700,
                        ],
                    ],
                ],
            ],

            // Other legacy storages

            'cache.legacy.storage' => [
                // old name `cache`
                'adapter'    => 'local',
                'visibility' => 'private',
                'options'    => [
                    'directory' => "{$container->getParameter('kernel.project_dir')}/var/cache",
                ],
            ],

            'temp.legacy.storage' => [
                // old name `temp`
                'adapter'    => 'local',
                'visibility' => 'private',
                'options'    => [
                    'directory' => "{$container->getParameter('kernel.project_dir')}/temp",
                ],
            ],

            'log.legacy.storage' => [
                // old name `log`
                'adapter'    => 'local',
                'visibility' => 'private',
                'options'    => [
                    'directory' => "{$container->getParameter('kernel.project_dir')}/var/log",
                ],
            ],

            'key.legacy.storage' => [
                // old name `key-storage`
                'adapter'    => 'local',
                'visibility' => 'private',
                'options'    => [
                    'directory' => "{$container->getParameter('kernel.project_dir')}/var/storage/keys",
                ],
            ],

            'server.key.legacy.storage' => [
                // old name `app-key-storage`
                'adapter'    => 'local',
                'visibility' => 'private',
                'options'    => [
                    'directory' => "{$container->getParameter('kernel.project_dir')}/{$container->getParameter('kernel.env.APP_ENCRYPTION_KEY_PATH')}",
                ],
            ],

            'local.legacy.storage' => [
                // old name `local`
                'adapter'              => 'local',
                'visibility'           => 'public',
                'directory_visibility' => 'public',
                'options'              => [
                    'directory'   => "{$container->getParameter('kernel.project_dir')}/public",
                    'permissions' => [
                        'file' => [
                            'public' => 0664,
                            'private'=> 0600,
                        ],
                        'dir' => [
                            'public'  => 0775,
                            'private' => 0700,
                        ],
                    ],
                ],
            ],

            'users.legacy.storage' => [
                // old name `users`
                'adapter'              => 'local',
                'visibility'           => 'public',
                'directory_visibility' => 'public',
                'options'              => [
                    'directory'   => "{$container->getParameter('kernel.project_dir')}/public/img/users",
                    'permissions' => [
                        'file' => [
                            'public' => 0664,
                            'private'=> 0600,
                        ],
                        'dir' => [
                            'public'  => 0775,
                            'private' => 0700,
                        ],
                    ],
                ],
            ],

            // @deprecated code using this storage has been refactored, and it is not used anymore
            // 'companies.legacy.storage' => [
            //     // old name `companies`
            //     'adapter'              => 'local',
            //     'visibility'           => 'public',
            //     'directory_visibility' => 'public',
            //     'options'              => [
            //         'directory'   => "{$container->getParameter('kernel.project_dir')}/public/img/company",
            //         'permissions' => [
            //             'file' => [
            //                 'public' => 0774,
            //                 'private'=> 0700,
            //             ],
            //             'dir' => [
            //                 'public'  => 0775,
            //                 'private' => 0700,
            //             ],
            //         ],
            //     ],
            // ],

            'sitemap.legacy.storage' => [
                // old name `sitemap`
                'adapter'              => 'local',
                'visibility'           => 'public',
                'directory_visibility' => 'public',
                'options'              => [
                    'directory'   => "{$container->getParameter('kernel.project_dir')}/sitemap",
                    'permissions' => [
                        'file' => [
                            'public' => 0664,
                            'private'=> 0600,
                        ],
                        'dir' => [
                            'public'  => 0775,
                            'private' => 0700,
                        ],
                    ],
                ],
            ],

            'messages.legacy.storage' => [
                // old name `message-attachments`
                'adapter'              => 'local',
                'visibility'           => 'public',
                'directory_visibility' => 'public',
                'options'              => [
                    'directory'   => "{$container->getParameter('kernel.project_dir')}/public/messages",
                    'permissions' => [
                        'file' => [
                            'public' => 0664,
                            'private'=> 0600,
                        ],
                        'dir' => [
                            'public'  => 0775,
                            'private' => 0700,
                        ],
                    ],
                ],
            ],

            'order.documents.legacy.storage' => [
                // old name `order-documents`
                'adapter'    => 'local',
                'visibility' => 'private',
                'options'    => [
                    'directory' => "{$container->getParameter('kernel.project_dir')}/public/orders_documents",
                    'permissions' => [
                        'file' => [
                            'public' => 0664,
                            'private'=> 0600,
                        ],
                        'dir' => [
                            'public'  => 0775,
                            'private' => 0700,
                        ],
                    ],
                ],
            ],

            'product.reviews.legacy.storage' => [
                // old name `product-reviews`
                'adapter'              => 'local',
                'visibility'           => 'public',
                'directory_visibility' => 'public',
                'options'              => [
                    'directory' => "{$container->getParameter('kernel.project_dir')}/var/app/public/product_reviews",
                    'permissions' => [
                        'file' => [
                            'public' => 0664,
                            'private'=> 0600,
                        ],
                        'dir' => [
                            'public'  => 0775,
                            'private' => 0700,
                        ],
                    ],
                ],
            ],

            'dashboard.banners.legacy.storage' => [
                // old name `dashboard-banners`
                'adapter'              => 'local',
                'visibility'           => 'public',
                'directory_visibility' => 'public',
                'options'              => [
                    'directory' => "{$container->getParameter('kernel.project_dir')}/var/app/public/dashboard_banners",
                    'permissions' => [
                        'file' => [
                            'public' => 0664,
                            'private'=> 0600,
                        ],
                        'dir' => [
                            'public'  => 0775,
                            'private' => 0700,
                        ],
                    ],
                ],
            ],

            'shipping-methods.legacy.storage' => [
                // old name `shipping-methods`
                'adapter'              => 'local',
                'visibility'           => 'public',
                'directory_visibility' => 'public',
                'options'              => [
                    'directory' => "{$container->getParameter('kernel.project_dir')}/var/app/public/shipping_methods",
                    'permissions' => [
                        'file' => [
                            'public' => 0664,
                            'private'=> 0600,
                        ],
                        'dir' => [
                            'public'  => 0775,
                            'private' => 0700,
                        ],
                    ],
                ],
            ],
        ],
    ];
};
