<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Blogs_Categories model
 */
final class Blogs_Categories_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "blogs_category";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "BLOGS_CATEGORY";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id_category";

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id_category',
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'special_link',
        'h1',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_category'       => Types::INTEGER,
        'translations_data' => Types::JSON,
    ];

    /**
     * Scope by blog category special link
     *
     * @param QueryBuilder $builder
     * @param string $specialLink
     *
     * @return void
     */
    protected function scopeSpecialLink(QueryBuilder $builder, string $specialLink): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->table}.`special_link`",
                $builder->createNamedParameter($specialLink, ParameterType::STRING, $this->nameScopeParameter('specialLink'))
            )
        );
    }
}

/* End of file blogs_categories_model.php */
/* Location: /tinymvc/myapp/models/blogs_categories_model.php */
