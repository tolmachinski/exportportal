<?php

namespace App\Common\Traits;

use DateInterval;
use DateTimeImmutable;

trait DatatableRequestAwareTrait
{
    /**
     * Transforms the datatble date filter value into the string.
     */
    private function parametrifyFilterDateAsString(string $dateString, string $initialFormat, string $finalFormat = 'Y-m-d', ?DateInterval $interval = null): ?string
    {
        if (null === $dateTime = $this->parametrifyFilterDate($dateString, $initialFormat, $interval)) {
            return null;
        }

        return $dateTime->format($finalFormat);
    }

    /**
     * Transforms the datatble date filter value into the instance.
     */
    private function parametrifyFilterDate(string $dateString, string $initialFormat, DateInterval $interval = null): ?DateTimeImmutable
    {
        $dateTime = $this->parametrifyFilterDateTime($dateString, $initialFormat);
        if (null === $dateTime) {
            return null;
        }
        // Down with time!
        $dateTime = $dateTime->setTime(0, 0, 0, 0);

        return null === $interval ? $dateTime : $dateTime->add($interval);
    }

    /**
     * Transforms the datatble datetime filter value into the string.
     */
    private function parametrifyFilterDateTimeAsString(string $dateString, string $initialFormat, string $finalFormat = 'Y-m-d', ?DateInterval $interval = null): ?string
    {
        if (null === $dateTime = $this->parametrifyFilterDateTime($dateString, $initialFormat, $interval)) {
            return null;
        }

        return $dateTime->format($finalFormat);
    }

    /**
     * Transforms the datatble datetime filter value into the instance.
     */
    private function parametrifyFilterDateTime(string $dateString, string $initialFormat, DateInterval $interval = null): ?DateTimeImmutable
    {
        $dateTime = \DateTimeImmutable::createFromFormat($initialFormat, $dateString);
        if (!$dateTime) {
            $dateTime = \date_create_immutable($dateString);
        }
        if (!$dateTime) {
            return null;
        }

        return null === $interval ? $dateTime : $dateTime->add($interval);
    }
}
