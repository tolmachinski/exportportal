<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Datatable\Element\Decorate;

use App\Plugins\Datatable\Output\Element\Decorate\Decorator;
use App\Plugins\Datatable\Output\Element\ElementInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class SenderOrRecipientPassDecorator extends Decorator
{
    /**
     * The current user ID.
     */
    private int $userId;

    /**
     * Creates instance of the sender pass decorator.
     */
    public function __construct(ElementInterface $element, int $userId)
    {
        parent::__construct($element);

        $this->userId = $userId;
    }

    /**
     * {@inheritdoc}
     */
    public function acceptsRow(array $envelope): bool
    {
        $accepted = true;
        if ((int) $envelope['id_sender'] !== $this->userId) {
            $recipients = $envelope['recipients'] ?? $envelope['recipients_routing']['recipients'] ?? null;
            if (null === $recipients) {
                $accepted = false;
            } else {
                if (!$recipients instanceof Collection) {
                    $recipients = new ArrayCollection($recipients);
                }

                $accepted = $recipients->exists(fn ($index, array $recipient) => $this->userId === $recipient['id_user']);
            }
        }

        return $accepted && $this->getElement()->acceptsRow($envelope);
    }
}
