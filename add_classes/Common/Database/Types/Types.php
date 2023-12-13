<?php

declare(strict_types=1);

namespace App\Common\Database\Types;

/**
 * Default built-in types for MySQL.
 */
final class Types
{
    public const UUID = 'uuid';
    public const MONEY = 'money';
    public const UUID_BINARY = 'uuid_bynary';
    public const PHONE_NUMBER = 'phone_number';
    public const SIMPLE_MONEY = 'simple_money';
    public const JSON_LEGACY = 'json_legacy'; // Legacy stuff
    public const DATE_LEGACY = 'date_legacy'; // Legacy stuff
    public const TIME_LEGACY = 'time_legacy'; // Legacy stuff
    public const SIMPLE_JSON_ARRAY = 'simple_json_array'; // Legacy stuff
    public const DATETIME_LEGACY = 'datetime_legacy'; // Legacy stuff

    /**
     * @internal Prevents direct instantaniation
     */
    private function __construct()
    {
        // None shall pass!
    }
}
