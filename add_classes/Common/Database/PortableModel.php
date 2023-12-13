<?php

declare(strict_types=1);

namespace App\Common\Database;

use TinyMVC_PDO as ConnectionHandler;

/**
 * Provides the protable way of wotking with models.
 */
final class PortableModel extends Model
{
    /**
     * {@inheritdoc}
     *
     * @param array|string $primaryKey
     */
    public function __construct(
        ConnectionHandler $connectionHandler,
        string $table,
        $primaryKey = null,
        array $casts = [],
        array $guarded = [],
        array $nullable = []
    ) {
        parent::__construct($connectionHandler);

        $this->table = $table;
        $this->casts = $casts;
        $this->guarded = $guarded;
        $this->nullable = $nullable;

        if (null !== $primaryKey) {
            $this->primaryKey = $primaryKey;
        }
    }
}
