<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Database\Types\Types as CustomTypes;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Items_Variants model
 */
final class Items_Variants_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "items_variants";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "ITEMS_VARIANTS";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'            => Types::INTEGER,
        'price'         => CustomTypes::SIMPLE_MONEY,
        'id_item'       => Types::INTEGER,
        'quantity'      => Types::INTEGER,
        'discount'      => Types::INTEGER,
        'final_price'   => CustomTypes::SIMPLE_MONEY,
    ];

    /**
     * This is a method created to obtain complete data about product variants
     *
     * @param int $itemId
     * @param bool $withSpecificData
     * @return array
     */
    public function getItemVariants(int $itemId, bool $withSpecificData = false): array
    {
        /** @var Items_Variants_Properties_Model $itemsVariantsPropertiesModel */
        $itemsVariantsPropertiesModel = model(Items_Variants_Properties_Model::class);

        $result = [
            'variants'      => array_column(
                $this->findAllBy([
                    'conditions'    => [
                        'itemId'    => $itemId,
                    ],
                    'with'  => [
                        'propertyOptions',
                    ],
                    'order'     => [
                        "`{$this->getTable()}`.`id`" => 'ASC',
                    ],
                ]),
                null,
                'id'
            ),
            'properties'    => array_column(
                $itemsVariantsPropertiesModel->findAllBy([
                    'conditions'    => [
                        'itemId'    => $itemId,
                    ],
                    'with'  => [
                        'propertyOptions',
                    ],
                    'order' => [
                        "`{$itemsVariantsPropertiesModel->getTable()}`.`priority`" => 'ASC',
                    ],
                ]),
                null,
                'id'
            ),
        ];

        //in this case we don't use MySQL ordering because on preview item we also need ordering variants, but we can't use MySQL there in case of new variants
        if (!empty($result['variants'])) {
            uasort(
                $result['variants'],
                fn ($currentVariant, $nextVariant) => $currentVariant['final_price'] > $nextVariant['final_price'] ? 1 : -1
            );
        }

        if ($withSpecificData) {
            $optionsUsagesInVariants = [];

            // we record for each property option the ID of the variant in which it is involved
            foreach ($result['variants'] ?: [] as $itemVariant) {
                foreach ($itemVariant['property_options'] ?: [] as $propertyOption) {
                    $optionsUsagesInVariants[$propertyOption['id']][$propertyOption['id_variant']] = $propertyOption['id_variant'];
                }
            }

            $result['optionUsages'] = $optionsUsagesInVariants;
        }

        return $result;
    }

    /**
     * @param array $itemVariantsProperties
     * @param array $itemVariants
     *
     * @return array
     *
     * @deprecated This is a temporary method, created for use it before product posting improvements stage 2
     */
    public function castVariantsDataToLegacyFormat(array $itemVariantsProperties, array $itemVariants): array
    {
        $result = $propertyOptionsKeys = [];
        $propertyIndex = $variantIndex = 1;

        foreach ($itemVariantsProperties as $property) {
            $propertyOptionIndex = 1;
            $propertyKey = "g{$propertyIndex}";
            $result['variant_groups'][$propertyKey] = [
                'group_name'    => $property['name'],
                'group_order'   => $property['priority'],
            ];

            $propertyOptionsKeys[$property['id']]['propertyKey'] = $propertyKey;

            foreach ($property['property_options'] as $propertyOption) {
                $propertyOptionKey = "v{$propertyOptionIndex}";
                $result['variant_groups'][$propertyKey]['variants'][$propertyOptionKey] = $propertyOption['name'];
                $propertyOptionsKeys[$property['id']][$propertyOption['id']] = $propertyOptionKey;
                $propertyOptionIndex++;
            }

            $propertyIndex++;
        }

        foreach ($itemVariants as $itemVariant) {
            $variantKey = "c{$variantIndex}";
            $result['combinations'][$variantKey] = [
                'price' => $itemVariant['price'],
                'img'   => $itemVariant['image'],
            ];

            foreach ($itemVariant['property_options'] as $propertyOption) {
                $variantPropertyKey = $propertyOptionsKeys[$propertyOption['id_property']]['propertyKey'];
                $variantPropertyOptionKey = $propertyOptionsKeys[$propertyOption['id_property']][$propertyOption['id']];

                // in case if any options is selected, options are an array, else are a string
                if (empty($result['combinations'][$variantKey]['combination'][$variantPropertyKey])) {
                    $result['combinations'][$variantKey]['combination'][$variantPropertyKey] = $variantPropertyOptionKey;
                } elseif (is_string($result['combinations'][$variantKey]['combination'][$variantPropertyKey])) {
                    $result['combinations'][$variantKey]['combination'][$variantPropertyKey] = [
                        $result['combinations'][$variantKey]['combination'][$variantPropertyKey]    => $result['combinations'][$variantKey]['combination'][$variantPropertyKey],
                        $variantPropertyOptionKey                                                   => $variantPropertyOptionKey,
                    ];
                } else {
                    $result['combinations'][$variantKey]['combination'][$variantPropertyKey][$variantPropertyOptionKey] = $variantPropertyOptionKey;
                }
            }

            $variantIndex++;
        }

        return $result;
    }

    /**
     * @param array $itemVariantsProperties
     *
     * @return array
     *
     * @deprecated This is a temporary method, created for use it before product posting improvements stage 2
     */
    public function getItemPropertiesOptionsKeysIds(array $itemVariantsProperties): array
    {
        $result = [];
        $propertyIndex = 1;

        foreach ($itemVariantsProperties as $property) {
            $propertyOptionIndex = 1;
            $propertyKey = "g{$propertyIndex}";
            $result[$propertyKey] = [
                'id'    => $property['id'],
                'name'  => $property['name'],
            ];

            foreach ($property['property_options'] as $propertyOption) {
                $result[$propertyKey]['options']["v{$propertyOptionIndex}"] = [
                    'id'    => $propertyOption['id'],
                    'name'  => $propertyOption['name'],
                ];

                $propertyOptionIndex++;
            }

            $propertyIndex++;
        }

        return $result;
    }

    /**
     * Scope query by variant id
     *
     * @param QueryBuilder $builder
     * @param int $variantId
     *
     * @return void
     */
    protected function scopeId(QueryBuilder $builder, int $variantId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`id`",
                $builder->createNamedParameter($variantId, ParameterType::INTEGER, $this->nameScopeParameter('variantId'))
            )
        );
    }

    /**
     * Scope query by variant ids
     *
     * @param QueryBuilder $builder
     * @param array $variantIds
     *
     * @return void
     */
    protected function scopeIds(QueryBuilder $builder, array $variantIds): void
    {
        if (empty($variantIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->getTable()}`.`id`",
                array_map(
                    fn (int $i, $variantId) => $builder->createNamedParameter((int) $variantId, ParameterType::INTEGER, $this->nameScopeParameter("variantId_{$i}")),
                    array_keys($variantIds),
                    $variantIds
                )
            )
        );
    }

    /**
     * Scope query by item id
     *
     * @param QueryBuilder $builder
     * @param int $itemId
     *
     * @return void
     */
    protected function scopeItemId(QueryBuilder $builder, int $itemId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`id_item`",
                $builder->createNamedParameter($itemId, ParameterType::INTEGER, $this->nameScopeParameter('itemId'))
            )
        );
    }

    /**
     * Scope query by item ids
     *
     * @param QueryBuilder $builder
     * @param array $itemIds
     *
     * @return void
     */
    protected function scopeItemIds(QueryBuilder $builder, array $itemIds): void
    {
        if (empty($itemIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->getTable()}`.`id_item`",
                array_map(
                    fn (int $i, $itemId) => $builder->createNamedParameter((int) $itemId, ParameterType::INTEGER, $this->nameScopeParameter("itemId_{$i}")),
                    array_keys($itemIds),
                    $itemIds
                )
            )
        );
    }

    /**
     * Scope query by variant images
     *
     * @param QueryBuilder $builder
     * @param string[] $variantImages
     *
     * @return void
     */
    protected function scopeVariantImages(QueryBuilder $builder, array $variantImages): void
    {
        if (empty($variantImages)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->getTable()}`.`image`",
                array_map(
                    fn (int $i, $variantImage) => $builder->createNamedParameter($variantImage, ParameterType::STRING, $this->nameScopeParameter("variantImage_{$i}")),
                    array_keys($variantImages),
                    $variantImages
                )
            )
        );
    }

    /**
     * Resolves static relationships with items properties
     */
    protected function propertyOptions(): RelationInterface
    {
        /** @var Items_Variants_Properties_Relation_Model $variantsPropertiesRelationModel */
        $variantsPropertiesRelationModel = model(Items_Variants_Properties_Relation_Model::class);
        $variantsPropertiesRelationTable = $variantsPropertiesRelationModel->getTable();

        /** @var Items_Variants_Properties_Options_Model $itemsVariantsPropertiesOptionsModel */
        $itemsVariantsPropertiesOptionsModel = model(Items_Variants_Properties_Options_Model::class);
        $itemsVariantsPropertiesOptionsTable = $itemsVariantsPropertiesOptionsModel->getTable();

        /** @var Items_Variants_Properties_Model $itemsVariantsPropertiesModel */
        $itemsVariantsPropertiesModel = model(Items_Variants_Properties_Model::class);
        $itemsVariantsPropertiesTable = $itemsVariantsPropertiesModel->getTable();

        /** @var RelationInterface $relation */
        $relation = $this->hasMany(Items_Variants_Properties_Relation_Model::class, 'id_variant')->enableNativeCast();

        $relation
            ->getQuery()
            ->select(
                "`{$variantsPropertiesRelationTable}`.`id_variant`",
                "`{$itemsVariantsPropertiesOptionsTable}`.*",
                "`{$itemsVariantsPropertiesTable}`.`name` AS propertyName",
            )
            ->leftJoin(
                $variantsPropertiesRelationTable,
                $itemsVariantsPropertiesOptionsTable,
                $itemsVariantsPropertiesOptionsTable,
                "`{$variantsPropertiesRelationTable}`.`id_property_option` = `{$itemsVariantsPropertiesOptionsTable}`.`id`",
                'left'
            )
            ->leftJoin(
                $itemsVariantsPropertiesOptionsTable,
                $itemsVariantsPropertiesTable,
                $itemsVariantsPropertiesTable,
                "`{$itemsVariantsPropertiesOptionsTable}`.`id_property` = `{$itemsVariantsPropertiesTable}`.`id`",
                'left'
            )
        ;

        return $relation;
    }
}

/* End of file items_variants_model.php */
/* Location: /tinymvc/myapp/models/items_variants_model.php */
