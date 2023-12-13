<?php

declare(strict_types=1);

use App\Messenger\Message;
use Symfony\Component\DependencyInjection\ContainerInterface;

return function (ContainerInterface $container) {
    return [
        // Set transports
        'transports'             => [
            // Create memory transport that is used for debuf purposes
            'memory'  => 'in-memory://',
        ],
        'routing'                => [
            // HC SVNT DRACONES
        ],
    ];
};
