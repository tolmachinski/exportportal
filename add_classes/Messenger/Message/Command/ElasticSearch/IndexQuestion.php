<?php

declare(strict_types=1);

namespace App\Messenger\Message\Command\ElasticSearch;

/**
 * Command that starts the indexing of the question.
 *
 * @author Anton Zencenco
 */
final class IndexQuestion
{
    /**
     * The question ID.
     */
    private int $questionId;

    /**
     * @param int $questionId the ID of the question
     */
    public function __construct(int $questionId)
    {
        $this->questionId = $questionId;
    }

    /**
     * Get the value of the question ID.
     */
    public function getQuestionId(): int
    {
        return $this->questionId;
    }

    /**
     * Set the value of the question ID.
     */
    public function setQuestionId(int $questionId): self
    {
        $this->questionId = $questionId;

        return $this;
    }
}
