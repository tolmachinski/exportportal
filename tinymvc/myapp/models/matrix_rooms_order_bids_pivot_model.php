<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Model\MatrixDirectChatPivotTrait;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Matrix_Rooms_Order_Bids_Pivot model.
 */
final class Matrix_Rooms_Order_Bids_Pivot_Model extends Model
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
    protected const RESOURCE_COLUMN = 'id_order_bid';

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
    protected string $table = 'matrix_rooms_order_bids_pivot';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'MATRIX_ROOMS_ORDER_BIDS_PIVOT';

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
        'id_order_bid'      => Types::INTEGER,
        'created_at_date'   => Types::DATETIME_IMMUTABLE,
    ];

    /**
     * Get the room.
     */
    public function getRoom(int $orderBidId, int $senderId, int $recipientId): ?array
    {
        return $this->findOneBy([
            'conditions' => [
                'sender'    => $senderId,
                'orderBid'  => $orderBidId,
                'recipient' => $recipientId,
            ],
            'joins' => ['rooms'],
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
    public function update(array $senderIds, array $recipientIds, int $orderId, array $record): bool
    {
        $this->updateMany($record, [
            'conditions' => [
                'room'       => 0,
                'senders'    => $senderIds,
                'orderBid'   => $orderId,
                'recipients' => $recipientIds,
            ],
        ]);

        return true;
    }

    /**
     * Scope room by order bid ID.
     */
    protected function scopeOrderBid(QueryBuilder $builder, int $orderBidId): void
    {
        $this->scopeResource($builder, $orderBidId);
    }
}

// End of file matrix_rooms_order_bid_pivot_model.php
// Location: /tinymvc/myapp/models/matrix_rooms_order_bid_pivot_model.php
