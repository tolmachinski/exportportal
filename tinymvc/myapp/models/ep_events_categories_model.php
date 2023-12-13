<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\ParameterType;

/**
 * Ep_Events_Categories model
 */
final class Ep_Events_Categories_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "ep_events_categories";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "EP_EVENTS_CATEGORIES";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * Scope category by categories ids
     *
     * @var array $categoriesIds
     *
     * @return void
     */
    protected function scopeIds(QueryBuilder $builder, array $categoriesIds): void
    {
        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->getTable()}`.`{$this->getPrimaryKey()}`",
                array_map(
                    fn ($index, $categoryId) => $builder->createNamedParameter(
                        (int) $categoryId,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("categoryId{$index}")
                    ),
                    array_keys($categoriesIds),
                    $categoriesIds
                )
            )
        );
    }

    /**
     * Scope category by category url or special link
     *
     * @var string $categorySlug
     *
     * @return void
     */
    protected function scopeUrlOrSpecialLink(QueryBuilder $builder, string $categorySlug): void
    {
        $builder->andWhere(
            $builder->expr()->or(
                $builder->expr()->eq(
                    "`{$this->getTable()}`.`url`",
                    $builder->createNamedParameter($categorySlug, ParameterType::STRING, $this->nameScopeParameter('categoryUrl'))
                ),
                $builder->expr()->eq(
                    "`{$this->getTable()}`.`special_link`",
                    $builder->createNamedParameter($categorySlug, ParameterType::STRING, $this->nameScopeParameter('categorySpecialLink'))
                ),
            )
        );
    }
}

/* End of file ep_events_categories_model.php */
/* Location: /tinymvc/myapp/models/ep_events_categories_model.php */
