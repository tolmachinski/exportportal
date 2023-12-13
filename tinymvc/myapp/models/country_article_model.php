<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Country_Article model
 */
final class Country_Article_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "country_articles";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "COUNTRY_ARTICLES";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

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
     * Scope a query to filter by country ID.
     */
    protected function scopeCountryId(QueryBuilder $builder, int $countryId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_country'),
                $builder->createNamedParameter($countryId, ParameterType::INTEGER, $this->nameScopeParameter('countryId'))
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

    /**
     * Relation with phone code.
     */
    protected function country(): RelationInterface
    {
        return $this->hasOne(Countries_Model::class, 'id', 'id_country')->enableNativeCast();
    }
}

/* End of file country_article_model.php */
/* Location: /tinymvc/myapp/models/country_article_model.php */
