<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Command;

use App\Bridge\Matrix\GuestAccess;
use App\Bridge\Matrix\MatrixConnector;
use App\Bridge\Matrix\Message\RoomMessageOptions;
use App\Bridge\Matrix\Room\RoomFactoryInterface as MatrixRoomFactoryInterface;
use App\Bridge\Matrix\RoomPreset;
use App\Bridge\Matrix\RoomVisibility;
use App\Bridge\Matrix\StateEventType;
use App\Common\Database\Model;
use App\Common\Exceptions\ContextAwareException;
use App\Common\Exceptions\NotSupportedException;
use App\Messenger\Message\Command\AbstractCreateDirectMatrixChatRoom;
use App\Messenger\Message\Command\CreateDirectMatrixChatRoom;
use App\Messenger\Message\Command\CreateDirectMatrixChatRoomNow;
use App\Messenger\Message\Command\DeleteMatrixRoom;
use App\Messenger\Message\Event\MatrixChatRoomAddedEvent;
use ExportPortal\Matrix\Client\Model\RoomReference;
use ExportPortal\Matrix\Client\Model\StateEvent;
use GuzzleHttp\Exception\RequestException;
use Matrix_Rooms_Model as MatrixRoomsRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

/**
 * Creates the matrix chat room of the direct conversation type.
 *
 * @author Anton Zencenco
 */
final class CreateDirectMatrixChatRoomHandler implements MessageSubscriberInterface, LoggerAwareInterface
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
        $this->roomsRepository = $roomsRepository;
        $this->matrixConnector = $matrixConnector;
    }

    /**
     * Handle message.
     */
    public function __invoke(AbstractCreateDirectMatrixChatRoom $message)
    {
        if (null === $message->getSubject()) {
            throw new ContextAwareException(\sprintf('The subject for direct room is required.'));
        }
        $options = (new RoomMessageOptions())
            ->preset((string) RoomPreset::from(RoomPreset::PRIVATE_CHAT))
            ->resource($message->getResourceOptions())
            ->senderId((string) $message->getSenderId())
            ->recipientId((string) $message->getRecipientId())
            ->visibility((string) RoomVisibility::from(RoomVisibility::PRIVATE_VISIBILITY))
            ->encrypted($this->matrixConnector->getConfig()->isEncryptionEnabled())
            ->initialState([
                (new StateEvent())->setType((string) StateEventType::from(StateEventType::GUEST_ACCESS))->setContent([
                    'guest_access' => (string) GuestAccess::from(GuestAccess::FORBIDDEN),
                ]),
            ])
        ;

        try {
            $roomReference = $this->roomFactory->create($message->getSubject(), $options);
        } catch (NotSupportedException $e) {
            if ($this->logger) {
                $this->logger->alert('Cannot create room with missing name and options');
            }

            return;
        } catch (ContextAwareException | RequestException $e) {
            // Write exeption to the logs
            $this->matrixConnector->getExceptionHandler()->handleException($e, 'Failed to send request to the matrix server.');

            throw $e;
        }

        // Write room into database.
        $this->writeRoom($this->roomsRepository, $roomReference);
        // And finally, send an event to create profile room.
        $this->eventBus->dispatch(new Envelope(
            new MatrixChatRoomAddedEvent(
                $roomReference->getRoomId(),
                (new RoomMessageOptions())
                    ->resource($message->getResourceOptions())
                    ->senderId((string) $message->getSenderId())
                    ->recipientId((string) $message->getRecipientId())
            ),
            [new DispatchAfterCurrentBusStamp()]
        ));
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield CreateDirectMatrixChatRoom::class => ['bus' => 'command.bus'];
        yield CreateDirectMatrixChatRoomNow::class => ['bus' => 'command.bus'];
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
            $this->commandBus->dispatch(new DeleteMatrixRoom($room->getRoomId(), null, null, null, true, true), [new DelayStamp(5000)]);
            $connection->rollBack();

            throw $e;
        }
    }
}
