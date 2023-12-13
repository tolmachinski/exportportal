<?php

declare(strict_types=1);

namespace App\Services;

use App\Bridge\Matrix\MatrixConnector;
use App\Common\Database\Model;
use App\Common\DependencyInjection\ServiceLocator\ModelLocator;
use App\Common\Exceptions\NotFoundException;
use App\Common\Exceptions\NotSupportedException;
use ExportPortal\Contracts\Chat\Recource\ResourceOptionsInterface;
use ExportPortal\Contracts\Chat\Recource\ResourceType;
use InvalidArgumentException;
use Matrix_Rooms_Model as RoomsRepository;

class ChatBindingService
{
    /**
     * The model locator.
     */
    protected ModelLocator $modelLocator;

    /**
     * The matrix connector.
     */
    protected MatrixConnector $matrixConnector;

    /**
     * The rooms repository.
     */
    protected RoomsRepository $roomsRepository;

    /**
     * @param MatrixConnector $matrixConnector the matrix connector
     * @param ModelLocator    $modelLocator    the model locator
     */
    public function __construct(MatrixConnector $matrixConnector, ModelLocator $modelLocator)
    {
        $this->modelLocator = $modelLocator;
        $this->matrixConnector = $matrixConnector;
        $this->roomsRepository = $modelLocator->get(RoomsRepository::class);
    }

    /**
     * Get room bindigs for provided type.
     *
     * @param ResourceOptionsInterface $resourceOptions the resource options
     * @param null|int|string          $senderId        the sender app ID or matrix ID
     * @param null|int|string          $recipientId     the recipient app ID or matrix ID
     * @param null|int|string          $roomId          the room app ID or matrix ID
     *
     * @throws NotFoundException when model for provided type is not found
     */
    public function getRoomBindings(ResourceOptionsInterface $resourceOptions, $senderId = null, $recipientId = null, $roomId = null): ?array
    {
        list(
            'model'         => $model,
            'recordsFinder' => $recordsFinder,
            'resourceId'    => $resourceId,
            'roomId'        => $roomRecordId
        ) = $this->getBindingParamters($resourceOptions, $roomId, (string) $senderId ?: null, (string) $recipientId ?: null);
        $recordsFinder = $recordsFinder ?? fn (object $model) => $model->getRoom($resourceId, $senderId, $recipientId, $roomRecordId);
        if (null === $model) {
            throw new NotFoundException(
                \sprintf('The model for type "%s" is not defined', (string) $resourceOptions->getType())
            );
        }

        return $recordsFinder($model, $senderId, $recipientId, $resourceId, $roomId);
    }

    /**
     * Check if room bindings exist.
     *
     * @param ResourceOptionsInterface $resourceOptions the resource options
     * @param null|int|string          $senderId        the sender app ID or matrix ID
     * @param null|int|string          $recipientId     the recipient app ID or matrix ID
     * @param null|int|string          $roomId          the room app ID or matrix ID
     *
     * @throws NotFoundException when room for provided type is not found is not found
     */
    public function hasRoomBindings(ResourceOptionsInterface $resourceOptions, $senderId = null, $recipientId = null, $roomId = null): bool
    {
        return !empty($this->getRoomBindings($resourceOptions, $senderId, $recipientId, $roomId));
    }

