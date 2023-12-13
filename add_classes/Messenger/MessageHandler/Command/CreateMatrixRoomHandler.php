<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Command;

use App\Bridge\Matrix\MatrixConnector;
use App\Bridge\Matrix\Message\RoomMessageOptions;
use App\Bridge\Matrix\Room\RoomFactoryInterface as MatrixRoomFactoryInterface;
use App\Common\Database\Model;
use App\Common\Exceptions\ContextAwareException;
use App\Common\Exceptions\NotSupportedException;
use App\Messenger\Message\Command\CreateMatrixRoom;
use App\Messenger\Message\Command\DeleteMatrixRoom;
use App\Messenger\Message\Event\MatrixChatRoomAddedEvent;
use ExportPortal\Matrix\Client\Model\RoomReference;
use GuzzleHttp\Exception\RequestException;
use Matrix_Rooms_Model as MatrixRoomsRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

/**
 * Creates the room on matrix server using provided configurations.
 *
 * @author Anton Zencenco
 */
final class CreateMatrixRoomHandler implements MessageSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * The rooms factory.
     */
    protected MatrixRoomFactoryInterface $roomFactory;

    /**
     * The matrix connector.
     */
    protected MatrixConnector $matrixConnector;

    /**
     * The command bus.
     */
    protected MessageBusInterface $commandBus;

    /**
     * The event bus.
     */
    protected MessageBusInterface $eventBus;

    /**
     * The rooms local repository.
     */
    protected MatrixRoomsRepository $roomsRepository;

    /**
     * @param MessageBusInterface $commandBus the command bus
     */
    public function __construct(
        MessageBusInterface $commandBus,
        MessageBusInterface $eventBus,
        MatrixConnector $matrixConnector,
        MatrixRoomFactoryInterface $roomFactory,
        MatrixRoomsRepository $roomsRepository
    ) {
        $this->logger = $matrixConnector->getConfig()->getLogger();
        $this->eventBus = $eventBus;
        $this->commandBus = $commandBus;
        $this->roomFactory = $roomFactory;
        $this->matrixConnector = $matrixConnector;
        $this->roomsRepository = $roomsRepository;
    }

    /**
     * Handle message.
     */
    public function __invoke(CreateMatrixRoom $message)
    {
        try {
            // Create room
            $roomReference = $this->roomFactory->create($message->getSubject(), $message->getOptions());
            // Write room into database.
            $this->writeRoom($this->roomsRepository, $roomReference);
        } catch (NotSupportedException $e) {
            if ($this->logger) {
                $this->logger->alert('Cannot create room with missing name and options');
            }

            return;
        } catch (ContextAwareException | RequestException $e) {
            // Write exeption to the logs
            $this->matrixConnector->getExceptionHandler()->handleException($e, 'Failed to send request to the matrix server.');
            // Remove room if exists
            if (isset($roomReference)) {
                $this->commandBus->dispatch(new DeleteMatrixRoom($roomReference->getRoomId(), null, null, null, true, true), [new DelayStamp(5000)]);
            }

            // Forward exception and prevent retry.
            throw new UnrecoverableMessageHandlingException($e->getMessage(), 0, $e);
        }

        // And finally, send an event to create profile room.
        $this->eventBus->dispatch(new Envelope(
            new MatrixChatRoomAddedEvent(
                $roomReference->getRoomId(),
                (new RoomMessageOptions())
                    ->resource($message->getOptions()->getResource())
                    ->senderId($message->getOptions()->getSenderId())
                    ->recipientId($message->getOptions()->getRecipientId())
            ),
            [new DispatchAfterCurrentBusStamp()]
        ));
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield CreateMatrixRoom::class => ['bus' => 'command.bus'];
    }

    /**
     * Writes the room ID in the databse.
     */
    protected function writeRoom(Model $roomsRepository, RoomReference $room): void
    {
        $connection = $roomsRepository->getConnection();
        $connection->beginTransaction();

        try {
            $roomsRepository->insertOne(['room_id' => $room->getRoomId()]);
            $connection->commit();
        } catch (\Throwable $e) {
            $connection->rollBack();

            throw $e;
        }
    }
}
