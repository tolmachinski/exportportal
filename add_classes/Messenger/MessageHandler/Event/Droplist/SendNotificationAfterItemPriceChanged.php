<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Event\Droplist;

use App\Common\Contracts\Droplist\NotificationType;
use App\Common\Contracts\Notifier\SystemChannel;
use App\DataProvider\DroplistItemsDataProvider;
use App\Email\ChangeDroplistPrice;
use App\Messenger\Message\Event\Droplist\DroplistEntryPriceChangedEvent;
use ExportPortal\Bridge\Mailer\Mime\RefAddress;
use ExportPortal\Bridge\Notifier\Notification\SystemNotification;
use ExportPortal\Bridge\Notifier\Recipient\Recipient;
use Money\MoneyFormatter;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Notifier\NotifierInterface;

/**
 * Event for sending notification.
 */
final class SendNotificationAfterItemPriceChanged implements MessageSubscriberInterface
{
    private NotifierInterface $notifier;
    private MailerInterface $mailer;
    private DroplistItemsDataProvider $droplistDataProvider;
    private MoneyFormatter $moneyFormatter;

    public function __construct(NotifierInterface $notifier, MailerInterface $mailer, DroplistItemsDataProvider $droplistDataProvider, MoneyFormatter $moneyFormatter)
    {
        $this->notifier = $notifier;
        $this->mailer = $mailer;
        $this->droplistDataProvider = $droplistDataProvider;
        $this->moneyFormatter = $moneyFormatter;
    }

    /**
     * Event on price changed.
     */
    public function onPriceChanged(DroplistEntryPriceChangedEvent $message): void
    {
        $droplistEntry = $this->droplistDataProvider->getItemForNotifications($message->getId());
        if (!empty($droplistEntry)) {
            if (in_array($droplistEntry['notification_type'], [NotificationType::WEBSITE(), NotificationType::BOTH()])) {
                $this->sendWebsiteNotification($droplistEntry);
            }

            if (in_array($droplistEntry['notification_type'], [NotificationType::EMAIL(), NotificationType::BOTH()])) {
                $this->sendEmailNotification($droplistEntry);
            }
        }
    }

    /**
     * Message handlers.
     */
    public static function getHandledMessages(): iterable
    {
        yield DroplistEntryPriceChangedEvent::class => ['bus' => 'event.bus', 'method' => 'onPriceChanged'];
    }

    /**
     * Send email notification.
     */
    private function sendEmailNotification(array $droplistEntry): void
    {
        $this->mailer->send(
            (
                new ChangeDroplistPrice(
                    makeItemUrl($droplistEntry['item_id'], $droplistEntry['item_title'], true),
                    $droplistEntry['item_title'],
                    $this->moneyFormatter->format($droplistEntry['droplist_price']),
                    $this->moneyFormatter->format($droplistEntry['item_price'])
                )
            )
            ->to(
                new RefAddress((string) $droplistEntry['user_id'], new Address($droplistEntry['user']['email']))
            )
            ->subjectContext(
                [
                    '[productName]' => $droplistEntry['item_title']
                ]
            )
        );
    }

    /**
     * Send website notification.
     */
    private function sendWebsiteNotification(array $droplistEntry): void
    {
        $this->notifier->send(
            (new SystemNotification('droplist_price_changed', [
                '[ITEM_LINK]'   => makeItemUrl($droplistEntry['item_id'], $droplistEntry['item_title'], true),
                '[ITEM_NAME]'   => $droplistEntry['item_title'],
            ]))->channels([(string) SystemChannel::STORAGE()]),
            new Recipient($droplistEntry['user_id'])
        );
    }
}
