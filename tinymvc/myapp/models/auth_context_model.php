<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Types\Types;
use App\Common\Database\Relations\RelationInterface;

/**
 * Auth Context Model
 */
final class Auth_Context_Model extends Model
{
    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = false;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'auth_context_form';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'AUTH_CONTEXT_MODEL';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_hash';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id_hash',
        'id_principal'
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'reset_password_date',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_hash'               => Types::INTEGER,
        'id_principal'          => Types::INTEGER,
        'is_legacy'             => Types::BOOLEAN,
        'reset_password_date'   => Types::DATETIME_IMMUTABLE,
    ];

    /**
     * Relation with user.
     */
    protected function user(): RelationInterface
    {
        return $this->belongsTo(Users_Model::class, 'id_principal', 'id_principal');
    }
}