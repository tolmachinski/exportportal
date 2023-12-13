<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\Types\Types;

/**
 * Seller_Company_Types model.
 */
final class Seller_Company_Types_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'company_type';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'SELLER_COMPANY_TYPES';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_type';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id_type',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_type'             => Types::INTEGER,
        'allowed_user_groups' => Types::JSON,
    ];

    /**
     * Relation with the companies.
     */
    protected function companies(): RelationInterface
    {
        return $this->hasMany(Seller_Companies_Model::class, 'id_type')->enableNativeCast();
    }
}

// End of file seller_company_types_model.php
// Location: /tinymvc/myapp/models/seller_company_types_model.php
