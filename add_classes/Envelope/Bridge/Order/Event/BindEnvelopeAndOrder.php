<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Order\Event;

use App\Common\Database\Model;
use App\Envelope\Bridge\Order\Message\BindEnvelopeAndOrderMessage;
use App\Envelope\Command\CommandInterface;
use Exception;

final class BindEnvelopeAndOrder implements CommandInterface
{
    /**
     * The relation pivot between envelope and order.
     */
    private Model $pivot;

    public function __construct(Model $pivot)
    {
        $this->pivot = $pivot;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(BindEnvelopeAndOrderMessage $message)
    {
        //region Bind to order
        try {
            $this->pivot->insertOne([
                'id_order'    => $message->getOrderId(),
                'id_envelope' => $message->getEnvelopeId(),
            ]);
        } catch (Exception $e) {
            // @todo Log this exception
        }
        //endregion Bind to order
    }
}
