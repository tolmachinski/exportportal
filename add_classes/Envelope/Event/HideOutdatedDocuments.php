<?php

declare(strict_types=1);

namespace App\Envelope\Event;

use App\Common\Database\Model;
use App\Envelope\Command\CommandInterface;
use App\Envelope\Message\HideOutdatedDocumentsMessage;

final class HideOutdatedDocuments implements CommandInterface
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
    public function __invoke(HideOutdatedDocumentsMessage $message)
    {
        //region Recipients
        $envelopeRecipients = $message->getRecipients();
        if (empty($envelopeRecipients)) {
            return;
        }
        //endregion Recipients

        //region Hide outdated documents
        $this->pivot->deleteAllBy([
            'conditions' => [
                'envelope'     => $message->getEnvelopeId(),
                'recipients'   => $envelopeRecipients,
                'authoriative' => false,
            ],
        ]);
        //endregion Hide outdated documents
    }
}
