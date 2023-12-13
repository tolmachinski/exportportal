<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\Types\Types;

/**
 * User_Rights_Pivot model.
 */
final class User_Rights_Pivot_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'user_rights_aditional';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'USER_RIGHTS_PIVOT';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'user_right_rel_id';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'user_right_rel_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'user_right_rel_id' => Types::INTEGER,
        'id_user'           => Types::INTEGER,
        'id_right'          => Types::INTEGER,
        'right_paid_until'  => Types::DATE_IMMUTABLE,
        'right_paid'        => Types::BOOLEAN,
    ];

    /**
     * Relation with user.
     */
    protected function user(): RelationInterface
    {
        return $this->belongsTo(Users_Model::class, 'id_user');
    }

    /**
     * Relation with right.
     */
    protected function right(): RelationInterface
    {
        return $this->belongsTo(Rights_Model::class, 'id_right');
    }
}

// End of file user_rights_pivot_model.php
// Location: /tinymvc/myapp/models/user_rights_pivot_model.php
