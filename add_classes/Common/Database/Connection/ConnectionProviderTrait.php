<?php

declare(strict_types=1);

namespace App\Common\Database\Connection;

use Doctrine\DBAL\Connection;

trait ConnectionProviderTrait
{
    /**
     * The database connection handler.
     *
     * @var Connection
     */
    private $connection;

    /**
     * Returns the database connection.
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }
}
