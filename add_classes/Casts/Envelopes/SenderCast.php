<?php

declare(strict_types=1);

namespace App\Casts\Envelopes;

use App\Common\Database\AttributeCastInterface;
use App\Common\Database\Model;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class SenderCast implements AttributeCastInterface
{
    /**
     * {@inheritdoc}
     */
    public function get(AbstractPlatform $platform, Model $model, string $key, $sender, array $attributes = [])
    {
        if (null === $sender) {
            return null;
        }

        return [
            'id'         => $sender['idu'],
            'email'      => $sender['email'],
            'name'       => \trim($sender['fname'] . ' ' . $sender['lname']),
            'legal_name' => $sender['legal_name'] ?? null,
            'principal'  => $sender['id_principal'],
            'group'      => [
                'id'   => $sender['user_group'],
                'type' => $sender['user_type'],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function set(AbstractPlatform $platform, Model $model, string $key, $sender, array $attributes = [])
    {
        if (null === $sender || !\is_array($sender)) {
            return null;
        }

        return $sender['idu'] ?: null;
    }
}
