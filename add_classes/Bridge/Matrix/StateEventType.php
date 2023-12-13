<?php

declare(strict_types=1);

namespace App\Bridge\Matrix;

use ExportPortal\Enum\EnumCase;

/**
 * @method static self NAME()
 * @method static self TOPIC()
 * @method static self CREATE()
 * @method static self AVATAR()
 * @method static self JOIN_RULE()
 * @method static self ROOM_MEMBER()
 * @method static self GUEST_ACCESS()
 * @method static self POWER_LEVELS()
 * @method static self SPACE_PARENT()
 * @method static self SPACE_CHILD()
 * @method static self ENCRYPTION()
 * @method static self PINNED_EVENTS()
 * @method static self THIRD_PARTY_INVITE()
 * @method static self HISTORY_VISIBILITY()
 * @method static self CANONICAL_ALIAS()
 */
final class StateEventType extends EnumCase
{
    public const NAME = 'm.room.name';
    public const TOPIC = 'm.room.topic';
    public const CREATE = 'm.room.create';
    public const AVATAR = 'm.room.avatar';
    public const JOIN_RULE = 'm.room.join_rules';
    public const ROOM_MEMBER = 'm.room.member';
    public const GUEST_ACCESS = 'm.room.guest_access';
    public const POWER_LEVELS = 'm.room.power_levels';
    public const SPACE_PARENT = 'm.space.parent';
    public const SPACE_CHILD = 'm.space.child';
    public const ENCRYPTION = 'm.room.encryption';
    public const PINNED_EVENTS = 'm.room.pinned_events';
    public const THIRD_PARTY_INVITE = 'm.room.third_party_invite';
    public const HISTORY_VISIBILITY = 'm.room.history_visibility';
    public const CANONICAL_ALIAS = 'm.room.canonical_alias';
}
