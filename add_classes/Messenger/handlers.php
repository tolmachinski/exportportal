<?php

declare(strict_types=1);

namespace App\Messenger;

use App\Bridge\Matrix\MatrixConnector;
use App\Bridge\Matrix\Room\RoomFactory;
use App\Bridge\Matrix\Room\SpaceFactory;
use App\Common\DependencyInjection\ServiceLocator\LibraryLocator;
use App\Common\DependencyInjection\ServiceLocator\ModelLocator;
use App\Messenger\MessageHandler\Command;
use App\Messenger\MessageHandler\Command\Company as CompanyCommands;
use App\Messenger\MessageHandler\Command\ElasticSearch as ElasticSearchCommands;
use App\Messenger\MessageHandler\Command\EPDocs as EPDocsCommands;
use App\Messenger\MessageHandler\Command\Media as MediaCommands;
use App\Messenger\MessageHandler\Command\Profile as ProfileCommands;
use App\Messenger\MessageHandler\Event;
use App\Messenger\MessageHandler\Event\Company as CompanyEvents;
use App\Messenger\MessageHandler\Event\EditRequest as EditRequestEvents;
use App\Messenger\MessageHandler\Event\ElasticSearch as ElasticSearchEvents;
use App\Messenger\MessageHandler\Event\Profile as ProfileEvents;
use App\Plugins\EPDocs\Rest\RestClient as EpDocsClient;
use App\Services\BuyerIndustryOfInterestService;
use App\Services\ChatBindingService;
use App\Services\Company\CompanyMediaProcessorService;
use App\Services\EditRequest\CompanyEditRequestDocumentsService;
use App\Services\EditRequest\ProfileEditRequestDocumentsService;
use App\Services\SampleOrdersService;
use ExportPortal\Bridge\Symfony\Component\Messenger\MessengerInterface;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use Money\MoneyFormatter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\NotifierInterface;

