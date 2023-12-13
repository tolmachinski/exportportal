<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Database\Types\Types as CustomTypes;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;
use Money\Money;

/**
 * Upgrade_Packages model.
 */
final class Upgrade_Packages_Model extends Model
{
    /**
     * The name of the "created at" column.
     */
    protected const CREATED_AT = null;

    /**
     * The name of the "updated at" column.
     */
    protected const UPDATED_AT = 'updated_at';

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'ugroup_packages';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'UPGRADE_PACKAGES';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'idpack';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'idpack',
        self::UPDATED_AT,
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'updated_at',
        'en_updated_at',
        'translations_data',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'idpack'             => Types::INTEGER,
        'gr_from'            => Types::INTEGER,
        'gr_to'              => Types::INTEGER,
        'downgrade_gr_to'    => Types::INTEGER,
        'period'             => Types::INTEGER,
        'price'              => CustomTypes::SIMPLE_MONEY,
        'price_saved'        => Types::INTEGER,
        'def'                => Types::BOOLEAN,
        'profile_completion' => Types::JSON,
        'is_disabled'        => Types::BOOLEAN,
        'is_active'          => Types::BOOLEAN,
        'package_active'     => Types::BOOLEAN,
        'updated_at'         => Types::DATETIME_IMMUTABLE,
        'en_updated_at'      => Types::DATETIME_IMMUTABLE,
        'translations_data'  => Types::JSON,
    ];

    /**
     * Scope a query to filter by request by package price.
     */
    protected function scopePrice(QueryBuilder $builder, Money $price)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('price'),
                $builder->createNamedParameter(
                    $this->castAttributeToDatabaseValue('price', $price),
                    ParameterType::STRING,
                    $this->nameScopeParameter('price')
                )
            )
        );
    }

    /**
     * Scope a query to filter by request by package period.
     */
    protected function scopePeriod(QueryBuilder $builder, int $period)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('period'),
                $builder->createNamedParameter((int) $period, ParameterType::INTEGER, $this->nameScopeParameter('period'))
            )
        );
    }

    /**
     * Relation with original group.
     */
    protected function originalGroup(): RelationInterface
    {
        return $this->belongsTo(User_Groups_Model::class, 'gr_from');
    }

    /**
     * Relation with target group.
     */
    protected function targetGroup(): RelationInterface
    {
        return $this->belongsTo(User_Groups_Model::class, 'gr_to');
    }

    /**
     * Relation with downgrade group.
     */
    protected function downgradeGroup(): RelationInterface
    {
        return $this->belongsTo(User_Groups_Model::class, 'downgrade_gr_to');
    }
}

// End of file upgrade_packages_model.php
// Location: /tinymvc/myapp/models/upgrade_packages_model.php
