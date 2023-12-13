<?php

declare(strict_types=1);

namespace App\Common\Contracts\Notifier;

use ExportPortal\Enum\EnumCase;

/**
 * @author Anton Zencenco
 *
 * @method static self STORAGE()
 * @method static self MATRIX()
 */
final class SystemChannel extends EnumCase
{
    public const STORAGE = 'storage/legacy';
    public const MATRIX = 'chat/matrix';

    /**
     * Get the channel label.
     */
    public function label(): string
    {
        return $this->value;
    }

    /**
     * Get all labels for channels.
     */
    public static function labels(): array
    {
        return \array_map(fn (SystemChannel $channel) => $channel->label(), static::cases());
    }
}
