<?php

declare(strict_types=1);

namespace App\Envelope;

final class EnvelopeStatuses
{
    public const SENT = 'sent';
    public const NOT_PROCESSED = 'not_processed';
    public const PROCESSED = 'processed';
    public const CREATED = 'created';
    public const DELIVERED = 'delivered';
    public const COMPLETED = 'completed';
    public const DECLINED = 'declined';
    public const SIGNED = 'signed';
    public const VOIDED = 'voided';

    public const PENDING = [
        self::NOT_PROCESSED,
        self::PROCESSED,
        self::CREATED,
    ];

    public const FINISHED = [
        self::COMPLETED,
        self::DECLINED,
        self::VOIDED,
    ];

    /**
     * @internal Prevents direct instantaniation
     */
    private function __construct()
    {
        // None shall pass!
    }
}
