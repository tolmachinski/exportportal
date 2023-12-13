<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Types\Types;
/**
 * Community_Questions_Categories model.
 *
 * @author Vlad A
 */
final class Community_Questions_Categories_Model extends Model
{

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = false;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'questions_categories';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'QUESTIONS_CATEGORIES';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'idcat';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'idcat'
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'icon'
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'idcat'             => Types::INTEGER,
        'translations_data' => Types::JSON,
        'visible_cat'       => Types::BOOLEAN,
        'on_main_page'      => Types::BOOLEAN,
        'order_number'      => Types::BOOLEAN
    ];
}