<?php

declare(strict_types=1);

namespace App\Common\Database\Concerns;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use TinyMVC_PDO;

/**
 * Allows for the model to DB connections.
 */
trait HasConnection
{
    /**
     * Returns the database handler.
     */
    public function getHandler(): TinyMVC_PDO
    {
        return $this->db;
    }

    /**
     * Returns the DB connection instance.
     */
    public function getConnection(): Connection
    {
        return $this->db->getConnection();
    }

    /**
     * Returns the DB platform.
     */
    public function getPlatform(): AbstractPlatform
    {
        return $this->getConnection()->getDatabasePlatform();
    }
}
