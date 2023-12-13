<?php

declare(strict_types=1);

namespace App\Casts\Envelopes;

use App\Common\Database\AttributeCastInterface;
use App\Common\Database\Model;
use App\Envelope\HistoryEvent;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class HistoryEventCast implements AttributeCastInterface
{
    /**
     * {@inheritdoc}
     */
    public function get(AbstractPlatform $platform, Model $model, string $key, $value, array $attributes = [])
    {
        return HistoryEvent::fromStorageCode((string) $value);
    }

    /**
     * {@inheritdoc}
     */
    public function set(AbstractPlatform $platform, Model $model, string $key, $value, array $attributes = [])
    {
        return HistoryEvent::storageCode($value);
    }
}
