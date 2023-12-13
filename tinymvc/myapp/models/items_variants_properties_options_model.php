<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Items_Variants_Properties_Options model
 */
final class Items_Variants_Properties_Options_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "items_variants_properties_options";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "ITEMS_VARIANTS_PROPERTIES_OPTIONS";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_property'   => Types::INTEGER,
        'id'            => Types::INTEGER,
    ];

    /**
     * Scope query by option ids
     *
     * @param QueryBuilder $builder
     * @param array $optionIds
     *
     * @return void
     */
    protected function scopeIds(QueryBuilder $builder, array $optionIds): void
    {
        if (empty($optionIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->getTable()}`.`id`",
                array_map(
                    fn (int $i, $optionId) => $builder->createNamedParameter((int) $optionId, ParameterType::INTEGER, $this->nameScopeParameter("optionId_{$i}")),
                    array_keys($optionIds),
                    $optionIds
                )
            )
        );
    }

    /**
     * Scope query by property ids
     *
     * @param QueryBuilder $builder
     * @param array $propertyIds
     *
     * @return void
     */
    protected function scopePropertyIds(QueryBuilder $builder, array $propertyIds): void
    {
        if (empty($propertyIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->getTable()}`.`id_property`",
                array_map(
                    fn (int $i, $propertyId) => $builder->createNamedParameter((int) $propertyId, ParameterType::INTEGER, $this->nameScopeParameter("propertyId_{$i}")),
                    array_keys($propertyIds),
                    $propertyIds
                )
            )
        );
    }
}

/* End of file items_variants_properties_options_model.php */
/* Location: /tinymvc/myapp/models/items_variants_properties_options_model.php */
