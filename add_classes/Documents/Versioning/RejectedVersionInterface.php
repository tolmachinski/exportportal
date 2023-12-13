<?php

namespace App\Documents\Versioning;

use DateTimeImmutable;

interface RejectedVersionInterface
{
    /**
     * Checks if version rejection date exists.
     */
    public function hasRejectionDate(): bool;

    /**
     * Returns the date when version was rejected.
     */
    public function getRejectionDate(): ?DateTimeImmutable;

    /**
     * Returns an instance with the specified date of rejection.
     *
     * @return static
     */
    public function withRejectionDate(DateTimeImmutable $date);

    /**
     * Return an instance without date of rejection.
     *
     * @return static
     */
    public function withoutRejectionDate();

    /**
     * Checks if rejection reason code exists.
     */
    public function hasReasonCode(): bool;

    /**
     * Returns the reason code of rejection.
     */
    public function getReasonCode(): ?string;

    /**
     * Returns an instance with the specified rejection reason code.
     *
     * @return static
     */
    public function withReasonCode(string $reasonCode);

    /**
     * Return an instance without rejection reason code.
     *
     * @return static
     */
    public function withoutReasonCode();

    /**
     * Checks if the title of rejection reason exists.
     */
    public function hasReasonTitle(): bool;

    /**
     * Returns the reason title of rejection.
     */
    public function getReasonTitle(): ?string;

    /**
     * Returns an instance with the specified rejection reason title.
     *
     * @return static
     */
    public function withReasonTitle(string $reasonTitle);

    /**
     * Return an instance without rejection reason title.
     *
     * @return static
     */
    public function withoutReasonTitle();

    /**
     * Checks if rejection reason exists.
     */
    public function hasReason(): bool;

    /**
     * Returns the reason of rejection.
     */
    public function getReason(): ?string;

    /**
     * Returns an instance with the specified rejection reason.
     *
     * @return static
     */
    public function withReason(string $reason);

    /**
     * Return an instance without rejection reason.
     *
     * @return static
     */
    public function withoutReason();
}
