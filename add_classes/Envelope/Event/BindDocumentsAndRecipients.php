<?php

declare(strict_types=1);

namespace App\Envelope\Event;

use App\Common\Database\Model;
use App\Envelope\Command\CommandInterface;
use App\Envelope\Message\BindDocumentsAndRecipientsMessage;
use Exception;

final class BindDocumentsAndRecipients implements CommandInterface
{
    /**
     * The relation pivot between envelope and order.
     */
    private Model $pivot;

    public function __construct(Model $documentsRepository)
    {
        $this->pivot = $documentsRepository->getRelation('recipients')->getRelated();
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(BindDocumentsAndRecipientsMessage $message)
    {
        //region Recipients
        $envelopeRecipients = $message->getRecipients();
        if (empty($envelopeRecipients)) {
            return;
        }
        //endregion Recipients

        //region Documents
        $envelopeDocuments = $message->getDocuments();
        if (empty($envelopeDocuments)) {
            return;
        }
        //endregion Documents

        //region Bind to documents
        $bindings = [];
        $evelopeId = $message->getEnvelopeId();
        foreach ($envelopeDocuments as $documentId) {
            foreach ($envelopeRecipients as $recipientId) {
                $bindings[] = [
                    'id_envelope'  => $evelopeId,
                    'id_document'  => $documentId,
                    'id_recipient' => $recipientId,
                ];
            }
        }

        try {
            $this->pivot->insertMany($bindings);
        } catch (Exception $e) {
            // @todo Log this exception
        }
        //endregion Bind to documents
    }
}
