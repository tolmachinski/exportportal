<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Command;

use App\Messenger\Message\Command\SaveBuyerIndustryOfInterest;
use App\Services\BuyerIndustryOfInterestService;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * Save industry handler.
 *
 * @author Bendiucov Tatiana
 */
class SaveBuyerIndustryOfInterestHandler implements MessageSubscriberInterface
{
    private BuyerIndustryOfInterestService $service;

    public function __construct(BuyerIndustryOfInterestService $service)
    {
        $this->service = $service;
    }

    public function __invoke(SaveBuyerIndustryOfInterest $message)
    {
        $this->service->addIndustryOfInterest($message->getIdCategory(), $message->getIdUser(), $message->getIdNotLogged(), $message->getCollectType());
    }

    /**
     * {@inheritdoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield SaveBuyerIndustryOfInterest::class => ['bus' => 'command.bus'];
    }
}
