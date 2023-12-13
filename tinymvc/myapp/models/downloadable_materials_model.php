<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use App\Common\Database\Relations\RelationInterface;

/**
 * Downloadable_Materials model
 */
final class Downloadable_Materials_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "downloadable_materials";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "dwm";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * Scope a query to filter records by creation date.
     *
     * @param mixed $creation_date
     */
    protected function scopeCreatedFrom(QueryBuilder $builder, ?string $creation_date): void
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($creation_date, 'Y-m-d H:i:s'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "DATE({$this->getTable()}.created)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('createdFrom'))
            )
        );
    }

    /**
     * Scope a query to filter records by creation date.
     *
     * @param mixed $creation_date
     */
    protected function scopeCreatedTo(QueryBuilder $builder, ?string $creation_date): void
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($creation_date, 'Y-m-d H:i:s'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "DATE({$this->getTable()}.created)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('createdTo'))
            )
        );
    }

    /**
     * Scope a query to filter records by title.
     *
     * @param string $title
     */
    protected function scopeTitle(QueryBuilder $builder, ?string $title): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'title',
                $builder->createNamedParameter($title, ParameterType::STRING, $this->nameScopeParameter('title'))
            )
        );
    }

    /**
     * Scope a query to filter records by slug.
     *
     * @param string $slug
     */
    protected function scopeSlug(QueryBuilder $builder, ?string $slug): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'slug',
                $builder->createNamedParameter($slug, ParameterType::STRING, $this->nameScopeParameter('slug'))
            )
        );
    }

    /**
     * Download count sum subquery
     */
    protected function scopeDownloadCounts(QueryBuilder $builder): void
    {
        $statisticsRelation = $this->statistics();
        $statisticsRepository = $statisticsRelation->getRelated();
        $subquery = $this->createQueryBuilder();
        $subquery
            ->select('`id_material`', 'SUM(`count`) AS `counts`')
            ->from($statisticsRepository->getTable())
            ->groupBy('id_material')
        ;

        $builder->leftJoin(
            $this->getTable(),
            "({$subquery->getSQL()})",
            'downloads',
            "`downloads`.`id_material` = {$this->qualifyColumn('id')}"
        );
    }

    /**
     * Scope a query to filter records by slug.
     *
     * @param string $slug
     */
    protected function scopeRecommended(QueryBuilder $builder, ?string $slug): void
    {
        $builder->andWhere(
            $builder->expr()->neq(
                'slug',
                $builder->createNamedParameter($slug, ParameterType::STRING, $this->nameScopeParameter('slug'))
            )
        );
    }

    /**
     * Resolves static relationships with Users_Downloadable_Materials_Model
     */
    protected function statistics(): RelationInterface
    {
        return $this->hasMany(Users_Downloadable_Materials_Model::class, 'id_material')->disableNativeCast();
    }
}

/* End of file downloadable_materials_model.php */
/* Location: /tinymvc/myapp/models/downloadable_materials_model.php */
