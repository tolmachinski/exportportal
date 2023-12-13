<?php

declare(strict_types=1);

namespace App\Bridge\Matrix\Message;

use App\Bridge\Matrix\StateEventType;
use ExportPortal\Matrix\Client\Model\StateEvent;
use ExportPortal\Matrix\Client\Model\ThirdPartyInvite;
use InvalidArgumentException;

/**
 * @author Anton Zencenco
 */
trait MessageOptionsTrait
{
    /**
     * Set the room topic.
     *
     * @return $this
     */
    public function topic(?string $topic): self
    {
        $this->options['topic'] = $topic;

        return $this;
    }

    /**
     * Get the room topic.
     */
    public function getTopic(): ?string
    {
        return $this->options['topic'] ?? null;
    }

    /**
     * Set the room preset.
     *
     * @return $this
     */
    public function preset(?string $preset): self
    {
        $this->options['preset'] = $preset;

        return $this;
    }

    /**
     * Get the room preset.
     */
    public function getPreset(): ?string
    {
        return $this->options['preset'] ?? null;
    }

    /**
     * Set the room alias.
     *
     * @return $this
     */
    public function alias(?string $alias): self
    {
        $this->options['alias'] = $alias;

        return $this;
    }

    /**
     * Get the room alias.
     */
    public function getAlias(): ?string
    {
        return $this->options['alias'] ?? null;
    }

    /**
     * Set the room version.
     *
     * @return $this
     */
    public function version(?string $version): self
    {
        $this->options['version'] = $version;

        return $this;
    }

    /**
     * Get the room version.
     */
    public function getVersion(): ?string
    {
        return $this->options['version'] ?? null;
    }

    /**
     * Set the room visibility.
     *
     * @return $this
     */
    public function visibility(?string $visibility): self
    {
        $this->options['visibility'] = $visibility;

        return $this;
    }

    /**
     * Get the room visibility.
     */
    public function getVisibility(): ?string
    {
        return $this->options['visibility'] ?? null;
    }

    /**
     * Set the room power levels.
     *
     * @return $this
     */
    public function powerLevels(?array $powerLevels): self
    {
        $this->options['powerLevels'] = $powerLevels;

        return $this;
    }

    /**
     * Get the room power levels.
     */
    public function getPowerLevels(): ?array
    {
        return $this->options['powerLevels'] ?? null;
    }

    /**
     * Set the initial room state.
     *
     * @param null|StateEvent[] $initialState
     *
     * @return $this
     */
    public function initialState(?array $initialState): self
    {
        if (null !== $initialState) {
            foreach ($initialState as $index => &$stateEvent) {
                if (\is_array($stateEvent)) {
                    $stateEvent = new StateEvent($stateEvent);
                }
                if (!$stateEvent instanceof StateEvent) {
                    throw new InvalidArgumentException(
                        \sprintf('The intitial state "%s" must be an instance of %s', $index, StateEvent::class)
                    );
                }
            }
        }
        $this->options['initialState'] = $initialState;

        return $this;
    }

    /**
     * Add one initial state.
     *
     * @return $this
     */
    public function addInitialState(StateEvent $state): self
    {
        if (!isset($this->options['initialState'])) {
            $this->options['initialState'] = [];
        }

        $this->options['initialState'][] = $state;

        return $this;
    }

    /**
     * Get the initial room state.
     *
     * @return null|StateEvent[]
     */
    public function getInitialState(): ?array
    {
        return $this->options['initialState'] ?? null;
    }

    /**
     * Set the room creation content.
     *
     * @return $this
     */
    public function creationContent(?array $creationContent): self
    {
        $this->options['creationContent'] = $creationContent;

        return $this;
    }

    /**
     * Get the room creation content.
     */
    public function getCreationContent(): ?array
    {
        return $this->options['creationContent'] ?? null;
    }

    /**
     * Set the invites to the room.
     *
     * @param null|string[] $invites
     *
     * @return $this
     */
    public function invites(?array $invites): self
    {
        $this->options['invites'] = $invites;

        return $this;
    }

