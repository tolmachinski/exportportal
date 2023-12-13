<?php

declare(strict_types=1);

use App\Casts\Popup\PopupCallTypeCast;
use App\Casts\Popup\PopupModeCast;
use App\Casts\Popup\PopupTargetCast;
use App\Casts\Popup\PopupTypeCast;
use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Model Popups.
 */
class Popup_Model extends Model
{
    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = false;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'popups';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'POPUPS';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_popup';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id',
        'popup_hash',
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'popup_hash',
        'data_mapping',
        'view_method',
        'repeat_on_cancel',
        'repeat_on_submit',
        'description',
        'priority',
        'snooze_time',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_popup'                     => Types::INTEGER,
        'type'                         => PopupTypeCast::class,
        'type_popup'                   => PopupModeCast::class,
        'data_mapping'                 => Types::JSON,
        'repeat_on_cancel'             => Types::INTEGER,
        'repeat_on_submit'             => Types::INTEGER,
        'is_active'                    => Types::BOOLEAN,
        'call_on_start'                => PopupCallTypeCast::class,
        'for_who'                      => PopupTargetCast::class,
        'priority'                     => Types::INTEGER,
        'snooze_time'                  => Types::INTEGER,
        'require_user_popups_relation' => Types::BOOLEAN,
    ];

    /**
     * Scope a query to filter by hash.
     */
    protected function scopePopupHash(QueryBuilder $builder, string $hash): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.popup_hash",
                $builder->createNamedParameter($hash, ParameterType::STRING, $this->nameScopeParameter('popup_hash', true))
            )
        );
    }

    /**
     * Scope a query to filter by is active.
     */
    protected function scopeIsActive(QueryBuilder $builder, int $isActive): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.is_active",
                $builder->createNamedParameter((int) $isActive, ParameterType::INTEGER, $this->nameScopeParameter('is_active', true))
            )
        );
    }
}

// End of file popups_model.php
// Location: /tinymvc/myapp/models/popups_model.php
