<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Notifier\Notification;

use App\Common\Contracts\Notifier\SystemChannel;
use App\Envelope\Bridge\Notifier\Sender;
use App\Envelope\Bridge\Notifier\Type;
use ExportPortal\Bridge\Notifier\Notification\SystemNotification;
use InvalidArgumentException;
use UnhandledMatchError;

class EnvelopeNotification extends SystemNotification
{
    /**
     * The envelope Id.
     */
    protected int $envelopeId;

    /**
     * The envelope title.
     */
    protected ?string $envelopeTitle;

    /**
     * The sender information.
     */
    protected ?Sender $sender;

    /**
     * Creates instance of the notification.
     */
    public function __construct(
        string $subject,
        int $envelopeId,
        ?string $envelopeTitle = null,
        ?Sender $sender = null,
        ?array $channels = null
    ) {
        parent::__construct($subject, [], true, $channels ?? [SystemChannel::STORAGE()->label(), SystemChannel::MATRIX()->label()]);

        $this->sender = $sender;
        $this->envelopeId = $envelopeId;
        $this->envelopeTitle = $envelopeTitle;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject(): string
    {
        try {
            return Type::browserCode(new Type(parent::getSubject()));
        } catch (InvalidArgumentException | UnhandledMatchError $e) {
            return parent::getSubject();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getContext(): array
    {
        return \array_merge(parent::getContext(), $this->prepareReplacementOptions());
    }

    /**
     * Prepares the replacement options for the message.
     */
    protected function prepareReplacementOptions(): array
    {
        $replacements = [
            '[DOCUMENT]'       => \orderNumber($this->envelopeId),
            '[DOCUMENT_ID]'    => $this->envelopeId,
            '[DOCUMENT_TITLE]' => \cleanOutput($this->envelopeTitle ?? '') ?: null,
        ];
        if (null !== $this->sender) {
            $replacements['[USER]'] = \cleanOutput($this->sender->getName() ?? '') ?: null;
            $replacements['[USER_URL]'] = \getUserLink($this->sender->getName() ?? '', $this->sender->getId(), $this->sender->getGroup() ?? null) ?: null;
        }

        return $replacements;
    }
}
