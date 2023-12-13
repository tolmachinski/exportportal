<?php

declare(strict_types=1);

namespace App\Common\Database\Connection;

/**
 * @deprecated
 *
 * This class is just a decoration over \App\Common\Database\Connection\Registry class.
 * It is used to properly DI the TinyMVC_PDO in the conainer.
 */
final class PdoRegistry extends Registry
{
    // HIC SVNT DRACONES
}
