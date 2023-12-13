<?php

declare(strict_types=1);

namespace App\Renderer;

use App\Common\Database\Model;
use App\Common\DependencyInjection\ServiceLocator\ModelLocator;
use App\Common\Exceptions\OwnershipException;
use App\Common\Traits\FileuploadOptionsAwareTrait;
use TinyMVC_View as Renderer;

/**
 * @author Anton Zencenco
 */
final class VerificationDocumentsViewRenderer extends AbstractViewRenderer
{
    use FileuploadOptionsAwareTrait {
        FileuploadOptionsAwareTrait::getFileuploadOptions as getFormattedFileuploadOptions;
    }

    /**
     * The local repository with the verification documents.
     */
    private Model $verificationDocumentsRepository;

    /**
     * The model locator.
     */
    private ModelLocator $modelLocator;

    /**
     * @param Renderer     $renderer     the page renderer
     * @param ModelLocator $modelLocator the model locator
     */
    public function __construct(Renderer $renderer, ModelLocator $modelLocator)
    {
        parent::__construct($renderer);

        $this->verificationDocumentsRepository = $modelLocator->get(\Verification_Documents_Model::class);
    }

    /**
     * Renderes the popup for inline documents upload.
     *
     * @throws OwnershipException if user is not owner of the document
     */
    public function inlineUploadPopup(int $userId, array $document): void
    {
        // Check if user is the owner of the document.
        $documentId = $document['id_document'];
        if ($document['id_user'] !== $userId) {
            throw new OwnershipException(
                \sprintf('Only owner of the document can access the document "%s"', (string) $documentId)
            );
        }

        $this->render('new/documents/upload_dialog_view', [
            // Document information
            'document'      => [
                'multiple' => $document['type']['document_is_multiple'],
                'subtitle' => $document['subtitle'] ?? null,
            ],

            // File upload options
            'uploadOptions' => $this->getFormattedFileuploadOptions(
                explode(',', config('fileupload_personal_document_formats', 'pdf,jpg,jpeg,png')),
                1,
                1,
                (int) config('fileupload_max_document_file_size', 2 * 1000 * 1000),
                config('fileupload_max_document_file_size_placeh', '2MB')
            ),
        ]);
    }
}
