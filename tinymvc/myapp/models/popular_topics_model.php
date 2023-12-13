<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Types\Types;

/**
 * Ep Topics Model.
 */
final class Popular_Topics_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'popular_topics';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'TOPICS';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_topic';

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = false;

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_topic'          => Types::INTEGER,
        'text_topic'        => Types::TEXT,
        'visible_topic'     => Types::INTEGER,
        'date_topic'        => Types::DATETIME_MUTABLE,
        'translations_data' => Types::JSON
    ];

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id_topic',
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'translations_data'
    ];
}
