<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Command;

use App\Common\DependencyInjection\ServiceLocator\LibraryLocator;
use App\Common\Exceptions\NotSupportedException;
use App\Documents\File\SystemFile;
use App\Messenger\Message\Command\AddSampleOrderDocument;
use App\Services\SampleOrdersService;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use App\Plugins\EPDocs\Rest\RestClient as DocumentsClient;
use App\Plugins\EPDocs\Rest\Resources\FilePermissions as FilePermissionsResource;
use App\Plugins\EPDocs\Rest\Resources\User as UserResource;
use App\Plugins\EPDocs;
use ExportPortal\Component\Notifier\Bridge\Matrix\MatrixOptions;
use ExportPortal\Component\Notifier\Bridge\Matrix\Metadata\CustomFileMetadata;
use TinyMVC_Library_Make_Pdf as PdfGenerator;
use Mpdf\Output\Destination as PdfDestinatin;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;

/**
 * Adds the document to the sample order.
 *
 * @author Anton Zencenco
 */
final class AddSampleOrderDocumentHandler implements MessageSubscriberInterface
{
    /**
     * The sample order service.
     */
    private SampleOrdersService $sampleOrdersService;

    /**
     * The PDF generator.
     */
    private PdfGenerator $pdfGenerator;

    /**
     * The EPDocs client.
     */
    private DocumentsClient $apiClient;

    /**
     * The chatter instance.
     */
    private ChatterInterface $chatter;

    /**
     * @param SampleOrdersService $sampleOrdersService the sample order service
     * @param LibraryLocator      $libraryLocator      the library locator
     */
    public function __construct(SampleOrdersService $sampleOrdersService, DocumentsClient $apiClient, ChatterInterface $chatter, LibraryLocator $libraryLocator)
    {
        $this->chatter = $chatter;
        $this->apiClient = $apiClient;
        $this->pdfGenerator = $libraryLocator->get(PdfGenerator::class);
        $this->sampleOrdersService = $sampleOrdersService;
    }