    /**
     * Get the invites to the room.
     *
     * @return null|string[]
     */
    public function getInvites(): ?array
    {
        return $this->options['invites'] ?? null;
    }

    /**
     * Add one invite.
     *
     * @return $this
     */
    public function addInvite(string $invite): self
    {
        if (!isset($this->options['invites'])) {
            $this->options['invites'] = [];
        }

        $this->options['invites'][] = $invite;

        return $this;
    }

    /**
     * Set the third party invites to the room.
     *
     * @param null|ThirdPartyInvite[] $invites
     *
     * @return $this
     */
    public function thirdPartyInvites(?array $invites): self
    {
        if (null !== $invites) {
            foreach ($invites as $index => &$invite) {
                if (\is_array($invite)) {
                    $invite = new ThirdPartyInvite($invite);
                }
                if (!$invite instanceof ThirdPartyInvite) {
                    throw new InvalidArgumentException(
                        \sprintf('The third party invite "%s" must be an instance of %s', $index, ThirdPartyInvite::class)
                    );
                }
            }
        }
        $this->options['thirdPartyInvites'] = $invites;

        return $this;
    }

    /**
     * Get the third party invites to the room.
     *
     * @return null|ThirdPartyInvite[]
     */
    public function getThirdPartyInvites(): ?array
    {
        return $this->options['thirdPartyInvites'] ?? null;
    }

    /**
     * Add one third party invite.
     *
     * @return $this
     */
    public function addThirdPartyInvite(ThirdPartyInvite $invite): self
    {
        if (!isset($this->options['thirdPartyInvites'])) {
            $this->options['thirdPartyInvites'] = [];
        }

        $this->options['thirdPartyInvites'][] = $invite;

        return $this;
    }

    /**
     * Set the room parents.
     *
     * @param null|StateEvent[] $parents
     *
     * @return $this
     */
    public function parents(?array $parents): self
    {
        if (null !== $parents) {
            foreach ($parents as $index => &$parent) {
                if (\is_array($parent)) {
                    $parent = new StateEvent($parent);
                }
                if (!$parent instanceof StateEvent) {
                    throw new InvalidArgumentException(
                        \sprintf('The parent "%s" must be an instance of %s', $index, StateEvent::class)
                    );
                }
            }
        }
        $this->options['parents'] = $parents;

        return $this;
    }

    /**
     * Get the room parents.
     *
     * @return null|StateEvent[]
     */
    public function getParents(): ?array
    {
        return $this->options['parents'] ?? null;
    }

    /**
     * Add one parent.
     *
     * @return $this
     */
    public function addParent(string $parentId, ?bool $canonical = null, array $via = []): self
    {
        if (!isset($this->options['parents'])) {
            $this->options['parents'] = [];
        }
        if ($canonical) {
            foreach ($this->options['parents'] as $parent) {
                if ($parent['canonical'] ?? false) {
                    throw new InvalidArgumentException('Only one parent room can be canonical');
                }
            }
        }
        $this->options['parents'][] = (new StateEvent())
            ->setType(StateEventType::from(StateEventType::SPACE_PARENT)->value)
            ->setStateKey($parentId)
            ->setContent(
                array_filter(['canonical' => $canonical, 'via' => $via], fn ($v) => null !== $v)
            )
        ;

        return $this;
    }

    /**
     * Set the flag that indicates if service users must be invited.
     *
     * @return $this
     */
    public function inviteServiceUsers(bool $invite): self
    {
        $this->options['inviteServiceUsers'] = $invite;

        return $this;
    }

    /**
     * Get the flag that indicates if service users must be invited.
     */
    public function getInviteServiceUsers(): ?bool
    {
        return $this->options['inviteServiceUsers'] ?? null;
    }

    /**
     * Set the flag for room encryption.
     *
     * @return $this
     */
    public function encrypted(bool $encrypted): self
    {
        $this->options['encrypted'] = $encrypted;

        return $this;
    }

    /**
     * Determine if room is encrypted.
     */
    public function isEncrypted(): bool
    {
        return $this->options['encrypted'] ?? false;
    }
}
