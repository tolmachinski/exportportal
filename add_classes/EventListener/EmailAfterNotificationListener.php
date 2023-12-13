<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use App\Common\DependencyInjection\ServiceLocator\ModelLocator;
use App\Email\Systmessages;
use ExportPortal\Bridge\Mailer\Mime\RefAddress;
use ExportPortal\Contracts\Notifier\Message\StorageMessage;
use ExportPortal\Contracts\Notifier\Message\StorageMessageOptions;
use ExportPortal\Contracts\Notifier\Recipient\ReferencedRecipientInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Notifier\Event\SentMessageEvent;

/**
 * The listener that sends the email after some of the notifications were sent.
 *
 * @author Anton Zencenco
 */
final class EmailAfterNotificationListener implements EventSubscriberInterface
{
    /**
     * The settings repository.
     */
    private Model $settingRepository;

    /**
     * The mailer instance.
     */
    private MailerInterface $mailer;

    /**
     * Create listener.
     *
     * @param modelLocator $locator - the model locator
     */
    public function __construct(MailerInterface $mailer, ModelLocator $locator)
    {
        $this->mailer = $mailer;
        $this->settingRepository = $locator->get(\User_System_Message_Settings_Model::class);
    }

    /**
     * Handle the sent message.
     */
    public function onMessage(SentMessageEvent $event): void
    {
        // if there is not message - skip.
        if (!$event->getMessage()) {
            return;
        }
        // Otherwise get the original message.
        $message = $event->getMessage()->getOriginalMessage();
        $options = $message->getOptions();
        // If message is not instane of StorageMessage::class or options are empty
        // we must leave.
        if (
            (!$message instanceof StorageMessage)
            || !$options
            || (!$options instanceof StorageMessageOptions)
        ) {
            return;
        }
        // Next we need to check if this is standard notification (not calendar-bound)
        // and type is `new`. If any of these conditions fail then we will stop handling this event.
        // The same goes for the empty module.
        if (!$options->getSystem() || 'new' !== $options->getType() || empty($options->getModule())) {
            return;
        }

        // Next we need to gather recipients and their IDs.
        // Given that the notification is already sent, we can be sure
        // that we have already pre-processed list of recipients
        $recipients = [];
        /** @var ReferencedRecipientInterface $recipient */
        foreach ($message->getRecipients() as $recipient) {
            // Skip non-refrence recipients
            if (!$recipient instanceof ReferencedRecipientInterface) {
                continue;
            }

            $recipients[] = $recipient->getReference();
        }
        // If list of recipients is empty we cannot do anything with it.
        if (empty($recipients)) {
            return;
        }
        // After that we need to get the settings for email subscriptions.
        // Given this query, if we have the record for user, it means that
        // user can recieve the email for it.
        $settings = $this->settingRepository->findAllBy([
            'with'   => ['user' => function (RelationInterface $relation) {
                $relation->getQuery()->select(
                    $relation->getRelated()->getPrimaryKey(),
                    '`fname` as `firstName`',
                    'TRIM(CONCAT(`fname`, " ", `lname`)) as `fullName`',
                    '`email`'
                );
            }],
            'scopes' => ['users' => $recipients, 'module' => (int) $options->getModule()],
            'exists' => [
                $this->settingRepository->getRelationsRuleBuilder()->whereHas('appModule', function ($query, $relation) {
                    $relation->getRelated()->getScope('hasEmailsEnabled')->call(
                        $relation->getRelated(),
                        $query,
                        true
                    );
                }),
            ],
        ]);
        // As long as we have a list of setitings, we can transform them into email recipients.
        $emailRecipients = [];
        foreach (\array_column($settings, 'user', 'id_user') as $userId => list('fullName' => $fullName, 'email' => $email, 'firstName' => $firstName)) {
            // If email empty - just skip it.
            if (empty($email)) {
                continue;
            }

            $emailRecipients[] = new RefAddress((string) $userId, new Address($email, $fullName));
        }
        // If list of recipients is empty - also leave.
        if (empty($emailRecipients)) {
            return;
        }
        // And finally, send email
        $this->mailer->send(
            (new Systmessages($firstName, (new \DateTimeImmutable())->format('Y-m-d H:i:s'), $options->getTitle(), ''))->to(...$emailRecipients)
        );
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // Should be the last one to allow properly write log.
            SentMessageEvent::class => ['onMessage', -255],
        ];
    }
}
