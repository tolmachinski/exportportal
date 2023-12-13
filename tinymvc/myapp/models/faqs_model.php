<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\Types\Types;

/**
 * Ep Faq Model
 */
final class Faqs_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'faq';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'FAQ';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_faq';

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = false;

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_faq'            => Types::INTEGER,
        'question'          => Types::STRING,
        'answer'            => Types::TEXT,
        'weight'            => Types::INTEGER,
        'translations_data' => Types::JSON,
        'inline_images'     => Types::JSON
    ];

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id_faq'
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'translations_data',
        'inline_images'
    ];

    /**
     * Get tags relation
     */
    protected function tags(): RelationInterface
    {
        return $this->hasManyThrough(
            Tags_Faq_Model::class,
            Tags_Faq_Relation_Model::class,
            'id_faq',
            'id_tag',
            $this->getPrimaryKey(),
            'id_tag'
        );
    }
}