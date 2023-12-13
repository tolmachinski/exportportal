<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Event\ElasticSearch;

use App\Common\Database\Model;
use App\Messenger\Message\Command\ElasticSearch as ElasticSearchCommands;
use App\Messenger\Message\Event\Lifecycle as LifecycleEvents;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

/**
 * Re-indexes the user's questions and answers every time the profile request is accepted.
 *
 * @author Anton Zencenco
 */
final class ReIndexQuestionsAndAnswersSubscriber implements MessageSubscriberInterface
{
    /**
     * The questions model.
     */
    private Model $questionsRepository;

    /**
     * The answers model.
     */
    private Model $answersRepository;

    /**
     * The command bus.
     */
    private MessageBusInterface $commandBus;

    /**
     * @param MessageBusInterface $commandBus          the command bus
     * @param Model               $questionsRepository the questions model
     * @param Model               $questionsRepository the answers reposityro
     */
    public function __construct(MessageBusInterface $commandBus, Model $questionsRepository, Model $answersRepository)
    {
        $this->commandBus = $commandBus;
        $this->answersRepository = $answersRepository;
        $this->questionsRepository = $questionsRepository;
    }

    /**
     * Handles the event whenuser profile is updated.
     */
    public function onProfileUpdated(LifecycleEvents\UserUpdatedProfileEvent $message): void
    {
        $this->reIndexQuestions($message->getUserId());
        $this->reIndexAnswers($message->getUserId());
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield LifecycleEvents\UserUpdatedProfileEvent::class => ['bus' => 'event.bus', 'method' => 'onProfileUpdated'];
    }

    /**
     * Re-index the user's questions.
     */
    private function reIndexQuestions(int $userId): void
    {
        foreach ($this->getQuestionIds([$userId]) as $questionId) {
            $this->commandBus->dispatch(
                new ElasticSearchCommands\ReIndexQuestion($questionId),
                [new DelayStamp(3000), new AmqpStamp('elastic.question.index')]
            );
        }
    }

    /**
     * Re-index the user's answers.
     */
    private function reIndexAnswers(int $userId): void
    {
        foreach ($this->getAnswerIds([$userId]) as $answerId => $questionId) {
            $this->commandBus->dispatch(
                new ElasticSearchCommands\ReIndexAnswer($answerId, $questionId),
                [new DelayStamp(3000), new AmqpStamp('elastic.answer.index')]
            );
        }
    }

    /**
     * Get the questions iterator.
     *
     * @param int[] $userIds the list of users IDs
     */
    private function getQuestionIds(array $userIds): iterable
    {
        foreach ($this->questionsRepository->findAllBy(['scopes' => ['users' => $userIds]]) ?? [] as $question) {
            yield $question[$this->questionsRepository->getPrimaryKey()];
        }
    }

    /**
     * Get the answers iterator.
     *
     * @param int[] $userIds the list of users IDs
     */
    private function getAnswerIds(array $userIds): iterable
    {
        foreach ($this->answersRepository->findAllBy(['scopes' => ['users' => $userIds]]) ?? [] as $answer) {
            yield $answer[$this->answersRepository->getPrimaryKey()] => $answer['id_question'];
        }
    }
}
