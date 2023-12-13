<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Database\Types\Types as CustomTypes;
use Doctrine\DBAL\Types\Types;

/**
 * Envelope_Attachments model.
 */
final class Envelope_Attachments_Model extends Model
{
    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    protected const CREATED_AT = 'uploaded_at_date';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    protected const UPDATED_AT = 'updated_at_date';

    /**
     * The table primary key.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;

    /**
     * The table name.
     */
    protected string $table = 'envelope_attachments';

    /**
     * The table alias.
     */
    protected string $alias = 'ENVELOPE_ATTACHMENTS';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id',
        self::CREATED_AT,
        self::UPDATED_AT,
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'label',
        'file_name',
        'file_extension',
        'file_original_name',
        'local_path',
        'mime_type',
        'remote_uuid',
        'deleted_at_date',
        self::CREATED_AT,
        self::UPDATED_AT,
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'               => Types::INTEGER,
        'id_envelope'      => Types::INTEGER,
        'uuid'             => CustomTypes::UUID,
        'file_size'        => Types::INTEGER,
        'uploaded_at_date' => Types::DATETIME_IMMUTABLE,
        'updated_at_date'  => Types::DATETIME_IMMUTABLE,
        'deleted_at_date'  => Types::DATETIME_IMMUTABLE,
    ];

    /**
     * Relation with the envelope.
     */
    protected function envelope(): RelationInterface
    {
        return $this->belongsTo(Envelopes_Model::class, 'id_envelope')->disableNativeCast();
    }
}

// End of file envelope_attachments_model.php
// Location: /tinymvc/myapp/models/envelope_attachments_model.php
