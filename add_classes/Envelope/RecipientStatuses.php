<?php

declare(strict_types=1);

namespace App\Envelope;

final class RecipientStatuses
{
    public const SENT = 'sent';
    public const SIGNED = 'signed';
    public const CREATED = 'created';
    public const DECLINED = 'declined';
    public const COMPLETED = 'completed';
    public const DELIVERED = 'delivered';
    public const FINALIZED = [
        self::COMPLETED,
        self::DECLINED,
    ];

    /**
     * @internal Prevents direct instantaniation
     */
    private function __construct()
    {
        // None shall pass!
    }

    /**
     * Checks if provided status belongs to the current set of statuses.
     */
    public static function isValid(string $status): bool
    {
        return \in_array(
            $status,
            [
                static::SENT,
                static::SIGNED,
                static::CREATED,
                static::DECLINED,
                static::COMPLETED,
                static::DELIVERED,
            ]
        );
    }
}
