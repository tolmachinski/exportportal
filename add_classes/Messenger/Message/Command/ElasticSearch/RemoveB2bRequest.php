<?php

declare(strict_types=1);

namespace App\Messenger\Message\Command\ElasticSearch;

/**
 * Command that starts the removing of the B2B request.
 *
 * @author Anton Zencenco
 */
final class RemoveB2bRequest
{
    /**
     * The B2B request ID.
     */
    private int $requestId;

    /**
     * @param int $requestId the ID of the B2B request
     */
    public function __construct(int $requestId)
    {
        $this->requestId = $requestId;
    }

    /**
     * Get the value of the B2B request ID.
     */
    public function getRequestId(): int
    {
        return $this->requestId;
    }

    /**
     * Set the value of the B2B request ID.
     */
    public function setRequestId(int $requestId): self
    {
        $this->requestId = $requestId;

        return $this;
    }
}
