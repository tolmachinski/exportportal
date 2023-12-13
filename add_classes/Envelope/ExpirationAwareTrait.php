<?php

declare(strict_types=1);

namespace App\Envelope;

use DateTimeImmutable;

trait ExpirationAwareTrait
{
    /**
     * Gets the maximum expirataion data from the recipients.
     */
    protected function getMaxExpiringDate(array $recipients): ?DateTimeImmutable
    {
        $dates = array_map(function ($date) {
            return (!empty($date['expiresAt'])) ? DateTimeImmutable::createFromFormat('m/d/Y', $date['expiresAt']) : 0;
        }, $recipients);

        sort($dates);
        $dates = array_filter($dates);
        if (!empty($dates)) {
            return end($dates);
        }

        return null;
    }
}
