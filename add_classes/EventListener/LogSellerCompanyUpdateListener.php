<?php

declare(strict_types=1);

namespace App\EventListener;

use Activity_Log_Messages_Model as ActivityMessageRepository;
use App\Event\SellerCompanyLogoUpdateEvent;
use App\Event\SellerCompanyUpdateEvent;
use App\Logger\ActivityLogger;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use const App\Logger\Activity\OperationTypes\DELETE_LOGO;
use const App\Logger\Activity\OperationTypes\EDIT;
use const App\Logger\Activity\ResourceTypes\COMPANY;

/**
 * @author Anton Zencenco
 */
class LogSellerCompanyUpdateListener implements EventSubscriberInterface
{
    /**
     * The activity logger.
     */
    protected ActivityLogger $activityLogger;

    /**
     * The actvity mesasges repository.
     */
    protected ActivityMessageRepository $mesasgeRepository;

    /**
     * @param ActivityLogger            $activityLogger    the activity logger
     * @param ActivityMessageRepository $mesasgeRepository the actvity mesasges repository
     */
    public function __construct(ActivityLogger $activityLogger, ActivityMessageRepository $mesasgeRepository)
    {
        $this->activityLogger = $activityLogger;
        $this->mesasgeRepository = $mesasgeRepository;
    }

    /**
     * Handles the event.
     */
    public function onGeneralUpdate(SellerCompanyUpdateEvent $event): void
    {
        if ($event->isAddendumUpdate()) {
            return;
        }

        $company = $event->getCompany();
        $changes = $event->getChanges();
        list($old, $current) = $this->makeUpdateDiff($company, $changes);
        $this->activityLogger->setOperationType(EDIT);
        $this->activityLogger->setResourceType(COMPANY);
        $this->activityLogger->setResource($event->getCompanyId());
        $this->activityLogger->info($this->mesasgeRepository->get_message(COMPANY, EDIT), \array_merge(
            \get_user_activity_context(),
            [
                'company' => ['name' => $company['name_company'], 'url' => \getCompanyURL($company)],
                'changes' => \compact('old', 'current'),
            ]
        ));
    }

    /**
     * Handles the event.
     */
    public function onLogoUpdate(SellerCompanyLogoUpdateEvent $event): void
    {
        $company = $event->getCompany();

        // Report logo deletion
        list($old, $current) = $this->makeUpdateDiff($company, ['logo_company' => '']);
        $this->activityLogger->setOperationType(DELETE_LOGO);
        $this->activityLogger->setResourceType(COMPANY);
        $this->activityLogger->setResource($event->getCompanyId());
        $this->activityLogger->info($this->mesasgeRepository->get_message(COMPANY, DELETE_LOGO), \array_merge(
            \get_user_activity_context(),
            [
                'company' => ['name' => $company['name_company'], 'url' => \getCompanyURL($company)],
                'changes' => \compact('old', 'current'),
            ]
        ));

        // Report logo addition
        if (null === $event->getLogoPath()) {
            return;
        }

        list($old, $current) = $this->makeUpdateDiff(['logo_company' => ''], ['logo_company' => $event->getLogoPath()]);
        $this->activityLogger->setOperationType(EDIT);
        $this->activityLogger->setResourceType(COMPANY);
        $this->activityLogger->setResource($event->getCompanyId());
        $this->activityLogger->info($this->mesasgeRepository->get_message(COMPANY, EDIT), \array_merge(
            \get_user_activity_context(),
            [
                'company' => ['name' => $company['name_company'], 'url' => \getCompanyURL($company)],
                'changes' => \compact('old', 'current'),
            ]
        ));
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // should be the last one to allow header changes by other listeners first
            SellerCompanyUpdateEvent::class     => ['onGeneralUpdate', -255],
            SellerCompanyLogoUpdateEvent::class => ['onLogoUpdate', -255],
        ];
    }

    /**
     * Makes the compound diff for company data and updated data.
     */
    private function makeUpdateDiff(array $old, array $new)
    {
        // For some reasons, array_diff() constantly fails that is why we need do it by the hands.
        // We need to get the intersection between two data sets and use it later to create diff.
        // But first we need to transorm some objects into strings.
        $moneyFormatter = (new DecimalMoneyFormatter(new ISOCurrencies()));
        if (isset($old['revenue_company'])) {
            // Given that we have price in USD we can use sinple formatter.
            $old['revenue_company'] = $moneyFormatter->format($old['revenue_company']);
        }
        if (isset($new['revenue_company'])) {
            // Given that we have price in USD we can use sinple formatter.
            $new['revenue_company'] = $moneyFormatter->format($new['revenue_company']);
        }
        // Remove update date from diff and other related entities
        unset($old['updated_company'], $new['updated_company'], $old['user'], $new['user'], $old['type'], $new['type']);

        $section = array_intersect_key($old, $new);
        if (empty($section)) {
            return [null, null];
        }
        $new = array_diff($new, \array_intersect_key($old, \array_flip(\array_keys($section))));
        $old = array_intersect_key($section, $new);
        ksort($old);
        ksort($new);

        return [$old, $new];
    }
}
