<?php

declare(strict_types=1);

use App\Common\Assets\VersionStrategy\MtimeVersionStrategy;

return array(
    'json_manifest_path' => 'public/build/manifest.json',
    'packages'           => array(
        'legacy' => array(
            'version_strategy' => function () { return new MtimeVersionStrategy(dirname(dirname(dirname(__DIR__)))); },
            'base_urls'        => array(
                __FILES_URL
            )
        ),
    ),
);
