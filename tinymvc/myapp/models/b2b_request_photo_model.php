<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * B2b_Request_Photo model
 */
final class B2b_Request_Photo_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "b2b_request_photos";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "B2B_REQUEST_PHOTOS";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'         => Types::INTEGER,
        'request_id' => Types::INTEGER,
        'photo'      => Types::STRING,
        'is_main'    => Types::BOOLEAN,
    ];

    /**
     * Scope a query to filter by photo ID.
     */
    protected function scopeId(QueryBuilder $builder, int $photoId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn($this->getPrimaryKey()),
                $builder->createNamedParameter($photoId, ParameterType::INTEGER, $this->nameScopeParameter('photoId'))
            )
        );
    }
    /**
     * Scope query for photo ids
     *
     * @param int[] $photoIds
     */
    protected function scopeIds(QueryBuilder $builder, array $photoIds): void
    {
        if (empty($photoIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in($this->getPrimaryKey(), array_map(
                fn (int $i, $id) => $builder->createNamedParameter((int) $id, ParameterType::INTEGER, $this->nameScopeParameter("photoId{$i}", true)),
                array_keys($photoIds),
                $photoIds
            ))
        );
    }

    /**
     * Scope a query to filter by request ID.
     */
    protected function scopeRequestId(QueryBuilder $builder, int $requestId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('request_id'),
                $builder->createNamedParameter($requestId, ParameterType::INTEGER, $this->nameScopeParameter('requestId'))
            )
        );
    }


    /**
     * Scope query for main photo
     */
    protected function scopeIsMain(QueryBuilder $builder, bool $isMain): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'is_main',
                $builder->createNamedParameter($isMain, ParameterType::BOOLEAN, $this->nameScopeParameter('isMain', true))
            )
        );
    }
}

/* End of file b2b_request_photo_model.php */
/* Location: /tinymvc/myapp/models/b2b_request_photo_model.php */
