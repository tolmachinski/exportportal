<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Notifier;

use InvalidArgumentException;
use UnhandledMatchError;

final class Type
{
    public const SENT_ENVELOPE = 'sent_envelope';

    public const SIGNED_ENVELOPE = 'signed_envelope';

    public const VIEWED_ENVELOPE = 'viewed_envelope';

    public const VOIDED_ENVELOPE = 'voided_envelope';

    public const UPDATED_ENVELOPE = 'updated_envelope';

    public const DECLINED_ENVELOPE = 'declined_envelope';

    public const APPROVED_ENVELOPE = 'approved_envelope';

    public const COMPLETED_ENVELOPE = 'completed_envelope';

    public const SENT_ORDER_ENVELOPE = 'sent_order_envelope';

    public const DECLINED_SIGNED_ENVELOPE = 'declined_signed_envelope';

    public const CONFIRMED_SIGNED_ENVELOPE = 'confirmed_signed_envelope';

    public const REQUIRED_ENVELOPE_APPROVAL = 'required_envelope_approval';

    public const CREATED_ENVELOPE_FOR_MANAGER = 'created_envelope_for_manager';

    public const COPY_ENVELOPE_FOR_MANAGER = 'copy_envelope_for_manager';

    public const SENT_ENVELOPE_FOR_MANAGER = 'sent_envelope_for_manager';

    public const SIGNED_ENVELOPE_FOR_MANAGER = 'signed_envelope_for_manager';

    public const VIEWED_ENVELOPE_FOR_MANAGER = 'viewed_envelope_for_manager';

    public const VOIDED_ENVELOPE_FOR_MANAGER = 'voided_envelope_for_manager';

    public const UPDATED_ENVELOPE_FOR_MANAGER = 'updated_envelope_for_manager';

    public const DECLINED_ENVELOPE_FOR_MANAGER = 'declined_envelope_for_manager';

    public const COMPLETED_ENVELOPE_FOR_MANAGER = 'completed_envelope_for_manager';

    public const SENT_ORDER_ENVELOPE_FOR_MANAGER = 'sent_order_envelope_for_manager';

    public const DECLINED_SIGNED_ENVELOPE_FOR_MANAGER = 'declined_signed_envelope_for_manager';

    public const CONFIRMED_SIGNED_ENVELOPE_FOR_MANAGER = 'confirmed_signed_envelope_for_manager';

    private const TYPES = [
        self::SENT_ENVELOPE,
        self::SIGNED_ENVELOPE,
        self::VIEWED_ENVELOPE,
        self::VOIDED_ENVELOPE,
        self::UPDATED_ENVELOPE,
        self::DECLINED_ENVELOPE,
        self::APPROVED_ENVELOPE,
        self::COMPLETED_ENVELOPE,
        self::SENT_ORDER_ENVELOPE,
        self::DECLINED_SIGNED_ENVELOPE,
        self::CONFIRMED_SIGNED_ENVELOPE,
        self::REQUIRED_ENVELOPE_APPROVAL,
        self::CREATED_ENVELOPE_FOR_MANAGER,
        self::COPY_ENVELOPE_FOR_MANAGER,
        self::SENT_ENVELOPE_FOR_MANAGER,
        self::SIGNED_ENVELOPE_FOR_MANAGER,
        self::VIEWED_ENVELOPE_FOR_MANAGER,
        self::VOIDED_ENVELOPE_FOR_MANAGER,
        self::UPDATED_ENVELOPE_FOR_MANAGER,
        self::DECLINED_ENVELOPE_FOR_MANAGER,
        self::COMPLETED_ENVELOPE_FOR_MANAGER,
        self::SENT_ORDER_ENVELOPE_FOR_MANAGER,
        self::DECLINED_SIGNED_ENVELOPE_FOR_MANAGER,
        self::CONFIRMED_SIGNED_ENVELOPE_FOR_MANAGER,
    ];

    /**
     * The type name.
     *
     * @internal
     */
    private string $name;

    public function __construct(string $name)
    {
        if (!in_array($name, self::TYPES)) {
            throw new InvalidArgumentException('Invalid type provided.');
        }

        $this->name = $name;
    }

    /**
     * Returns browser code for notfication type.
     *
     * @throws UnhandledMatchError if match code provided for type is not defined
     */
    public static function browserCode(self $type): string
    {
        switch ($type->getName()) {
            case static::SENT_ENVELOPE: return 'order_documents_sender_sent_document_envelope';

            case static::SIGNED_ENVELOPE: return 'order_documents_recipient_signed_document_envelope';

            case static::VIEWED_ENVELOPE: return 'order_documents_recipient_viewed_document_envelope';

            case static::VOIDED_ENVELOPE: return 'order_documents_sender_voided_document_envelope';

            case static::UPDATED_ENVELOPE: return 'order_documents_sender_updated_document_envelope';

            case static::DECLINED_ENVELOPE: return 'order_documents_recipient_declined_document_envelope';

            case static::APPROVED_ENVELOPE: return 'order_documents_operator_approved_document_envelope';

            case static::COMPLETED_ENVELOPE: return 'order_documents_operator_completed_document_envelope';

            case static::SENT_ORDER_ENVELOPE: return 'order_documents_sender_sent_internal_document_envelope';

            case static::CONFIRMED_SIGNED_ENVELOPE: return 'order_documents_sender_confirmed_signed_document_envelope';

            case static::DECLINED_SIGNED_ENVELOPE: return 'order_documents_sender_declined_signed_document_envelope';

            case static::REQUIRED_ENVELOPE_APPROVAL: return 'order_documents_sender_require_approval_document_envelope';

            case static::CREATED_ENVELOPE_FOR_MANAGER: return 'order_documents_sender_create_document_envelope_for_manager';

            case static::COPY_ENVELOPE_FOR_MANAGER: return 'order_documents_sender_copy_document_envelope_for_manager';

            case static::SENT_ENVELOPE_FOR_MANAGER: return 'order_documents_sender_sent_document_envelope_for_manager';

            case static::SIGNED_ENVELOPE_FOR_MANAGER: return 'order_documents_recipient_signed_document_envelope_for_manager';

            case static::VIEWED_ENVELOPE_FOR_MANAGER: return 'order_documents_recipient_viewed_document_envelope_for_manager';

            case static::VOIDED_ENVELOPE_FOR_MANAGER: return 'order_documents_sender_voided_document_envelope_for_manager';

            case static::UPDATED_ENVELOPE_FOR_MANAGER: return 'order_documents_sender_updated_document_envelop_for_manager';

            case static::DECLINED_ENVELOPE_FOR_MANAGER: return 'order_documents_recipient_declined_document_envelope_for_manager';

            case static::COMPLETED_ENVELOPE_FOR_MANAGER: return 'order_documents_operator_completed_document_envelope_for_manager';

            case static::SENT_ORDER_ENVELOPE_FOR_MANAGER: return 'order_documents_sender_sent_internal_document_envelope_for_manager';

            case static::DECLINED_SIGNED_ENVELOPE_FOR_MANAGER: return 'order_documents_sender_declined_signed_document_envelope_for_manager';

            case static::CONFIRMED_SIGNED_ENVELOPE_FOR_MANAGER: return 'order_documents_sender_confirmed_signed_document_envelope_for_manager';

            default:
                throw new UnhandledMatchError('The provided type is not supported.');
        }
    }

    /**
     * Get the type name.
     */
    private function getName(): string
    {
        return $this->name;
    }
}
