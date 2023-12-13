<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Command\Media\Legacy;

use App\Common\Exceptions\ProcessingException;
use App\Messenger\Message\Command\Media\Legacy\ProcessImage;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use TinyMVC_Library_Image_intervention as LegacyImageHandler;

/**
 * Processes the image using legacy image handler.
 *
 * @author Anton Zencenco
 */
final class ProcessImageHandler implements MessageSubscriberInterface
{
    /**
     * The instance of the image handler (legacy variant).
     */
    private LegacyImageHandler $imageHandler;

    /**
     * Undocumented function.
     *
     * @param LegacyImageHandler $imageHandler the instance of the image handler (legacy variant)
     */
    public function __construct(LegacyImageHandler $imageHandler)
    {
        $this->imageHandler = $imageHandler;
    }

    /**
     * Handles the image processing message.
     */
    public function __invoke(ProcessImage $message): void
    {
        if (empty($message->getFilePath())) {
            return;
        }

        $processedLogo = $this->imageHandler->image_processing(
            ['tmp_name' => $message->getFilePath(), 'name' => $message->getOriginalName()],
            \array_merge(
                ['destination' => $message->getDestination()],
                $message->getConfigurations()
            )
        );
        // Error processing in the thumb handler is not so good, so we transform it into exception.
        if (!empty($processedLogo['errors'])) {
            throw new ProcessingException(\implode('. ', $processedLogo['errors']));
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield ProcessImage::class => ['bus' => 'command.bus'];
    }
}
