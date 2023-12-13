<?php

declare(strict_types=1);

namespace App\Envelope;

use InvalidArgumentException;
use UnhandledMatchError;

final class HistoryEvent
{
    public const SYNC = 'sync';

    public const COPY = 'copy';

    public const VOID = 'void';

    public const CREATE = 'create';

    public const UPDATE = 'update';

    public const SEND = 'send';

    public const SIGN = 'sign';

    public const VIEW = 'view';

    public const DELIVER = 'deliver';

    public const PROCESS = 'process';

    public const CONFIRM = 'confirm';

    public const DECLINE = 'decline';

    public const AUTORESPONDED = 'autoresponded';

    public const EDIT_DESCRIPTION = 'edit_description';

    public const DECLINE_SIGNATURE = 'decline_signature';

    public const REQUIRE_PROCESSING = 'require_processing';

    public const CREATE_CONTRACT = 'create_contract';

    public const CREATE_INVOICE = 'create_invoice';

    private const EVENTS = [
        self::SYNC,
        self::COPY,
        self::VOID,
        self::CREATE,
        self::UPDATE,
        self::SEND,
        self::SIGN,
        self::VIEW,
        self::DELIVER,
        self::PROCESS,
        self::CONFIRM,
        self::DECLINE,
        self::AUTORESPONDED,
        self::EDIT_DESCRIPTION,
        self::DECLINE_SIGNATURE,
        self::REQUIRE_PROCESSING,
        self::CREATE_CONTRACT,
        self::CREATE_INVOICE,
    ];

    /**
     * The history event name.
     *
     * @internal
     */
    private string $name;

    public function __construct(string $name)
    {
        if (!in_array($name, self::EVENTS)) {
            throw new InvalidArgumentException('Invalid event provided.');
        }

        $this->name = $name;
    }

    /**
     * Returns the storage code for history event type.
     *
     * @throws UnhandledMatchError if match code provided for event is not defined
     */
    public static function storageCode(self $type): string
    {
        switch ($type->getName()) {
            case static::SYNC: return 'event_sync';

            case static::COPY: return 'event_copy';

            case static::VOID: return 'event_void';

            case static::CREATE: return 'event_create';

            case static::UPDATE: return 'event_update';

            case static::SEND: return 'event_send';

            case static::SIGN: return 'event_sign';

            case static::VIEW: return 'event_view';

            case static::DELIVER: return 'event_deliver';

            case static::PROCESS: return 'event_process';

            case static::CONFIRM: return 'event_confirm';

            case static::DECLINE: return 'event_decline';

            case static::AUTORESPONDED: return 'event_autoresponded';

            case static::EDIT_DESCRIPTION: return 'event_edit_description';

            case static::DECLINE_SIGNATURE: return 'event_decline_signature';

            case static::REQUIRE_PROCESSING: return 'event_require_processing';

            case static::CREATE_CONTRACT: return 'event_create_contract';

            case static::CREATE_INVOICE: return 'event_create_invoice';

            default:
                throw new UnhandledMatchError('The provided type is not supported.');
        }
    }

    /**
     * Returns the history event from storage code.
     */
    public static function fromStorageCode(string $code): self
    {
        if (\str_starts_with($code, 'event_')) {
            $code = \mb_substr($code, 6);
        }

        return new static($code);
    }

    /**
     * Get the type name.
     */
    private function getName(): string
    {
        return $this->name;
    }
}
