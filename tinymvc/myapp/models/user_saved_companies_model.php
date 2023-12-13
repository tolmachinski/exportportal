<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\Types\Types;

/**
 * User saved companies model.
 */
final class User_Saved_Companies_Model extends Model
{
    /**
     * The name of the "created at" column.
     */
    protected const CREATED_AT = 'date_save';

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'user_saved_companies';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'USER_SAVED_COMPANIES';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'         => Types::INTEGER,
        'user_id'    => Types::INTEGER,
        'company_id' => Types::INTEGER,
        'date_save'  => Types::DATETIME_IMMUTABLE,
    ];

    /**
     * Relation with the user.
     */
    protected function user(): RelationInterface
    {
        return $this->belongsTo(Users_Model::class, 'user_id')->enableNativeCast();
    }

    /**
     * Relation with the company.
     */
    protected function company(): RelationInterface
    {
        return $this->belongsTo(Seller_Companies_Model::class, 'company_id')->enableNativeCast();
    }
}

// End of file User_Saved_Companies_Model.php
// Location: /tinymvc/myapp/models/User_Saved_Companies_Model.php
