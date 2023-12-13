<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Command;

use App\Messenger\Message\Command\UpdateUserIdForBuyerIndustryStats;
use App\Services\BuyerIndustryOfInterestService;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * Save industry handler.
 *
 * @author Bendiucov Tatiana
 */
class UpdateUserIdForBuyerIndustryStatsHandler implements MessageSubscriberInterface
{
    private BuyerIndustryOfInterestService $service;

    public function __construct(BuyerIndustryOfInterestService $service)
    {
        $this->service = $service;
    }

    public function __invoke(UpdateUserIdForBuyerIndustryStats $message)
    {
        $this->service->correlateIdUser($message->getIdUser(), $message->getIdNotLogged());
    }

    /**
     * {@inheritdoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield UpdateUserIdForBuyerIndustryStats::class => ['bus' => 'command.bus'];
    }
}
