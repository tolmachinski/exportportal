<?php

declare(strict_types=1);

namespace App\Services\Company;

use App\Common\Database\Exceptions\WriteException;
use App\Common\Database\Model;
use App\Common\Exceptions\FileException;
use App\Common\Exceptions\FileWriteException;
use App\Common\Exceptions\ProcessingException;
use App\Common\Media\Thumbnail\ThumbnailReaderInterface;
use App\Common\Media\Thumbnail\VideoMissingThumbnailException;
use App\Common\Media\Thumbnail\VideoThumbnailInterface;
use App\DataProvider\CompanyProvider;
use App\Event\SellerCompanyLogoUpdateEvent;
use App\Filesystem\CompanyLogoFilePathGenerator;
use App\Filesystem\CompanyVideoFilePathGenerator;
use App\Messenger\Message\Command\Company\RemoveSellerFiles;
use App\Messenger\Message\Event\Lifecycle\UserUpdatedSellerCompanyLogoEvent;
use ExportPortal\Contracts\Filesystem\FilesystemOperator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use Intervention\Image\ImageManager;
use League\Flysystem\PathPrefixer;
use League\Flysystem\StorageAttributes;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToWriteFile;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Mime\MimeTypesInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use TinyMVC_Library_Image_intervention as LegacyImageHandler;

/**
 * @author Anton Zencenco
 */
final class CompanyMediaProcessorService
{
    /**
     * The repository for company.
     */
    private Model $companyRepository;

    /**
     * The company provider.
     */
    private CompanyProvider $companyProvider;

    /**
     * The event bus instance.
     */
    private MessageBusInterface $eventBus;

    /**
     * The command bus instance.
     */
    private MessageBusInterface $commandBus;

    /**
     * The video thubnail reader.
     */
    private ThumbnailReaderInterface $thumbnailReader;

    /**
     * The mime types reader service.
     */
    private MimeTypesInterface $mimeTypes;

    /**
     * The event dispatcher.
     */
    private EventDispatcherInterface $eventDispatcher;

    /**
     * The instance of the image handler (legacy variant).
     */
    private LegacyImageHandler $imageHandler;

    /**
     * The image manager instance.
     */
    private ImageManager $imageManager;

    /**
     * The filesystem storage for company files.
     */
    private FilesystemOperator $companyFilesystem;

    /**
     * The filesystem prefixer for company files.
     */
    private PathPrefixer $companyPrefixer;

    /**
     * The filesystem prefixer for temporary files.
     */
    private PathPrefixer $tempPrefixer;

    /**
     * The options for images.
     */
    private array $imageOptions;

    /**
     * Create the service.
     */
    public function __construct(
        CompanyProvider $companyProvider,
        MessageBusInterface $eventBus,
        MessageBusInterface $commandBus,
        EventDispatcherInterface $eventDispatcher,
        ThumbnailReaderInterface $thumbnailReader,
        LegacyImageHandler $imageHandler,
        ImageManager $imageManager,
        MimeTypesInterface $mimeTypes,
        FilesystemProviderInterface $filesystemProvider,
        array $imageOptions = []
    ) {
        $this->eventBus = $eventBus;
        $this->commandBus = $commandBus;
        $this->imageManager = $imageManager;
        $this->imageHandler = $imageHandler;
        $this->eventDispatcher = $eventDispatcher;
        $this->thumbnailReader = $thumbnailReader;
        $this->companyProvider = $companyProvider;
        $this->companyRepository = $companyProvider->getRepository();
        $this->companyFilesystem = $filesystemProvider->storage('public.storage');
        $this->companyPrefixer = $filesystemProvider->prefixer('public.storage');
        $this->tempPrefixer = $filesystemProvider->prefixer('temp.storage');
        $this->imageOptions = $imageOptions;
        $this->mimeTypes = $mimeTypes;
    }

