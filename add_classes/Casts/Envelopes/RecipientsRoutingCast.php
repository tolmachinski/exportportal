<?php

declare(strict_types=1);

namespace App\Casts\Envelopes;

use App\Common\Database\AttributeCastInterface;
use App\Common\Database\Model;
use BadMethodCallException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class RecipientsRoutingCast implements AttributeCastInterface
{
    /**
     * {@inheritdoc}
     *
     * @param null|Collection $recipients
     */
    public function get(AbstractPlatform $platform, Model $model, string $key, $recipients, array $attributes = [])
    {
        if (null === $recipients) {
            $recipients = new ArrayCollection();
        }

        // Make the routing map
        $routing = (new ArrayCollection(\arrayByKey($recipients->toArray(), 'routing_order', true)))->map(
            fn ($group) => new ArrayCollection(\arrayByKey($group instanceof Collection ? $group->toArray() : $group, 'id_user'))
        );
        // Get current rooting order number
        $currentOrder = (int) $attributes['current_routing_order'] ?: null;
        // Get current routing by its order number
        $currentRouting = $routing->get($currentOrder) ?: null;
        // Get the next routing (if exists)
        /** @var null|Collection $nextRouting */
        $nextRouting = $routing->filter(fn ($group, $index) => $index > $currentOrder)->first() ?: null;
        // Get the previous routing (if exists)
        /** @var null|Collection $previousRouting */
        $previousRouting = $routing->filter(fn ($group, $index) => $index < $currentOrder)->first() ?: null;
        // Find first recipient in the routing
        $firstRecipient = $recipients->first() ?: null;
        // Find first recipient in the routing
        $lastRecipient = $recipients->last() ?: null;
        // Find first recipient in the next routing
        $nextRecipient = null !== $nextRouting ? ($nextRouting->first() ?: null) : null;
        // Find first recipient in the previous routing
        $previousRecipient = null !== $previousRouting ? ($previousRouting->first() ?: null) : null;
        // Organize all recipients in the groups
        $groups = (
            new ArrayCollection(
                \array_map(
                    fn ($group) => \arrayByKey($group, 'id'),
                    \arrayByKey($recipients->toArray(), 'type', true)
                )
            )
        )->map(fn (array $group) => new ArrayCollection($group));

        return \array_merge(
            [
                'recipients'             => $recipients,
                'next_routing_order'     => $nextRecipient['routing_order'] ?? null,
                'first_routing_order'    => $firstRecipient['routing_order'] ?? null,
                'last_routing_order'     => $lastRecipient['routing_order'] ?? null,
                'previous_routing_order' => $previousRecipient['routing_order'] ?? null,
                'current_routing_order'  => $currentOrder,
                'previous_routing'       => $previousRouting,
                'current_routing'        => $currentRouting,
                'next_routing'           => $nextRouting,
                'last_routing'           => $routing->last() ?: new ArrayCollection(),
                'routing'                => $routing,
                'groups'                 => $groups,
            ],
        );
    }

    /**
     * {@inheritdoc}
     */
    public function set(AbstractPlatform $platform, Model $model, string $key, $value, array $attributes = [])
    {
        throw new BadMethodCallException('This cast class is read-only.');
    }
}
