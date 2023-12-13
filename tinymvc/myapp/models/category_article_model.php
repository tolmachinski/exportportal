<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Category_Article model.
 */
final class Category_Article_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'item_category_articles';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'ITEM_CATEGORY_ARTICLES';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id';

    /**
     * Scope a query to filter by category ID.
     */
    protected function scopeId(QueryBuilder $builder, int $categoryId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn($this->getPrimaryKey()),
                $builder->createNamedParameter($categoryId, ParameterType::INTEGER, $this->nameScopeParameter('categoryId'))
            )
        );
    }

    /**
     * Scope a query by no empty photo.
     */
    protected function scopePhotoNotEmpty(QueryBuilder $builder)
    {
        $builder->andWhere(
            "`photo` <> ''"
        );
    }
}

// End of file category_article_model.php
// Location: /tinymvc/myapp/models/category_article_model.php