    /**
     * Update the company log from the temp path.
     *
     * @param int    $companyId the company ID
     * @param string $path      the path to the temp file
     */
    public function updateCompanyLogo(int $companyId, ?string $path = null, bool $isTemporary = false): array
    {
        // First we will take the company data from provider.
        $company = $this->companyProvider->getCompany($companyId);
        // Next we will create relative path to the company directory
        $basePath = CompanyLogoFilePathGenerator::logoFolder($companyId);
        // In order to ensure idempotency of the operation and remova any possibility of
        // invalid processing and following kerfuffle with deleting the old image or new one,
        // we will delete the old logo first without regarding to the provided path value.
        // The catch here is that we need to do this only if the old logo can be removed
        // only if company HAS one.
        if (!empty($company['logo_company'])) {
            // To remove the old logo, we must do this in several steps.
            // First one is to cleanup records in database and second is to remove the file.
            if (!$this->companyRepository->updateOne($companyId, $update = ['logo_company' => ''])) {
                throw new WriteException(\sprintf('Failed to remove old logo for company with ID "%s".', $companyId));
            }

            // Remove existing logo files asynchronously
            $this->commandBus->dispatch(
                new RemoveSellerFiles($companyId, $this->listFilesForRemoval($basePath)),
                [new DelayStamp(5000)]
            );
        }
        // At this moment we don't have the old logo and thumb at ALL.
        // If provided path is empty, then we have nothing to do anymore here
        // and we can leave
        if (null !== $path && '' !== $path) {
            // Next step is to process log using the image handler
            // But the path we got from the company is not complete.
            // To resolve this issue we can use the temp filesystem instance to complete it;
            if ($isTemporary) {
                $sourcePath = $this->tempPrefixer->prefixPath($path);
            } else {
                $sourcePath = $this->companyPrefixer->prefixPath($path);
            }
            // Ensure that directory exists
            $this->companyFilesystem->createDirectory($basePath);
            // Process company logo
            $processedLogo = $this->imageHandler->image_processing(
                ['tmp_name' => $sourcePath, 'name' => \basename($sourcePath)],
                [
                    'destination'   => $this->companyPrefixer->prefixPath($basePath),
                    'handlers'      => [
                        'create_thumbs' => $this->imageOptions['companies']['main']['thumbs'] ?? [],
                        'resize'        => $this->imageOptions['companies']['main']['resize'] ?? [],
                    ],
                ]
            );
            // Error processing in the thumb handler is not so good, so we transform it into exception.
            if (!empty($processedLogo['errors'])) {
                throw new ProcessingException(\implode('. ', $processedLogo['errors']));
            }
            // And finally we will write the changes into the DB
            list($companyLogo) = $processedLogo;
            if (!$this->companyRepository->updateOne($companyId, $update = ['logo_company' => $companyLogo['new_name']])) {
                // Remove existing logo files asynchronously
                $this->commandBus->dispatch(
                    new RemoveSellerFiles($companyId, $this->listFilesForRemoval($basePath)),
                    [new DelayStamp(5000)]
                );

                throw new WriteException(\sprintf('Failed to add new logo for company with ID "%s".', $companyId));
            }
        }

        // Send app events
        $this->eventDispatcher->dispatch(new SellerCompanyLogoUpdateEvent($companyId, $company, $companyLogo['new_name'] ?? null));
        // Send bus events
        $this->eventBus->dispatch(new UserUpdatedSellerCompanyLogoEvent(
            $company['id_user'],
            $companyId,
            !isset($sourcePath) || null === $sourcePath || !isset($companyLogo)
                ? null
                : $this->companyPrefixer->prefixPath(CompanyLogoFilePathGenerator::logoPath($companyId, $companyLogo['new_name']))
        ));

        return \array_merge($company, $update);
    }

