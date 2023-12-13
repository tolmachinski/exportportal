<?php

declare(strict_types=1);

namespace App\Common\Database\Legacy;

use Doctrine\DBAL\Driver\Connection;
use InvalidArgumentException;
use PDO;

/**
 * @deprecated
 */
interface ConnectionFactoryInterface
{
    /**
     * Creates the databse connection.
     *
     * @throws InvalidArgumentException if configurations are empty
     *
     * @return Connection|PDO
     */
    public function createConnection(array $configs);
}
