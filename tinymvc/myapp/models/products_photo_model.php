<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Produts_Photo model
 */
final class Products_Photo_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "item_photo";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "ITEM_PHOTO";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * Scope a query to filter by item id
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
                "`{$this->table}`.`sale_id`",
                $builder->createNamedParameter($itemId, ParameterType::INTEGER, $this->nameScopeParameter('itemId'))
            )
        );
    }

    /**
     * Scope a query to filter by main photo
     *
     * @param QueryBuilder $builder
     * @param int $isMainPhoto
     *
     * @return void
     */
    protected function scopeIsMainPhoto(QueryBuilder $builder, int $isMainPhoto): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`main_photo`",
                $builder->createNamedParameter($isMainPhoto, ParameterType::INTEGER, $this->nameScopeParameter('isMainPhoto'))
            )
        );
    }

    /**
     * Scope a query to filter by photo name
     *
     * @param QueryBuilder $builder
     * @param string $photoName
     *
     * @return void
     */
    protected function scopePhotoName(QueryBuilder $builder, string $photoName): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`photo_name`",
                $builder->createNamedParameter($photoName, ParameterType::STRING, $this->nameScopeParameter('photoName'))
            )
        );
    }

    /**
     * Scope a query to filter by photo name
     *
     * @param QueryBuilder $builder
     * @param bool $isMainParent
     *
     * @return void
     */
    protected function scopeIsMainParent(QueryBuilder $builder, bool $isMainParent): void
    {
        if ($isMainParent) {
            $builder->andWhere(
                $builder->expr()->eq(
                    "`{$this->table}`.`main_parent`",
                    1
                )
            );
        } else {
            $builder->andWhere(
                $builder->expr()->isNull("`{$this->table}`.`main_parent`")
            );
        }
    }
}

/* End of file produts_photo_model.php */
/* Location: /tinymvc/myapp/models/produts_photo_model.php */
