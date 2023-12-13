<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Types\Types;

/**
 * Auth Context Model
 */
final class User_Photos_Model extends Model
{
    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = false;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'user_photo';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'USER_PHOTOS';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_photo';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id_photo',
        'id_user'
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_user'           => Types::INTEGER,
        'actulized_photo'   => Types::BOOLEAN,
        'id_photo'          => Types::INTEGER,
        'thumb_photo'       => Types::ARRAY,
    ];
}