    /**
     * Update the company video information.
     *
     * @param int         $companyId the company id
     * @param null|string $url       the new video URL; if null will, the old informaiton will be cleaned
     *
     * @return array the updated company information
     */
    public function updateCompanyVideo(int $companyId, ?string $url = null): array
    {
        // First we will take the company data from provider.
        $company = $this->companyProvider->getCompany($companyId);

        // In order to ensure idempotency of the operation and remova any possibility of
        // invalid processing and following kerfuffle with deleting the old image or new one,
        // we will delete the old video first without regarding to the provided URL value.
        // The catch here is that we need to do this only if the old video can be removed
        // only if company HAS one.
        if (!empty($company['video_company_image'])) {
            // Remove the video fields from DB.
            if (!$this->companyRepository->updateOne($companyId, $update = [
                'video_company_image'   => '',
                'video_company_code'    => '',
                'video_company'         => '',
            ])) {
                throw new WriteException(\sprintf('Failed to remove old video for company with ID "%s".', $companyId));
            }

            // Remove existing logo files asynchronously
            $this->commandBus->dispatch(
                new RemoveSellerFiles($companyId, [CompanyVideoFilePathGenerator::videoPath($companyId, $company['video_company_image'])]),
                [new DelayStamp(5000)]
            );
        }
        // At this moment we don't have the old video and thumb at ALL.
        // If provided URL is empty, then we have nothing to do anymore here
        // and we can leave
        if (null === $url || '' === $url) {
            return \array_merge($company, $update ?? []);
        }

        try {
            /** @var VideoThumbnailInterface */
            $videoThumbnail = $this->thumbnailReader->readThumbnail($url);
            // After that we can create the image instance
            $thumbImage = $this->imageManager->make($videoThumbnail->getContents())->widen(750);
            // Here we make the destination path where thumb must be saved.
            $destinationPath = CompanyVideoFilePathGenerator::videoPath(
                $companyId,
                // Create new name for image using a pseudo-random string of bytes.
                $thumbName = \sprintf('%s.%s', \bin2hex(\random_bytes(16)), $this->mimeTypes->getExtensions($thumbImage->mime)[0] ?? 'jpg')
            );
            // In order to save the thumb we must take the image instance and transform it into stream of data
            // that can be used with filesystem. We do this to not leak the full path to the image manager.
            try {
                $this->companyFilesystem->write($destinationPath, (string) $thumbImage->stream());
            } catch (UnableToWriteFile $e) {
                throw new FileWriteException($destinationPath, 0, $e);
            }

            $videoId = $videoThumbnail->getVideoId();
            $videoSource = $videoThumbnail->getSource();
        } catch (VideoMissingThumbnailException $e) {
            // In the case when thumbnail is not foud, we just simply use video source and ID from exception
            $videoId = $e->getVideoId();
            $videoSource = $e->getSource();
            // And assign empty thumbnail name value
            $thumbName = null;
        }

        // Next we will write the updated information.
        if (
            !$this->companyRepository->updateOne($companyId, $update = [
                'video_company_image'   => $thumbName ?? null,
                'video_company_source'  => $videoSource,
                'video_company_code'    => $videoId,
                'video_company'         => $url,
            ])
        ) {
            if (null !== $thumbName) {
                // Remove existing logo files asynchronously
                $this->commandBus->dispatch(
                    new RemoveSellerFiles($companyId, [$destinationPath]),
                    [new DelayStamp(5000)]
                );
            }

            // And throw exception.
            throw new WriteException(\sprintf('Failed to write the new video for company with ID "%s".', $companyId));
        }

        // And finally, we will return the updated company information.
        return \array_merge($company, $update);
    }

    /**
     * Removes the logo files for the company.
     *
     * @param int      $companyId the company ID
     * @param string[] $files     the list of files that are placed inside of the company directory
     */
    public function removeCompanyFiles(int $companyId, array $files = []): void
    {
        // If list of files empty, then we will leave rigth away.
        if (empty($files)) {
            return;
        }
        // Walk over provided paths and delete files
        foreach ($files as $path) {
            if (
                0 !== \strpos(
                    ltrim($path, '\\/'),
                    trim(CompanyLogoFilePathGenerator::directory($companyId), '\\/') . '/'
                )
            ) {
                continue;
            }

            // And now we delete the file.
            try {
                $this->companyFilesystem->delete($path);
            } catch (UnableToDeleteFile $e) {
                // If we failed to delete the file then we will throw an exception
                throw new FileException(
                    \sprintf('Failed to delete the file "%s" for company "%s"', $path, $companyId),
                    0,
                    $e
                );
            }
        }
    }

    /**
     * Lists the content of the directory for removal.
     */
    private function listFilesForRemoval(string $basePath): array
    {
        return \array_map(
            // Get only paths
            fn (StorageAttributes $attributes) => $attributes->path(),
            // List the content of the logo directory and filter only the files
            \array_filter(
                \iterator_to_array($this->companyFilesystem->listContents($basePath)),
                fn (StorageAttributes $attributes) => 'file' === $attributes->type()
            )
        );
    }
}
