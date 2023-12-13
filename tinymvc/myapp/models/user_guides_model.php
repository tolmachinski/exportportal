<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Types\Types;

/**
 * User Guides Model.
 */
final class User_Guides_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'user_guides';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'UG';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_menu';

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = false;

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_menu'               => Types::INTEGER,
        'id_parent'             => Types::INTEGER,
        'menu_description'      => Types::TEXT,
        'menu_video_buyer'      => Types::TEXT,
        'menu_video_shipper'    => Types::TEXT,
        'menu_breadcrumbs'      => Types::JSON,
        'menu_children'         => Types::SIMPLE_ARRAY,
        'menu_position'         => Types::INTEGER,
        'menu_actualized'       => Types::INTEGER
    ];

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id_menu',
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [];
}
