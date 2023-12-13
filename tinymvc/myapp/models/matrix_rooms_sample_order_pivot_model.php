<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use App\Model\MatrixDirectChatPivotTrait;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Matrix_Rooms_Sample_Order_Pivot model.
 */
final class Matrix_Rooms_Sample_Order_Pivot_Model extends Model
{
    use MatrixDirectChatPivotTrait;

    /**
     * The name of the room column.
     */
    protected const ROOM_COLUMN = 'id_room';

    /**
     * The name of the sender column.
     */
    protected const SENDER_COLUMN = 'id_sender';

    /**
     * The name of the resource column.
     */
    protected const RESOURCE_COLUMN = 'id_sample_order';

    /**
     * The name of the recipients column.
     */
    protected const RECIPIENT_COLUMN = 'id_recipient';

    /**
     * The name of the "created at" column.
     */
    protected const CREATED_AT = 'created_at_date';

    /**
     * {@inheritdoc}
     */
    protected string $table = 'matrix_rooms_sample_order_pivot';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'MATRIX_ROOMS_SAMPLE_ORDER_PIVOT';

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
        'id_room'           => Types::INTEGER,
        'id_sender'         => Types::INTEGER,
        'id_recipient'      => Types::INTEGER,
        'id_sample_order'   => Types::INTEGER,
        'created_at_date'   => Types::DATETIME_IMMUTABLE,
        'is_direct'         => Types::BOOLEAN,
    ];

    /**
     * Get the room.
     *
     * @deprecated
     */
    public function getRoom(int $sampleOrderId, int $senderId, int $recipientId, bool $onlyDirect = false): ?array
    {
        return $this->findOneBy([
            'conditions' => [
                'sender'      => $senderId,
                'recipient'   => $recipientId,
                'sampleOrder' => $sampleOrderId,
                'onlyDirect'  => $onlyDirect,
            ],
            'joins' => ['rooms'],
        ]);
    }

    /**
     * Get the room.
     */
    public function findRoom(int $sampleOrderId, int $senderId, int $recipientId, bool $onlyDirect = false): ?array
    {
        return $this->findOneBy([
            'with'       => ['room'],
            'conditions' => [
                'sender'      => $senderId,
                'recipient'   => $recipientId,
                'sampleOrder' => $sampleOrderId,
                'onlyDirect'  => $onlyDirect,
            ],
        ]);
    }

    /**
     * Get the room.
     */
    public function findRooms(int $sampleOrderId, int $senderId, int $recipientId, bool $onlyDirect = false): array
    {
        return $this->findAllBy([
            'with'       => ['room'],
            'conditions' => [
                'sender'      => $senderId,
                'recipient'   => $recipientId,
                'sampleOrder' => $sampleOrderId,
                'onlyDirect'  => $onlyDirect,
            ],
        ]);
    }

    /**
     * Add one room.
     *
     * @deprecated
     * @see Model::insertOne()
     */
    public function add(array $rooms)
    {
        $this->insertMany($rooms);

        return (int) $this->getConnection()->lastInsertId();
    }

    /**
     * Updates many records with one set of changes.
     *
     * @deprecated
     * @see Model::updateMany()
     */
    public function update(array $senderIds, array $recipientIds, int $sampleOrderId, array $record): bool
    {
        $this->updateMany($record, [
            'conditions' => [
                'room'        => 0,
                'senders'     => $senderIds,
                'recipients'  => $recipientIds,
                'sampleOrder' => $sampleOrderId,
            ],
        ]);

        return true;
    }

    /**
     * Scope room by sample order ID.
     */
    protected function scopeSampleOrder(QueryBuilder $builder, int $sampleOrderId): void
    {
        $this->scopeResource($builder, $sampleOrderId);
    }

    /**
     * Scope only direct room.
     */
    protected function scopeOnlyDirect(QueryBuilder $builder, bool $onlyDirect): void
    {
        if (false === $onlyDirect) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                'is_direct',
                $builder->createNamedParameter($onlyDirect, ParameterType::BOOLEAN, $this->nameScopeParameter('onlyDirect', true))
            )
        );
    }

    /**
     * Relation with the sample order.
     */
    protected function sampleOrder(): RelationInterface
    {
        return $this->makeResourceRelation(Sample_Orders_Model::class)->setName('sampleOrder')->disableNativeCast();
    }
}

// End of file matrix_rooms_sample_order_pivot_model.php
// Location: /tinymvc/myapp/models/matrix_rooms_sample_order_pivot_model.php
