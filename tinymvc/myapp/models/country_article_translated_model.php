<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Country_Article_Translated model
 */
final class Country_Article_Translated_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "country_articles_i18n";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "COUNTRY_ARTICLES_I18N";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id_article_i18n";

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

/* End of file country_article_translated_model.php */
/* Location: /tinymvc/myapp/models/country_article_translated_model.php */
