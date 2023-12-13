<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Types\Types;

/**
 * Auth Context Model
 */
final class Campaign_Model extends Model
{
    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = false;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'campaigns';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'CAMPAINGS';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_campaign';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id_campaign',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_campaign'           => Types::INTEGER,
        'campaign_active'       => Types::INTEGER,
    ];
}