    /**
     * Prepare binding data for room and resource.
     *
     * @param null|int|string $roomId      the room app ID or matrix ID
     * @param null|int|string $senderId    the sender app ID or matrix ID
     * @param null|int|string $recipientId the recipient app ID or matrix ID
     */
    public function bindResourceToRoom(ResourceOptionsInterface $resource, $roomId = null, $senderId = null, $recipientId = null): void
    {
        // Get the list of configuration values for resource bindings.
        list(
            // The configured model name
            'modelName'       => $modelName,
            // The found model
            'model'           => $model,
            // The re-configured room column key
            'roomKey'         => $roomKey,
            // The normalized room ID
            'roomId'          => $roomRecordId,
            // The re-configured sender column key
            'senderKey'       => $senderKey,
            // The normalized sender ID
            'senderId'        => $senderUserId,
            // The re-configured recipient column key
            'recipientKey'    => $recipientKey,
            // The normalized recipient ID
            'recipientId'     => $recipientUserId,
            // The re-configured type resource column key
            'resourceKey'     => $resourceKey,
            // The normalized recipient ID
            'resourceId'      => $resourceId,
            // Allow/disallow cross-references
            'crossReferences' => $crossReferences,
            // Allow/disallow records duplicates
            'allowDuplicates' => $allowDuplicates,
            // The room records finder
            'recordsFinder'   => $recordsFinder,
            // Additional data to add with every record
            'typeData'        => $typeData
        ) = $this->getBindingParamters($resource, $roomId, $senderId, $recipientId);

        // Resolve the pivot model. If not found, then just leave.
        if (null === $modelName || null === $model) {
            return;
        }
        // The next step is to check the room ID column name.
        if (null === $roomKey) {
            // If the room key is null, then we have nothing to do here - room cannot be bound to the resource.
            return;
        }
        // In the cases when duplicated records are dissalowed
        // we need to check if there is no records with such values in the database.
        if (
            false === $allowDuplicates
            && null !== $recordsFinder
            && !empty($recordsFinder($model, $senderUserId, $recipientUserId, $resourceId, $roomRecordId))
        ) {
            return;
        }
        // Now we will prepare the data for binding
        $recourceData = [];
        if (null !== $resourceKey) {
            $recourceData = [$resourceKey => $resourceId];
        }
        $records = [
            \array_merge(
                [$roomKey => $roomRecordId, $senderKey => $senderId, $recipientKey => $recipientId],
                $recourceData,
                $typeData
            ),
        ];
        // If the cross references are enabled then we just need to add a second record with
        // mirrored sender and recipient.
        if ($crossReferences) {
            $records[] = \array_merge(
                [$roomKey => $roomRecordId, $senderKey => $recipientId, $recipientKey => $senderId],
                $recourceData,
                $typeData
            );
        }
        foreach ($records as &$entry) {
            $entry = \array_filter($entry, fn ($v) => null !== $v);
        }

        // And write them down into the database.
        $this->writeBinding($model, $records);
    }

