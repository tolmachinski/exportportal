<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Category_Translated_Article model.
 */
final class Category_Translated_Article_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'item_category_articles_i18n';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'ITEM_CATEGORY_ARTICLES_I18N';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_article_i18n';

    /**
     * Scope a query to filter by article ID.
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
     * Scope a query to filter by main article ID.
     */
    protected function scopeIdMainArticle(QueryBuilder $builder, int $articleMainId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_article'),
                $builder->createNamedParameter($articleMainId, ParameterType::INTEGER, $this->nameScopeParameter('articleMainId'))
            )
        );
    }

    /**
     * Scope a query to filter by main article ID.
     */
    protected function scopeLanguage(QueryBuilder $builder, string $lang)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('lang_article'),
                $builder->createNamedParameter($lang, ParameterType::STRING, $this->nameScopeParameter('lang'))
            )
        );
    }
}

// End of file category_translated_article_model.php
// Location: /tinymvc/myapp/models/category_translated_article_model.php
