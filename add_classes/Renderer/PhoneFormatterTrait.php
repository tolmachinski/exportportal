<?php

declare(strict_types=1);

namespace App\Renderer;

use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;

/**
 * @author Anton Zencenco
 */
trait PhoneFormatterTrait
{
    /**
     * Makes the phone number instance from the phone code and phone (if possible).
     */
    private function parseRawPhoneNumber(?string $phoneCode, ?string $phone): ?PhoneNumber
    {
        try {
            return PhoneNumberUtil::getInstance()->parse(trim($phoneCode . ' ' . $phone), PhoneNumberUtil::UNKNOWN_REGION);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