    /**
     * Prepare binding data for room and resource.
     *
     * @param null|int|string $roomId      the room app ID or matrix ID
     * @param null|int|string $senderId    the sender app ID or matrix ID
     * @param null|int|string $recipientId the recipient app ID or matrix ID
     *
     * @throws InvalidArgumentException if resource type is missing from the resource options
     * @throws NotSupportedException    if resource type is not supported
     */
    public function getBindingParamters(ResourceOptionsInterface $resource, $roomId = null, $senderId = null, $recipientId = null): array
    {
        // The first thing we need to check its the resource type. If it empty
        // then we have nothing to do here - chat room binding requires the
        // type to be present.
        if (null === $resourceType = $resource->getType()) {
            throw new InvalidArgumentException('The resource options must contain the resource type value.');
        }

        $roomKey = 'id_room';
        $senderKey = 'id_sender';
        $recipientKey = 'id_recipient';
        $resourceKey = null;
        $recordsFinder = null;
        $crossReferences = true;
        $allowDuplicates = true;
        $modelName = null;
        $typeData = [];

        switch ($resourceType) {
            case ResourceType::from(ResourceType::PO):
                $modelName = \Matrix_Rooms_Po_Pivot_Model::class;
                $resourceKey = 'id_po';

                break;
            case ResourceType::from(ResourceType::B2B):
                $modelName = \Matrix_Rooms_B2b_Pivot_Model::class;
                $resourceKey = 'id_b2b';

                break;
            case ResourceType::from(ResourceType::USER):
                $modelName = \Matrix_Rooms_Users_Pivot_Model::class;
                $resourceKey = null;

                break;
            case ResourceType::from(ResourceType::ORDER):
                $modelName = \Matrix_Rooms_Orders_Pivot_Model::class;
                $resourceKey = 'id_order';

                break;
            case ResourceType::from(ResourceType::OFFER):
                $modelName = \Matrix_Rooms_Offers_Pivot_Model::class;
                $resourceKey = 'id_offer';

                break;
            case ResourceType::from(ResourceType::INQUIRY):
                $modelName = \Matrix_Rooms_Inquiry_Pivot_Model::class;
                $resourceKey = 'id_inquiry';

                break;
            case ResourceType::from(ResourceType::ESTIMATE):
                $modelName = \Matrix_Rooms_Estimate_Pivot_Model::class;
                $resourceKey = 'id_estimate';

                break;
            case ResourceType::from(ResourceType::ORDER_BID):
                $modelName = \Matrix_Rooms_Order_Bids_Pivot_Model::class;
                $resourceKey = 'id_order_bid';

                break;
            case ResourceType::from(ResourceType::B2B_RESPONSE):
                $modelName = \Matrix_Rooms_B2b_Response_Pivot_Model::class;
                $resourceKey = 'id_b2b_response';

                break;
            case ResourceType::from(ResourceType::SAMPLE_ORDER):
                $modelName = \Matrix_Rooms_Sample_Order_Pivot_Model::class;
                $resourceKey = 'id_sample_order';
                $typeData['is_direct'] = false;
                $allowDuplicates = false;
                $recordsFinder = fn (
                    \Matrix_Rooms_Sample_Order_Pivot_Model $repository,
                    ?int $senderId,
                    ?int $recipientId,
                    ?int $recordId
                ): ?array => $repository->findRoom($recordId, $senderId, $recipientId);

                break;
            case ResourceType::from(ResourceType::UPCOMING_ORDER):
                $modelName = \Matrix_Rooms_Upcoming_Orders_Pivot_Model::class;
                $resourceKey = 'id_upcoming_order';

                break;

            default:
                throw new NotSupportedException(\sprintf('The type "%s" is not supported.', (string) $resourceType));
        }

        return [
            // The configured model name
            'modelName'       => $modelName,
            // The found model
            'model'           => null === $modelName ? null : $this->modelLocator->get($modelName),
            // The re-configured room column key
            'roomKey'         => $roomKey,
            // The normalized room ID
            'roomId'          => null === $roomId ? null : $this->normalizeRoomIdOrGetFromStoredValue($roomId),
            // The re-configured sender column key
            'senderKey'       => $senderKey,
            // The normalized sender ID
            'senderId'        => null === $senderKey ? null : $this->normalizeUserIdOrGetFromReference($senderId),
            // The re-configured recipient column key
            'recipientKey'    => $recipientKey,
            // The normalized recipient ID
            'recipientId'     => null === $recipientKey ? null : $this->normalizeUserIdOrGetFromReference($recipientId),
            // The re-configured type reference column key
            'resourceKey'     => $resourceKey,
            // Normalize resource ID
            'resourceId'      => null === $resourceKey ? null : ((int) $resource->getId() ?: null),
            // Allow/disallow cross-references
            'crossReferences' => $crossReferences,
            // Allow/disallow records duplicates
            'allowDuplicates' => $allowDuplicates,
            // The room records finder
            'recordsFinder'   => $recordsFinder,
            // Additional data to add with every record
            'typeData'        => $typeData,
        ];
    }

    /**
     * Get room by ID.
     *
     * @throws NotFoundException when room is not found
     */
    protected function getRoomByMxId(string $roomMxId): array
    {
        // We NEED to have room at this stage, so if room is not found, then it is bad.
        if (null === ($room = $this->roomsRepository->get($roomMxId))) {
            throw new NotFoundException(\sprintf('The room with ID "%s" is not found.', $roomMxId));
        }

        return $room;
    }

    /**
     * Writes the room binding into the database.
     */
    private function writeBinding(Model $repository, array $bindingData): void
    {
        $connection = $repository->getConnection();
        $connection->beginTransaction();

        try {
            $repository->insertMany($bindingData);
            $connection->commit();
        } catch (\Throwable $e) {
            $connection->rollBack();

            throw $e;
        }
    }

    /**
     * Normalize the user ID or get it from user matrix reference.
     *
     * @param int|string $userId the user app ID or matrix ID
     */
    private function normalizeUserIdOrGetFromReference($userId): ?int
    {
        if (null === $userId) {
            return null;
        }
        if (\is_numeric($userId)) {
            return (int) $userId;
        }
        $userReference = $this->matrixConnector->getUserReferenceProvider()->getReferenceByUserMxid($userId) ?? [];

        return $userReference['id_user'] ?? null;
    }

    /**
     * Normalize the user ID or get it from user matrix reference.
     *
     * @param int|string $roomId the room app ID or matrix ID
     */
    private function normalizeRoomIdOrGetFromStoredValue($roomId): ?int
    {
        if (null === $roomId) {
            return null;
        }
        if (\is_numeric($roomId)) {
            return (int) $roomId;
        }
        $room = $this->getRoomByMxId($roomId);

        return $room['id'] ?? null;
    }
}
