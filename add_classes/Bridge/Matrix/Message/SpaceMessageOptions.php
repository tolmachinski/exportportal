<?php

declare(strict_types=1);

namespace App\Bridge\Matrix\Message;

use App\Bridge\Matrix\StateEventType;
use ExportPortal\Contracts\Chat\Recource\ResourceOptionsInterface;
use ExportPortal\Matrix\Client\Model\StateEvent;
use InvalidArgumentException;

/**
 * @author Anton Zencenco
 */
final class SpaceMessageOptions extends RoomMessageOptions
{
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        if (isset($options['parents'])) {
            $this->parents($options['parents']);
        }
        if (isset($options['childRooms'])) {
            $this->childRooms($options['childRooms']);
        }
        if (isset($options['childSpaces'])) {
            $this->childSpaces($options['childSpaces']);
        }
        if (isset($options['initialState'])) {
            $this->initialState($options['initialState']);
        }
        if (isset($options['thirdPartyInvites'])) {
            $this->thirdPartyInvites($options['thirdPartyInvites']);
        }

        $this->inviteServiceUsers($options['inviteServiceUsers'] ?? true);
    }

    /**
     * {@inheritDoc}
     */
    public function recipientId(?string $sender): self
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getRecipientId(): ?string
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function resource($resource): self
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getResource(): ?ResourceOptionsInterface
    {
        return null;
    }

    /**
     * Set the child rooms.
     *
     * @param null|StateEvent[] $childRooms
     *
     * @return $this
     */
    public function childRooms(?array $childRooms): self
    {
        if (null !== $childRooms) {
            foreach ($childRooms as $index => &$child) {
                if (\is_array($child)) {
                    $child = new StateEvent($child);
                }
                if (!$child instanceof StateEvent) {
                    throw new InvalidArgumentException(
                        \sprintf('The child room "%s" must be an instance of %s', $index, StateEvent::class)
                    );
                }
            }
        }
        $this->options['childRooms'] = $childRooms;

        return $this;
    }

    /**
     * Get the child rooms.
     *
     * @return null|StateEvent[]
     */
    public function getChildRooms(): ?array
    {
        return $this->options['childRooms'] ?? null;
    }

    /**
     * Add one child.
     */
    public function addChildRoom(string $childId, ?string $order = null, ?bool $suggested = null, ?bool $autoJoin = null, array $via = []): self
    {
        if (!isset($this->options['childRooms'])) {
            $this->options['childRooms'] = [];
        }
        $this->options['childRooms'][] = (new StateEvent())
            ->setType(StateEventType::from(StateEventType::SPACE_CHILD)->value)
            ->setStateKey($childId)
            ->setContent(
                \array_filter(
                    ['suggested' => $suggested, 'auto_join' => $autoJoin, 'via' => $via, 'order' => $order],
                    fn ($v) => null !== $v
                )
            )
        ;

        return $this;
    }

    /**
     * Set the child spaces.
     *
     * @param null|StateEvent[] $childSpaces
     *
     * @return $this
     */
    public function childSpaces(?array $childSpaces): self
    {
        if (null !== $childSpaces) {
            foreach ($childSpaces as $index => &$child) {
                if (\is_array($child)) {
                    $child = new StateEvent($child);
                }
                if (!$child instanceof StateEvent) {
                    throw new InvalidArgumentException(
                        \sprintf('The child space "%s" must be an instance of %s', $index, StateEvent::class)
                    );
                }
            }
        }
        $this->options['childSpaces'] = $childSpaces;

        return $this;
    }

    /**
     * Get the child spaces.
     *
     * @return null|StateEvent[]
     */
    public function getChildSpaces(): ?array
    {
        return $this->options['childSpaces'] ?? null;
    }

    /**
     * Add one child.
     */
    public function addChildSpace(string $childId, ?string $order = null, ?bool $suggested = null, ?bool $autoJoin = null, array $via = []): self
    {
        if (!isset($this->options['childSpaces'])) {
            $this->options['childSpaces'] = [];
        }
        $this->options['childSpaces'][] = (new StateEvent())
            ->setType(StateEventType::from(StateEventType::SPACE_CHILD)->value)
            ->setStateKey($childId)
            ->setContent(
                \array_filter(
                    ['suggested' => $suggested, 'auto_join' => $autoJoin, 'via' => $via, 'order' => $order],
                    fn ($v) => null !== $v
                )
            )
        ;

        return $this;
    }
}
