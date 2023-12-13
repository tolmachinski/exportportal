<?php

namespace App\Documents\Versioning;

use DateTimeImmutable;

trait RejectedVersionTrait
{
    /**
     * The version rejection date.
     *
     * @var null|DateTimeImmutable
     */
    private $rejectionDate;

    /**
     * The rejection reason.
     *
     * @var null|string
     */
    private $reasonCode;

    /**
     * The rejection reason title.
     *
     * @var null|string
     */
    private $reasonTitle;

    /**
     * The rejection reason.
     *
     * @var null|string
     */
    private $reason;

    /**
     * Checks if version rejection date exists.
     */
    public function hasRejectionDate(): bool
    {
        return null !== $this->rejectionDate;
    }

    /**
     * Returns the date when version was rejected.
     */
    public function getRejectionDate(): ?DateTimeImmutable
    {
        return $this->rejectionDate;
    }

    /**
     * Returns an instance with the specified date of rejection.
     *
     * @return static
     */
    public function withRejectionDate(DateTimeImmutable $date)
    {
        $new = clone $this;
        $new->rejectionDate = $date;

        return $new;
    }

    /**
     * Return an instance without date of rejection.
     *
     * @return static
     */
    public function withoutRejectionDate()
    {
        $new = clone $this;
        $new->rejectionDate = null;

        return $new;
    }

    /**
     * Checks if rejection reason code exists.
     */
    public function hasReasonCode(): bool
    {
        return null !== $this->reasonCode;
    }

    /**
     * Returns the reason code of rejection.
     */
    public function getReasonCode(): ?string
    {
        return $this->reasonCode;
    }

    /**
     * Returns an instance with the specified rejection reason code.
     *
     * @return static
     */
    public function withReasonCode(string $reasonCode)
    {
        $new = clone $this;
        $new->reasonCode = !empty($reasonCode) ? (string) $reasonCode : null;

        return $new;
    }

    /**
     * Return an instance without rejection reason code.
     *
     * @return static
     */
    public function withoutReasonCode()
    {
        $new = clone $this;
        $new->reasonCode = null;

        return $new;
    }

    /**
     * Checks if the title of rejection reason exists.
     */
    public function hasReasonTitle(): bool
    {
        return null !== $this->reasonTitle;
    }

    /**
     * Returns the reason title of rejection.
     */
    public function getReasonTitle(): ?string
    {
        return $this->reasonTitle;
    }

    /**
     * Returns an instance with the specified rejection reason title.
     *
     * @return static
     */
    public function withReasonTitle(string $reasonTitle)
    {
        $new = clone $this;
        $new->reasonTitle = !empty($reasonTitle) ? (string) $reasonTitle : null;

        return $new;
    }

    /**
     * Return an instance without rejection reason title.
     *
     * @return static
     */
    public function withoutReasonTitle()
    {
        $new = clone $this;
        $new->reasonTitle = null;

        return $new;
    }

    /**
     * Checks if rejection reason exists.
     */
    public function hasReason(): bool
    {
        return null !== $this->reason;
    }

    /**
     * Returns the reason of rejection.
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * Returns an instance with the specified rejection reason.
     *
     * @return static
     */
    public function withReason(string $reason)
    {
        $new = clone $this;
        $new->reason = !empty($reason) ? (string) $reason : null;

        return $new;
    }

    /**
     * Return an instance without rejection reason.
     *
     * @return static
     */
    public function withoutReason()
    {
        $new = clone $this;
        $new->reason = null;

        return $new;
    }
}
