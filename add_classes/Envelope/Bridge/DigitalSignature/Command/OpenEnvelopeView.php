<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\DigitalSignature\Command;

use App\Common\Database\Model;
use App\Common\DigitalSignature\Provider\ProviderResolverAwareTrait;
use App\Common\DigitalSignature\Provider\ProviderResolverInterface as SigningProviderResolverInterface;
use App\Common\Exceptions\AccessDeniedException;
use App\Common\Exceptions\NotFoundException;
use App\Envelope\Bridge\DigitalSignature\Message\OpenEnvelopeViewMessage;
use App\Envelope\EnvelopeStatuses;
use App\Envelope\Exception\EnvelopeException;
use App\Envelope\Exception\EnvelopeStatusException;
use App\Envelope\RecipientAccessTrait;
use App\Envelope\RecipientStatuses;
use App\Envelope\RecipientTypes;
use App\Envelope\SigningMecahisms;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class OpenEnvelopeView
{
    use RecipientAccessTrait;
    use ProviderResolverAwareTrait {
        ProviderResolverAwareTrait::setProviderResolver as setSigningProviderResolver;
        ProviderResolverAwareTrait::getProviderResolver as getSigningProviderResolver;
    }

    /**
     * The envelopes repository.
     */
    private Model $envelopesRepository;

    /**
     * Create instance of the command.
     */
    public function __construct(Model $envelopesRepository, SigningProviderResolverInterface $signingProviderResolver)
    {
        $this->envelopesRepository = $envelopesRepository;
        $this->setSigningProviderResolver($signingProviderResolver);
    }

    /**
     * Execute the command.
     */
    public function __invoke(OpenEnvelopeViewMessage $message)
    {
        $userId = $message->getUserId();
        /** @var array $envelope */
        $envelope = $this->getEnvelopeFromStorage($message->getEnvelopeId());
        if (in_array($envelope['status'], [...EnvelopeStatuses::PENDING, ...EnvelopeStatuses::FINISHED])) {
            throw new EnvelopeStatusException(
                \sprintf(
                    'The view operation on cannot be performed on envelope at the status "%s".',
                    $envelope['status']
                )
            );
        }

        /** @var null|Collection $currentRouting */
        $currentRouting = $envelope['recipients_routing']['current_routing'] ?? new ArrayCollection();
        /** @var null|array $currentRecipient */
        $currentRecipient = $currentRouting->filter(fn (array $recipient) => $userId === (int) $recipient['id_user'])->first() ?: null;

        //region Access
        $this->assertSenderIsCurrentRecipient($userId, $currentRouting);
        if (!\in_array($currentRecipient['type'], [RecipientTypes::SIGNER, RecipientTypes::VIEWER])) {
            throw new AccessDeniedException(
                \sprintf('The recipient of the type "%s" cannot perform this operation.', $currentRecipient['type'])
            );
        }
        if (!\in_array($currentRecipient['status'], [RecipientStatuses::SENT, RecipientStatuses::DELIVERED])) {
            throw new AccessDeniedException('The recipient in this status cannot perfoem this operation.');
        }
        //endregion Access

        $signingProvider = $this->getSigningProviderResolver()->resolve($envelope['signing_mechanism'] ?? SigningMecahisms::NATIVE);
        if (null === $signingProvider) {
            return null;
        }

        if (!empty($returnUrl = $message->getReturnUrl())) {
            $separator = null === parse_url($returnUrl, PHP_URL_QUERY) ? '?' : '&';
            $returnUrl = \sprintf('%s%s%s', $message->getReturnUrl(), $separator, \http_build_query(\arrayCamelizeAssocKeys([
                'original_envelope_id' => (string) $envelope['uuid'],
            ])));
        }

        try {
            if (null === $envelopeId = $envelope['remote_envelope'] ?? null) {
                return null;
            }

            return $signingProvider->getEnvelopeRecipientReference(
                (string) $envelopeId,
                $currentRecipient,
                [
                    'domain'    => $message->getDomain(),
                    'returnUrl' => $returnUrl,
                ],
            );
        } catch (\Throwable $e) {
            throw new EnvelopeException('Failed to get the access to the envelope in preview mode.', 0, $e);
        }
    }

    /**
     * Finds envelope in the repository.
     *
     * @throws NotFoundException if envelope is not found
     */
    private function getEnvelopeFromStorage(?int $envelopeId): array
    {
        if (
            null === $envelopeId
            || null === $envelope = $this->envelopesRepository->findOne($envelopeId, ['with' => ['extended_recipients as recipients_routing']])
        ) {
            throw new NotFoundException(\sprintf('The envelope with ID %s is not found', \varToString($envelopeId)));
        }

        return $envelope;
    }
}
