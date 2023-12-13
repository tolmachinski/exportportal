<?php

declare(strict_types=1);

namespace App\Messenger\Message\Command\ElasticSearch;

/**
 * Command that starts the indexing of the answer.
 *
 * @author Anton Zencenco
 */
final class IndexAnswer
{
    /**
     * The answer ID.
     */
    private int $answerId;

    /**
     * The question ID.
     */
    private int $questionId;

    /**
     * @param int $answerId the ID of the answer
     */
    public function __construct(int $answerId, int $questionId)
    {
        $this->answerId = $answerId;
        $this->questionId = $questionId;
    }

    /**
     * Get the value of the answer ID.
     */
    public function getAnswerId(): int
    {
        return $this->answerId;
    }

    /**
     * Set the value of the answer ID.
     */
    public function setAnswerId(int $answerId): self
    {
        $this->answerId = $answerId;

        return $this;
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
