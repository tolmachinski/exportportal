<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Blogs_Categories_I18n model
 */
final class Blogs_Categories_I18n_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "blogs_category_i18n";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "BLOGS_CATEGORY_I18N";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id_category_i18n";

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id_category_i18n',
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'h1',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_category_i18n'  => Types::INTEGER,
        'id_category'       => Types::INTEGER,
    ];

    /**
     * Scope by blog category ID
     *
     * @param QueryBuilder $builder
     * @param int $categoryId
     *
     * @return void
     */
    protected function scopeCategoryId(QueryBuilder $builder, int $categoryId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->table}.`id_category`",
                $builder->createNamedParameter($categoryId, ParameterType::INTEGER, $this->nameScopeParameter('categoryId'))
            )
        );
    }

    /**
     * Scope by blog category IDs
     *
     * @param QueryBuilder $builder
     * @param array $categoryIds
     *
     * @return void
     */
    protected function scopeCategoryIds(QueryBuilder $builder, array $categoryIds): void
    {
        if (empty($categoryIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                "{$this->table}.`id_category`",
                array_map(
                    fn ($i, $categoryId) => $builder->createNamedParameter((int) $categoryId, ParameterType::INTEGER, $this->nameScopeParameter("categoryId{$i}")),
                    array_keys($categoryIds),
                    $categoryIds
                )
            )
        );
    }

    /**
     * Scope by blog category language
     *
     * @param QueryBuilder $builder
     * @param string $language
     *
     * @return void
     */
    protected function scopeLanguage(QueryBuilder $builder, string $language): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->table}.`lang_category`",
                $builder->createNamedParameter($language, ParameterType::STRING, $this->nameScopeParameter('categoryLanguage'))
            )
        );
    }
}

/* End of file blogs_categories_i18n_model.php */
/* Location: /tinymvc/myapp/models/blogs_categories_i18n_model.php */
