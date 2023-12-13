<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Order\Command;

use App\Envelope\Bridge\Order\Event\BindEnvelopeAndOrder;
use App\Envelope\Bridge\Order\Message\BindEnvelopeAndOrderMessage;
use App\Envelope\Bridge\Order\Message\CopyEnvelopeMessage;
use App\Envelope\Command\CopyEnvelope as BaseCopyEnvelope;
use App\Envelope\Exception\WriteEnvelopeException;

final class CopyEnvelope extends BaseCopyEnvelope
{
    /**
     * {@inheritdoc}
     *
     * @throws WriteEnvelopeException if failed to write the envelope draft
     */
    public function __invoke(CopyEnvelopeMessage $message)
    {
        $envelopeId = parent::__invoke($message);

        // Bind envelope to the order
        (new BindEnvelopeAndOrder($this->envelopesRepository->getRelation('orderReference')->getRelated()))->__invoke(
            new BindEnvelopeAndOrderMessage((int) $envelopeId, $message->getOrderId())
        );

        return $envelopeId;
    }
}
