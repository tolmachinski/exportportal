<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Command;

use App\Bridge\Matrix\MatrixConnector;
use App\Bridge\Matrix\Message\SpaceMessageOptions;
use App\Bridge\Matrix\Room\RoomFactoryInterface as MatrixRoomFactoryInterface;
use App\Common\Database\Model;
use App\Common\Exceptions\ContextAwareException;
use App\Common\Exceptions\NotSupportedException;
use App\Messenger\Message\Command\CreateMatrixSpace;
use App\Messenger\Message\Command\DeleteMatrixRoom;
use App\Messenger\Message\Event\MatrixChatSpaceAddedEvent;
use ExportPortal\Matrix\Client\Model\RoomReference;
use GuzzleHttp\Exception\RequestException;
use Matrix_Spaces_Model as MatrixSpacesRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

/**
 * Creates the space on matrix server using provided configurations.
 *
 * @author Anton Zencenco
 */
final class CreateMatrixSpaceHandler implements MessageSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * The spaces factory.
     */
    protected MatrixRoomFactoryInterface $spaceFactory;

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
    protected MatrixSpacesRepository $spacesRepository;

    /**
     * @param MessageBusInterface $commandBus the command bus
     */
    public function __construct(
        MessageBusInterface $commandBus,
        MessageBusInterface $eventBus,
        MatrixConnector $matrixConnector,
        MatrixRoomFactoryInterface $spaceFactory,
        MatrixSpacesRepository $spacesRepository
    ) {
        $this->logger = $matrixConnector->getConfig()->getLogger();
        $this->eventBus = $eventBus;
        $this->commandBus = $commandBus;
        $this->spaceFactory = $spaceFactory;
        $this->matrixConnector = $matrixConnector;
        $this->spacesRepository = $spacesRepository;
    }

    /**
     * Handle message.
     */
    public function __invoke(CreateMatrixSpace $message)
    {
        if (null === $message->getSubject()) {
            throw new ContextAwareException(\sprintf('The subject for space is required.'));
        }

        try {
            // Create new space
            $spaceReference = $this->spaceFactory->create($message->getSubject(), $message->getOptions());
            // Write space down.
            $this->writeSpace($this->spacesRepository, $spaceReference, $message->getSubject(), $message->getOptions());
        } catch (NotSupportedException $e) {
            if ($this->logger) {
                $this->logger->alert('Cannot create space with missing name and options.');
            }

            return;
        } catch (ContextAwareException | RequestException $e) {
            // Write exeption to the logs
            $this->matrixConnector->getExceptionHandler()->handleException($e, 'Failed to send request to the matrix server.');
            // Remove space from matrix server
            if (isset($spaceReference)) {
                $this->commandBus->dispatch(new DeleteMatrixRoom($spaceReference->getRoomId(), null, null, null, true, true), [new DelayStamp(5000)]);
            }

            // Forward exception and prevent retry.
            throw new UnrecoverableMessageHandlingException($e->getMessage(), 0, $e);
        }

        // And finally, send an event to create profile room.
        $this->eventBus->dispatch(new Envelope(
            new MatrixChatSpaceAddedEvent(
                $spaceReference->getRoomId(),
                (new SpaceMessageOptions())
                    ->alias($message->getOptions()->getAlias())
                    ->senderId($message->getOptions()->getSenderId())
            ),
            [new DispatchAfterCurrentBusStamp()]
        ));
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield CreateMatrixSpace::class => ['bus' => 'command.bus'];
    }

    /**
     * Writes the room di in the databse.
     */
    protected function writeSpace(Model $spaceRepository, RoomReference $room, string $spaceName, SpaceMessageOptions $options): void
    {
        $namingStrategy = $this->matrixConnector->getConfig()->getSpacesNamingStrategy();
        $connection = $spaceRepository->getConnection();
        $connection->beginTransaction();

        try {
            $spaceRepository->insertOne([
                'room_id' => $room->getRoomId(),
                'alias'   => $namingStrategy->spaceAlias($options->getAlias() ?? $namingStrategy->spaceName($spaceName)),
                'name'    => $namingStrategy->spaceName($spaceName),
            ]);
            $connection->commit();
        } catch (\Throwable $e) {
            $connection->rollBack();

            throw $e;
        }
    }
}
