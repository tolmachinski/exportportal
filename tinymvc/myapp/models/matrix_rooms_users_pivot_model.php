<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Model\MatrixDirectChatPivotTrait;
use Doctrine\DBAL\Types\Types;

/**
 * Matrix_Rooms_Users_Pivot model.
 */
final class Matrix_Rooms_Users_Pivot_Model extends Model
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
    protected const RESOURCE_COLUMN = null;

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
    protected string $table = 'matrix_rooms_users_pivot';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'MATRIX_ROOMS_USERS_PIVOT';

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
        'created_at_date'   => Types::DATETIME_IMMUTABLE,
    ];

    /**
     * Get the room.
     */
    public function getRoom(int $senderId, int $recipientId): ?array
    {
        return $this->findOneBy([
            'conditions' => [
                'sender'    => $senderId,
                'recipient' => $recipientId,
            ],
            'joins' => ['rooms'],
        ]);
    }

    /**
     * Add one room.
     *
     * @deprecated
     * @see Model::insertMany()
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
    public function update(array $senderIds, array $recipientIds, array $record): bool
    {
        $this->updateMany($record, [
            'conditions' => [
                'room'       => 0,
                'senders'    => $senderIds,
                'recipients' => $recipientIds,
            ],
        ]);

        return true;
    }
}

// End of file matrix_rooms_users_pivot_model.php
// Location: /tinymvc/myapp/models/matrix_rooms_users_pivot_model.php
