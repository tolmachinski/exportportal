<?php

declare(strict_types=1);

namespace App\Common\Database\Connection;

use Doctrine\DBAL\Connection;

interface ConnectionProviderInterface
{
    /**
     * Returns the database connection.
     */
    public function getConnection(): Connection;
}