return static function (ContainerInterface $container): iterable {
    /** @var ModelLocator $modelLocator */
    $modelLocator = $container->get(ModelLocator::class);
    /** @var LibraryLocator $libraryLocator */
    $libraryLocator = $container->get(LibraryLocator::class);

    // Test handler
    yield Command\HelloWorldHandler::class;

    // Notifier handlers
    yield MessageHandler\SmsMessageHandler::class                                => fn () => new MessageHandler\SmsMessageHandler($container->get('texter.transports'));
    yield MessageHandler\ChatMessageHandler::class                               => fn () => new MessageHandler\ChatMessageHandler($container->get('chatter.transports'));
    yield MessageHandler\PushMessageHandler::class                               => fn () => new MessageHandler\PushMessageHandler($container->get('texter.transports'));
    yield MessageHandler\EmailMessageHandler::class                              => fn () => new MessageHandler\EmailMessageHandler($container->get('mailer.transports'));
    yield MessageHandler\StorageMessageHandler::class                            => fn () => new MessageHandler\StorageMessageHandler($container->get('texter.transports'));

    // App command handlers
    yield ElasticSearchCommands\SellerCompanyIndexHandler::class                 => fn () => new ElasticSearchCommands\SellerCompanyIndexHandler($modelLocator->get(\Elasticsearch_Company_Model::class));
    yield ElasticSearchCommands\B2bRequestsIndexHandler::class                   => fn () => new ElasticSearchCommands\B2bRequestsIndexHandler($modelLocator->get(\Elasticsearch_B2b_Model::class));
    yield ElasticSearchCommands\QuestionAndAnswerIndexHandler::class             => fn () => new ElasticSearchCommands\QuestionAndAnswerIndexHandler($modelLocator->get(\Elasticsearch_Questions_Model::class));
    yield ProfileCommands\AcceptTemporaryFileHandler::class                      => fn () => new ProfileCommands\AcceptTemporaryFileHandler($container->get(ProfileEditRequestDocumentsService::class));
    yield CompanyCommands\AcceptTemporaryFileHandler::class                      => fn () => new CompanyCommands\AcceptTemporaryFileHandler($container->get(CompanyEditRequestDocumentsService::class));
    yield CompanyCommands\UpdateCompanyVideoHandler::class                       => fn () => new CompanyCommands\UpdateCompanyVideoHandler($container->get(CompanyMediaProcessorService::class));
    yield CompanyCommands\UpdateSellerLogoHandler::class                         => fn () => new CompanyCommands\UpdateSellerLogoHandler($container->get(CompanyMediaProcessorService::class));
    yield CompanyCommands\RemoveSellerLogoFilesHandler::class                    => fn () => new CompanyCommands\RemoveSellerLogoFilesHandler($container->get(CompanyMediaProcessorService::class));
    yield EPDocsCommands\DeleteFileHandler::class                                => fn () => new EPDocsCommands\DeleteFileHandler($container->get(EpDocsClient::class));
    yield MediaCommands\Legacy\ProcessImageHandler::class                        => fn () => new MediaCommands\Legacy\ProcessImageHandler($libraryLocator->get(\TinyMVC_Library_Image_intervention::class));

    yield Event\Droplist\SendNotificationAfterItemPriceChanged::class                => fn () => new Event\Droplist\SendNotificationAfterItemPriceChanged($container->get(NotifierInterface::class), $container->get(\Symfony\Component\Mailer\MailerInterface::class), $container->get(\App\DataProvider\DroplistItemsDataProvider::class), $container->get(MoneyFormatter::class));

    yield Event\Product\ProductDroplistUpdatesSubscriber::class                    => fn () => new Event\Product\ProductDroplistUpdatesSubscriber($container->get(MessengerInterface::class)->getBus('event.bus'), $modelLocator->get(\Items_Droplist_Model::class), $modelLocator->get(\Products_Model::class), $container->get(MessengerInterface::class)->getBus('command.bus'));

    yield Event\Product\TriggerGranularActionsWhenProductWasUpdated::class          => fn () => new Event\Product\TriggerGranularActionsWhenProductWasUpdated($container->get(MessengerInterface::class)->getBus('event.bus'));

    // App event handlers
    yield ElasticSearchEvents\ReIndexSellerCompanyAfterUpdateSubscriber::class   => fn () => new ElasticSearchEvents\ReIndexSellerCompanyAfterUpdateSubscriber($container->get(MessengerInterface::class)->getBus('command.bus'));
    yield ElasticSearchEvents\UpdateB2bRequestsAfterCompanyLogoUpdate::class     => fn () => new ElasticSearchEvents\UpdateB2bRequestsAfterCompanyLogoUpdate($modelLocator->get(\Elasticsearch_B2b_Model::class));
    yield ElasticSearchEvents\ReIndexQuestionsAndAnswersSubscriber::class        => fn () => new ElasticSearchEvents\ReIndexQuestionsAndAnswersSubscriber($container->get(MessengerInterface::class)->getBus('command.bus'), $modelLocator->get(\Community_Questions_Model::class), $modelLocator->get(\Community_Question_Answers_Model::class));
    yield ElasticSearchEvents\ReIndexB2bRequestsSubscriber::class                => fn () => new ElasticSearchEvents\ReIndexB2bRequestsSubscriber($container->get(MessengerInterface::class)->getBus('command.bus'), $modelLocator->get(\B2b_Requests_Model::class));
    yield EditRequestEvents\UpdateEventsProxySubscriber::class                   => fn () => new EditRequestEvents\UpdateEventsProxySubscriber($container->get(MessengerInterface::class)->getBus('event.bus'));
    yield ProfileEvents\DeleteDocumentsWhenRequestDeclined::class                => fn () => new ProfileEvents\DeleteDocumentsWhenRequestDeclined($container->get(ProfileEditRequestDocumentsService::class));
    yield CompanyEvents\DeleteDocumentsWhenRequestDeclined::class                => fn () => new CompanyEvents\DeleteDocumentsWhenRequestDeclined($container->get(CompanyEditRequestDocumentsService::class));
    yield CompanyEvents\OptimizeImageWhenLogoIsUpdated::class                    => fn () => new CompanyEvents\OptimizeImageWhenLogoIsUpdated($modelLocator->get(\Image_optimization_Model::class));

    // Matrix command handlers
    yield Command\SyncMatrixUserHandler::class                                   => fn () => new Command\SyncMatrixUserHandler($container->get(MessengerInterface::class)->getBus('command.bus'), $container->get(MatrixConnector::class), $container->get(ModelLocator::class));
    yield Command\CreateMatrixRoomHandler::class                                 => fn () => new Command\CreateMatrixRoomHandler($container->get(MessengerInterface::class)->getBus('command.bus'), $container->get(MessengerInterface::class)->getBus('event.bus'), $container->get(MatrixConnector::class), $container->get(RoomFactory::class), $modelLocator->get(\Matrix_Rooms_Model::class));
    yield Command\CreateMatrixSpaceHandler::class                                => fn () => new Command\CreateMatrixSpaceHandler($container->get(MessengerInterface::class)->getBus('command.bus'), $container->get(MessengerInterface::class)->getBus('event.bus'), $container->get(MatrixConnector::class), $container->get(SpaceFactory::class), $modelLocator->get(\Matrix_Spaces_Model::class));
    yield Command\DeleteMatrixRoomHandler::class                                 => fn () => new Command\DeleteMatrixRoomHandler($container->get(MatrixConnector::class));
    yield Command\CreateMatrixUserHandler::class                                 => fn () => new Command\CreateMatrixUserHandler($container->get(MessengerInterface::class)->getBus('command.bus'), $container->get(MessengerInterface::class)->getBus('event.bus'), $container->get(MatrixConnector::class), $modelLocator->get(\User_Model::class), $modelLocator->get(\Matrix_Users_Model::class));
    yield Command\DeactivateMatrixUserHandler::class                             => fn () => new Command\DeactivateMatrixUserHandler($container->get(MatrixConnector::class), $modelLocator->get(\Matrix_Users_Model::class));
    yield Command\CreateMatrixPorfileRoomHandler::class                          => fn () => new Command\CreateMatrixPorfileRoomHandler($container->get(MessengerInterface::class)->getBus('command.bus'), $container->get(MessengerInterface::class)->getBus('event.bus'), $container->get(MatrixConnector::class), $container->get(RoomFactory::class), $modelLocator->get(\Matrix_Users_Model::class), $modelLocator->get(\Matrix_Spaces_Model::class));
    yield Command\CreateMatrixServerNoticesRoomHandler::class                    => fn () => new Command\CreateMatrixServerNoticesRoomHandler($container->get(MessengerInterface::class)->getBus('command.bus'), $container->get(MessengerInterface::class)->getBus('event.bus'), $container->get(MatrixConnector::class), $container->get(RoomFactory::class), $modelLocator->get(\Matrix_Users_Model::class), $modelLocator->get(\Matrix_Spaces_Model::class));
    yield Command\Matrix\CreateCargoRoomHandler::class                           => fn () => new Command\Matrix\CreateCargoRoomHandler($container->get(MessengerInterface::class)->getBus('command.bus'), $container->get(MessengerInterface::class)->getBus('event.bus'), $container->get(MatrixConnector::class), $container->get(RoomFactory::class), $modelLocator->get(\Matrix_Users_Model::class), $modelLocator->get(\Matrix_Spaces_Model::class));
    yield Command\Matrix\UpdateCargoRoomHandler::class                           => fn () => new Command\Matrix\UpdateCargoRoomHandler($container->get(MessengerInterface::class)->getBus('event.bus'), $container->get(MatrixConnector::class));
    yield Command\Matrix\UpdateServerNoticesRoomHandler::class                   => fn () => new Command\Matrix\UpdateServerNoticesRoomHandler($container->get(MessengerInterface::class)->getBus('event.bus'), $container->get(MatrixConnector::class));
    yield Command\CreateDirectMatrixChatRoomHandler::class                       => fn () => new Command\CreateDirectMatrixChatRoomHandler($container->get(MessengerInterface::class)->getBus('command.bus'), $container->get(MessengerInterface::class)->getBus('event.bus'), $container->get(MatrixConnector::class), $container->get(RoomFactory::class), $modelLocator->get(\Matrix_Rooms_Model::class));
    yield Command\AddSampleOrderDocumentHandler::class                           => fn () => new Command\AddSampleOrderDocumentHandler($container->get(SampleOrdersService::class), $container->get(EpDocsClient::class), $container->get(ChatterInterface::class), $container->get(LibraryLocator::class));
    yield Command\JoinMatrixRoomHandler::class                                   => fn () => new Command\JoinMatrixRoomHandler($container->get(MatrixConnector::class));
    yield Command\LeaveMatrixRoomHandler::class                                  => fn () => new Command\LeaveMatrixRoomHandler($container->get(MatrixConnector::class));
    // Buyer Industry of Interest handlers
    yield Command\SaveBuyerIndustryOfInterestHandler::class                      => fn () => new Command\SaveBuyerIndustryOfInterestHandler($container->get(BuyerIndustryOfInterestService::class));
    yield Command\UpdateUserIdForBuyerIndustryStatsHandler::class                => fn () => new Command\UpdateUserIdForBuyerIndustryStatsHandler($container->get(BuyerIndustryOfInterestService::class));

    // Items Views Log
    yield Command\SaveViewItemsLogHandler::class                                 => fn () => new Command\SaveViewItemsLogHandler($modelLocator->get(\Items_Views_Model::class));

    // Matrix event handlers
    yield Event\ExportUserToMatrixAfterRegistration::class                       => fn () => new Event\ExportUserToMatrixAfterRegistration($container->get(MessengerInterface::class)->getBus('command.bus'));
    yield Event\BindResourceWhenMatrixChatRoomAdded::class                       => fn () => new Event\BindResourceWhenMatrixChatRoomAdded($container->get(ChatBindingService::class));
    yield Event\MatrixRoomsNotificationsSubscribers::class                       => fn () => new Event\MatrixRoomsNotificationsSubscribers($container->get(MatrixConnector::class), $container->get(ChatBindingService::class), $container->get(NotifierInterface::class), $container->get(ModelLocator::class));
    yield Event\CreateServiceRoomsAfterMatrixUserAdded::class                    => fn () => new Event\CreateServiceRoomsAfterMatrixUserAdded($container->get(MessengerInterface::class)->getBus('command.bus'), $container->get(MessengerInterface::class)->getBus('event.bus'), $container->get(MatrixConnector::class), $container->get(RoomFactory::class), $modelLocator->get(\Matrix_Users_Model::class), $modelLocator->get(\Matrix_Spaces_Model::class));
    yield Event\UpdatePresenceInMatrixWhenUserIsActive::class                    => fn () => new Event\UpdatePresenceInMatrixWhenUserIsActive($container->get(MatrixConnector::class));
    yield Event\SyncUserDataAfterProfileRoomWasCreated::class                    => fn () => new Event\SyncUserDataAfterProfileRoomWasCreated($container->get(MessengerInterface::class)->getBus('command.bus'));
    yield Event\ProcessUserDataInMatrixAfterAccountChanges::class                => fn () => new Event\ProcessUserDataInMatrixAfterAccountChanges($container->get(MessengerInterface::class)->getBus('command.bus'), $container->get(MatrixConnector::class));
    yield Event\UpdateUserMatrixAccountDataAfterProfileRoomCreated::class        => fn () => new Event\UpdateUserMatrixAccountDataAfterProfileRoomCreated($container->get(MatrixConnector::class));

    // Files
    yield Command\Media\CopyFileToStorageHandler::class                               => fn () => new Command\Media\CopyFileToStorageHandler($container->get(FilesystemProviderInterface::class));

    yield Command\Droplist\ReplaceImageHandler::class                               => fn () => new Command\Droplist\ReplaceImageHandler($container->get(FilesystemProviderInterface::class), $modelLocator->get(\Items_Droplist_Model::class));
};
