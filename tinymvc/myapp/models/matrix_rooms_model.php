<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Matrix_Rooms model.
 */
final class Matrix_Rooms_Model extends Model
{
    /**
     * The name of the "created at" column.
     *
     * @var null|string
     */
    protected const CREATED_AT = 'created_at_date';

    /**
     * {@inheritdoc}
     */
    protected string $table = 'matrix_rooms';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'MATRIX_ROOMS';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id';

    /**
     * {@inheritdoc}
     */
    protected bool $timestamps = true;

    /**
     * {@inheritdoc}
     */
    protected array $casts = [
        'id'                => Types::INTEGER,
        'room_id'           => Types::STRING,
        'created_at_date'   => Types::DATETIME_IMMUTABLE,
    ];

    /**
     * Add one room.
     *
     * @see Matrix_Rooms::insertOne()
     */
    public function add(array $room): int
    {
        return (int) $this->insertOne($room, true);
    }

    /**
     * Get the room.
     */
    public function get(string $roomId): ?array
    {
        return $this->findOneBy([
            'conditions' => [
                'room_id'   => $roomId,
            ],
        ]);
    }

    /**
     * Scope room IDs.
     */
    protected function scopeIds(QueryBuilder $builder, array $roomIds): void
    {
        if (empty($roomIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in('id', \array_map(
                fn (int $index, $id) => $builder->createNamedParameter(
                    $id,
                    ParameterType::INTEGER,
                    $this->nameScopeParameter(\sprintf('referenceId%s%s', $index, \bin2hex(\random_bytes(12))))
                ),
                \array_keys($roomIds),
                $roomIds
            ))
        );
    }

    /**
     * Scope room by room_id.
     */
    protected function scopeRoomId(QueryBuilder $builder, string $roomId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'room_id',
                $builder->createNamedParameter($roomId, ParameterType::STRING, $this->nameScopeParameter('roomId'))
            )
        );
    }
}

// End of file matrix_rooms_model.php
// Location: /tinymvc/myapp/models/matrix_rooms_model.php
