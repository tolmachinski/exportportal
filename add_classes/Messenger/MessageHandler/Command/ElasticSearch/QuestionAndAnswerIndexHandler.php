<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Command\ElasticSearch;

use App\Messenger\Message\Command\ElasticSearch;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * Indexes the questions and answer in the ElasticSearch.
 */
final class QuestionAndAnswerIndexHandler implements MessageSubscriberInterface
{
    /**
     * The elastic questions/answer repository.
     */
    private \Elasticsearch_Questions_Model $elasticRepository;

    /**
     * @param \Elasticsearch_Questions_Model $elasticRepository the elastic questions/answer repository
     */
    public function __construct(\Elasticsearch_Questions_Model $elasticRepository)
    {
        $this->elasticRepository = $elasticRepository;
    }

    /**
     * Indexes the answer by its ID.
     */
    public function indexAnswer(ElasticSearch\IndexAnswer $message): void
    {
        $this->elasticRepository->indexAnswer($message->getAnswerId(), $message->getQuestionId(), 'add');
    }

    /**
     * Indexes the answer by its ID.
     */
    public function reIndexAnswer(ElasticSearch\ReIndexAnswer $message): void
    {
        $this->elasticRepository->indexAnswer($message->getAnswerId(), $message->getQuestionId(), 'update');
    }

    /**
     * Indexes the question by its ID.
     */
    public function indexQuestion(ElasticSearch\IndexQuestion $message): void
    {
        $this->elasticRepository->index($message->getQuestionId());
    }

    /**
     * Indexes the question by its ID.
     */
    public function reIndexQuestion(ElasticSearch\ReIndexQuestion $message): void
    {
        $this->elasticRepository->updateQuestion($message->getQuestionId());
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield ElasticSearch\IndexAnswer::class => ['bus' => 'command.bus', 'method' => 'indexAnswer'];
        yield ElasticSearch\IndexQuestion::class => ['bus' => 'command.bus', 'method' => 'indexQuestion'];
        yield ElasticSearch\ReIndexAnswer::class => ['bus' => 'command.bus', 'method' => 'reIndexAnswer'];
        yield ElasticSearch\ReIndexQuestion::class => ['bus' => 'command.bus', 'method' => 'reIndexQuestion'];
    }
}
