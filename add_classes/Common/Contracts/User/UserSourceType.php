<?php

declare(strict_types=1);

namespace App\Common\Contracts\User;

use ExportPortal\Enum\EnumCase;

/**
 * @author Anton Zencenco
 *
 * @method static self NONE()
 * @method static self USER()
 * @method static self OTHER()
 * @method static self EMAIL()
 * @method static self EVENT()
 * @method static self CAMPAIGN()
 * @method static self SOCIAL_MEDIA()
 * @method static self SEARCH_ENGINES()
 * @method static self OTHER_WEBSITES()
 * @method static self PRESS_RELEASES()
 * @method static self CA_IA_REFERRAL()
 */
final class UserSourceType extends EnumCase
{
    public const NONE = '';
    public const USER = 'user';
    public const OTHER = 'other';
    public const EMAIL = 'email';
    public const EVENT = 'event';
    public const CAMPAIGN = 'campaign';
    public const SOCIAL_MEDIA = 'social_media';
    public const SEARCH_ENGINES = 'search_engines';
    public const OTHER_WEBSITES = 'other_websites';
    public const PRESS_RELEASES = 'press_releases';
    public const CA_IA_REFERRAL = 'CA/IA Referral';
}
