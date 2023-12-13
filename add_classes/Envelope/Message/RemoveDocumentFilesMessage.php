<?php

declare(strict_types=1);

namespace App\Envelope\Message;

/**
 * Message that is send to remove external documents.
 */
final class RemoveDocumentFilesMessage
{
    /**
     * The list of exteranl files.
     *
     * @var Array<int, string>
     */
    private array $externalFiles;

    /**
     * Create instance of the messasge.
     *
     * @param Array<int, string> $externalFiles
     */
    public function __construct(array $externalFiles = [])
    {
        $this->externalFiles = $externalFiles;
    }

    /**
     * Get the list of exteranl files.
     *
     * @return Array<int, string>
     */
    public function getExternalFiles(): array
    {
        return $this->externalFiles;
    }
}