    /**
     * Handle message.
     */
    public function __invoke(AddSampleOrderDocument $message)
    {
        // Export file and return the information about it.
        $file = $this->exportFile(
            $this->apiClient,
            $message->getBuyerId(),
            $message->getSellerId(),
            $message->getFilename(),
            $this->strToStream(
                $this->generateFile($message->getOrderId(), $message->getType(), $message->getFilename())
            )
        );

        // Read sample order from storage.
        // Given that the requests to EPDocs can take a long time, the export can happen many times, so
        // we will read sample order only AFTER we finished export.
        $sampleOrder = $this->sampleOrdersService->getSampleOrderInformation($message->getOrderId(), null, true);
        $purchaseOrder = $sampleOrder['purchase_order'] ?? [];
        $orderTimeline = new ArrayCollection($sampleOrder['purchase_order_timeline'] ?? []);
        $purchaseOrder[$message->getType()]['file'] = [
            'id'            => $file->getId()->toString(),
            'name'          => $file->getName(),
            'size'          => $file->getSize(),
            'mime'          => $file->getMime(),
            'media'         => $file->getMedia(),
            'extension'     => $file->getExtension(),
            'upload_date'   => $file->getUploadDate()->format(DATE_ATOM),
            'original_name' => $file->getOriginalName(),
        ];
        $orderTimeline->add([
            'date'    => (new \DateTimeImmutable())->format(DATE_ATOM),
            'user'    => \trim(\sprintf('%s %s', $sampleOrder['buyer']['fname'], $sampleOrder['buyer']['lname'])),
            'message' => $message->getMessage(),
        ]);

        // After that we will update the sample order
        try {
            $this->sampleOrdersService->updateOrder(
                $message->getOrderId(),
                [
                    'purchase_order'          => $purchaseOrder,
                    'purchase_order_timeline' => $orderTimeline->getValues(),
                ],
                true
            );
        } catch (\Throwable $e) {
            // If failed to save the information abou the exported document,
            // then we need to delete the exported file.
            try {
                /** @var EPDocs\Rest\Resources\File $filesResource */
                $filesResource = $this->apiClient->getResource(EPDocs\Rest\Resources\File::class);
                $filesResource->deleteFile($file->getId());
            } catch (\Throwable $th) {
                // Silently fail
            }

            throw $e;
        }

        // And finally, let's send the message to the room.
        if ($roomId = $message->getRoomId()) {
            $options = (new MatrixOptions())
                ->roomId($roomId)
                ->messageType('com.exportportal.file')
                ->metadata(
                    (new CustomFileMetadata())
                        ->id((string) $file->getId())
                        ->type($file->getExtension())
                        ->name($file->getName())
                        ->size($file->getSize())
                        ->weight(\fileSizeSuffixText($file->getSize()))
                )
            ;

            // Send the attachment to the chat room.
            $this->chatter->send(new ChatMessage($file->getOriginalName(), $options));
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield AddSampleOrderDocument::class => ['bus' => 'command.bus'];
    }

    // /**
    //  * Writes the room ID in the databse.
    //  */
    // private function writeRoom(Model $sampleOrder, RoomReference $room): void
    // {
    // $connection = $roomsRepository->getConnection();
    // $connection->beginTransaction();

    // try {
    //     $roomsRepository->insertOne(['room_id' => $room->getRoomId()]);
    //     $connection->commit();
    // } catch (\Throwable $e) {
    //     $this->commandBus->dispatch(new DeleteMatrixRoom($room->getRoomId(), null, null, null, true, true), [new DelayStamp(5000)]);
    //     $connection->rollBack();

    //     throw $e;
    // }
    // }

    /**
     * Exports the prepared files.
     *
     * @param resource $fileContent
     */
    private function exportFile(DocumentsClient $client, int $buyerId, int $sellerId, string $name, $fileContent): SystemFile
    {
        /** @var UserResource $usersResource */
        $usersResource = $client->getResource(UserResource::class);
        /** @var EPDocs\Rest\Resources\File $filesResource */
        $filesResource = $client->getResource(EPDocs\Rest\Resources\File::class);
        /** @var FilePermissionsResource $permissionsResource */
        $permissionsResource = $client->getResource(FilePermissionsResource::class);
        // Get the HTTP origin
        $httpOrigin = $client->getConfiguration()->getHttpOrigin();
        // Get (or create) buyer's account from the API
        $buyer = $usersResource->findUserIfNotCreate(EPDocs\Util::createContext($buyerId, $httpOrigin));
        // Get (or create) seller's account from the API
        $seller = $usersResource->findUserIfNotCreate(EPDocs\Util::createContext($sellerId, $httpOrigin));
        // Get (or create) manager's account from the API
        $manager = $usersResource->findUserIfNotCreate(EPDocs\Util::createContext($client->getConfiguration()->getDefaultUserId(), $httpOrigin));
        // Write file to the API
        $storedFile = $filesResource->createFileFromResource($manager->getId(), $fileContent, $name, 'attachment');
        // Update permissions for the buyer
        $permissionsResource->createPermissions($storedFile->getId(), $buyer->getId(), FilePermissionsResource::PERMISSION_READ);
        // Update permissions for the seller
        $permissionsResource->createPermissions($storedFile->getId(), $seller->getId(), FilePermissionsResource::PERMISSION_READ);

        return new SystemFile(
            $storedFile->getId(),
            $storedFile->getName(),
            $storedFile->getExtension(),
            $storedFile->getSize(),
            $storedFile->getType(),
            $storedFile->getOriginalName()
        );
    }

    /**
     * Generates the file for order and type.
     */
    private function generateFile(int $orderId, string $type, string $filename): string
    {
        switch ($type) {
            case 'invoice': return $this->pdfGenerator->sample_order_invoice($orderId)->Output($filename, PdfDestinatin::STRING_RETURN);
            case 'contract': return $this->pdfGenerator->sample_order_contract($orderId)->Output($filename, PdfDestinatin::STRING_RETURN);

            default:
                throw new NotSupportedException(
                    \sprintf('The type "%s" is not supported by this handler.', $type)
                );
        }
    }

    /**
     * Transforms the string to stream;.
     *
     * @throws \RuntimeException if failed to create the stream
     *
     * @return resource
     */
    private function strToStream(string $str)
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $str);
        rewind($stream);

        if (false === $stream) {
            throw new \RuntimeException('Failed to create the stram.');
        }

        return $stream;
    }
}
