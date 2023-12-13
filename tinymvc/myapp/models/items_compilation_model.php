<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Items_Compilation model
 */
final class Items_Compilation_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "items_compilation";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "ITEMS_COMPILATION";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'                                 => Types::INTEGER,
        'category_id'                        => Types::INTEGER,
        'is_published'                       => Types::INTEGER,
        'background_images'                  => Types::JSON,
    ];

    /**
     * Scope a query by the published status value
     *
     * @param QueryBuilder $builder
     * @param int $isPublished
     *
     * @return void
     */
    protected function scopeIsPublished(QueryBuilder $builder, int $isPublished): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.`is_published`",
                $builder->createNamedParameter($isPublished, ParameterType::INTEGER, $this->nameScopeParameter('isPublished'))
            )
        );
    }

    /**
     * Scope a query by the category id
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
                "{$this->getTable()}.`category_id`",
                $builder->createNamedParameter($categoryId, ParameterType::INTEGER, $this->nameScopeParameter('categoryId'))
            )
        );
    }

    /**
     * Resolves static relationships with category.
     */
    protected function category(): RelationInterface
    {
        /** @var RelationInterface $relation */
        $relation = $this->belongsTo(Item_Category_Model::class, 'category_id', 'category_id')->enableNativeCast();
        $builder = $relation->getQuery();

        $categoriesTable = $relation->getRelated()->getTable();

        $builder
            ->select(
                "`{$categoriesTable}`.`category_id`",
                "`{$categoriesTable}`.`industry_id`",
                "`{$categoriesTable}`.`parent`",
                "`{$categoriesTable}`.`name`",
                "`{$categoriesTable}`.`link`",
                "CONCAT('[', {$categoriesTable}.`breadcrumbs`, ']') breadcrumbs",
            );

        return $relation;
    }

     /**
     * Left Join with items_compilation_relation_items table.
     */
    protected function bindItemRelation(QueryBuilder $builder): void
    {
        /** @var Item_Compilation_Relation_Model $ItemsCompilationRelationModel */
        $ItemsCompilationRelationModel = model(Item_Compilation_Relation_Model::class);

        $ItemsCompilationRelationTable = $ItemsCompilationRelationModel->getTable();
        $builder->leftJoin(
            $this->getTable(),
            $ItemsCompilationRelationTable,
            $ItemsCompilationRelationTable,
            "`{$ItemsCompilationRelationTable}`.`compilation_id` = `{$this->getTable()}`.`id`"
        );
    }

    /**
     * Relation with complete items compilation relation options.
     */
    protected function itemsRelations(): RelationInterface
    {
        return $this->hasManyThrough(
            Products_Model::class,
            Item_Compilation_Relation_Model::class,
            'compilation_id',
            'id',
            $this->getPrimaryKey(),
            'item_id'
        );
    }
}

/* End of file items_compilation_model.php */
/* Location: /tinymvc/myapp/models/items_compilation_model.php */
