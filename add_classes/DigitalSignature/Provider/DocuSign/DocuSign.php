<?php

declare(strict_types=1);

namespace App\DigitalSignature\Provider\DocuSign;

use App\Common\DigitalSignature\Provider\ProviderInterface;
use App\Common\OAuth2\Client\ClientInterface as AuthClientInterface;
use App\Envelope\File\FileInterface;
use App\Envelope\File\ReferenceInterface;
use App\Envelope\RecipientTypes;
use DateTimeImmutable;
use DocuSign\eSign\Api\EnvelopesApi;
use DocuSign\eSign\Api\EnvelopesApi\GetDocumentOptions;
use DocuSign\eSign\Api\FoldersApi;
use DocuSign\eSign\Client\ApiClient;
use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model as DocuSignModels;
use Ramsey\Uuid\Uuid;
use SplFileObject;
use Symfony\Component\String\UnicodeString;

class DocuSign implements ProviderInterface
{
    use ApiAwareTrait;

    /**
     * List of supported languages for document certificate.
     */
    private array $supportedCertificateLanguages = [
        'zh_CN',
        'zh_TW',
        'nl',
        'en',
        'fr',
        'de',
        'it',
        'ja',
        'ko',
        'pt',
        'pt_BR',
        'ru',
        'es',
    ];

    /**
     * The default account ID value. Used if account ID is not provided.
     */
    private ?string $defaultAccountId;

    /**
     * The email subject template. If empty, document title will be used.
     */
    private ?string $emailSubjectTemplate;

    /**
     * The email message template. if empty, nothing will be used.
     */
    private ?string $emailMessageTemplate;

    public function __construct(
        ApiClient $apiClient,
        AuthClientInterface $authClient,
        ?string $defaultAccountId,
        ?string $emailSubjectTemplate,
        ?string $emailMessageTemplate
    ) {
        $this->apiClient = $apiClient;
        $this->authClient = $authClient;
        $this->defaultAccountId = $defaultAccountId;
        $this->emailSubjectTemplate = $emailSubjectTemplate;
        $this->emailMessageTemplate = $emailMessageTemplate;
    }

    /**
     * {@inheritdoc}
     *
     * @return DocuSignModels\Envelope
     */
    public function getEnvelope(?string $envelopeId, ?string $accountId = null, array $params = [])
    {
        if (null === $envelopeId) {
            return;
        }
        // Renew token
        $this->addAuthAccessTokenToApiClient();

        return $this->getEnvelopeApi()->getEnvelope(
            $accountId ?? $this->defaultAccountId,
            $envelopeId,
            (new EnvelopesApi\GetEnvelopeOptions())->setInclude($params['include'] ?? [])
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDocument(string $envelopeId, string $documentId, ?string $accountId = null, array $params = []): SplFileObject
    {
        // Renew token
        $this->addAuthAccessTokenToApiClient();
        // Get envelopes API
        $envelopesApi = $this->getEnvelopeApi();
        $params = \arrayCamelizeAssocKeys($params);
        $options = new GetDocumentOptions();

        // When set to 'false', the envelope signing certificate is removed from the download.
        if (isset($params['certificate'])) {
            if (\filter_var($params['certificate'] ?? false, \FILTER_VALIDATE_BOOL)) {
                $options->setCertificate('true');
            } else {
                $options->setCertificate('false');
            }
        }

        // When set to true, the account has the watermark feature enabled, and the envelope is not complete,
        // then the watermark for the account is added to the PDF documents. This option can remove the watermark.
        if (isset($params['watermark'])) {
            if (\filter_var($params['watermark'] ?? false, \FILTER_VALIDATE_BOOL)) {
                $options->setWatermark('true');
            } else {
                $options->setWatermark('false');
            }
        }

        // When set to 'true', the PDF bytes returned in the response are encrypted for all the key managers configured on your DocuSign account.
        if (isset($params['encrypt'])) {
            if (\filter_var($params['encrypt'] ?? false, \FILTER_VALIDATE_BOOL)) {
                $options->setEncrypt('true');
            } else {
                $options->setEncrypt('false');
            }
        }

        // When set to 'true', any changed fields for the returned PDF are highlighted in yellow and optional signatures or initials outlined in red.
        if (isset($params['showChanges']) && \filter_var($params['showChanges'] ?? false, \FILTER_VALIDATE_BOOL)) {
            $options->setShowChanges('true');
        }

        // When set to 'true', allows recipients to get documents by their user id.
        // Incomaptible with `recipientId`
        if (isset($params['documentsByUserId']) && \filter_var($params['documentsByUserId'] ?? false, \FILTER_VALIDATE_BOOL)) {
            $options->setDocumentsByUserid('true');
        }

        // Allows the sender to retrieve the documents as one of the recipients that they control.
        // Incomaptible with `documentsByUserId`.
        if (isset($params['recipientId'])) {
            $options->setRecipientId($params['recipientId']);
            $options->setDocumentsByUserid('false');
        }

        // The ID of a shared user that you want to impersonate in order to retrieve their view of the list of documents
        if (isset($params['sharedUserId'])) {
            $options->setSharedUserId($params['sharedUserId']);
        }

        // Specifies the language for the Certificate of Completion in the response.
        if (
            null !== ($language = $params['language'] ?? null)
            || !\in_array($language, $this->supportedCertificateLanguages)
        ) {
            $language = 'en';
        }
        $options->setLanguage($language);

        return $envelopesApi->getDocument(
            $accountId ?? $this->defaultAccountId,
            $documentId,
            $envelopeId,
            $options
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createEnvelope(array $draft, iterable $recipients, iterable $documents, ?string $accountId = null, array $params = []): string
    {
        // Renew token
        $this->addAuthAccessTokenToApiClient();
        // Get envelopes API
        $envelopesApi = $this->getEnvelopeApi();
        $envelopesApi->getApiClient();

        $envelopeDefinition = $this->makeEnvelopeDefinitionFromDraft($draft, $documents, $params);
        $envelopeDefinition->setRecipients($this->makeEnvelopeRecipientsFromRaw($recipients));
        $envelopeSummary = $envelopesApi->createEnvelope(
            $accountId ?? $this->defaultAccountId,
            $envelopeDefinition
        );

        return $envelopeSummary->getEnvelopeId();
    }

    /**
     * {@inheritdoc}
     */
    public function updateEnvelope(?string $envelopeId, array $draft, iterable $recipients, iterable $documents, ?string $accountId = null, array $params = []): ?string
    {
        // Renew token
        $this->addAuthAccessTokenToApiClient();
        // Get envelopes API
        $envelopesApi = $this->getEnvelopeApi();
        // Get original envelope
        $originalEnvelopeDefinition = $envelopesApi->getEnvelope(
            $accountId ?? $this->defaultAccountId,
            $envelopeId,
            (new EnvelopesApi\GetEnvelopeOptions())->setInclude([
                'recipients',
            ])
        );
        // Delete otiginal recipients
        $envelopesApi->deleteRecipients($accountId ?? $this->defaultAccountId, $envelopeId, $originalEnvelopeDefinition->getRecipients());

        $envelopeDefinition = $this->makeEnvelopeDefinitionFromDraft($draft, $documents, $params);
        $envelopeDefinition->setRecipients($this->makeEnvelopeRecipientsFromRaw($recipients));
        $envelopeSummary = $envelopesApi->update(
            $accountId ?? $this->defaultAccountId,
            $envelopeId,
            $envelopeDefinition,
            \tap(new EnvelopesApi\UpdateOptions(), function (EnvelopesApi\UpdateOptions $envelopeUpdateOptions) {
                $envelopeUpdateOptions->setAdvancedUpdate('true');
            })
        );

        return $envelopeSummary->getEnvelopeId();
    }

    /**
     * {@inheritdoc}
     */
    public function removeEnvelope(?string $envelopeId, ?string $deleteReason = null, ?string $accountId = null, array $params = []): void
    {
        if (null === $envelopeId) {
            return;
        }
        // Renew token
        $this->addAuthAccessTokenToApiClient();

        try {
            // Get envelopes API
            $envelopesApi = $this->getEnvelopeApi();
            $envelopeDefinition = new DocuSignModels\EnvelopeDefinition();
            $envelopeDefinition->setStatus('voided');
            $envelopeDefinition->setVoidedReason($deleteReason ?? 'This envelope was deleted by automatic means.');
            $envelopesApi->update(
                $accountId ?? $this->defaultAccountId,
                $envelopeId,
                $envelopeDefinition
            );
        } catch (ApiException $e) {
            if ('ENVELOPE_CANNOT_VOID_INVALID_STATE' === ($e->getResponseBody()->errorCode ?? null)) {
                $foldersApi = $this->getFoldersApi();
                $foldersResponse = $foldersApi->callList(
                    $accountId ?? $this->defaultAccountId,
                    \tap(new FoldersApi\ListOptions(), function (FoldersApi\ListOptions $options) {
                        $options->setInclude('envelope_folders');
                        $options->setUserFilter('owned_by_me');
                    })
                );
                /** @var DocuSignModels\Folder $binFodler */
                $binFodler = \current(
                    \array_filter($foldersResponse->getFolders(), fn (DocuSignModels\Folder $folder) => 'recyclebin' === $folder->getType())
                ) ?: null;
                if (null !== $binFodler) {
                    $foldersApi->moveEnvelopes(
                        $accountId ?? $this->defaultAccountId,
                        $binFodler->getFolderId(),
                        \tap(new DocuSignModels\FoldersRequest(), function (DocuSignModels\FoldersRequest $request) use ($envelopeId) {
                            $request->setEnvelopeIds([$envelopeId]);
                        })
                    );
                }

                return;
            }

            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvelopeEditReference(?string $envelopeId, ?string $returnUrl = null, ?string $accountId = null, array $params = []): ?string
    {
        if (null === $envelopeId) {
            return null;
        }
        // Renew token
        $this->addAuthAccessTokenToApiClient();

        return $this->getEnvelopeApi()
            ->createEditView(
                $accountId ?? $this->defaultAccountId,
                $envelopeId,
                new DocuSignModels\ReturnUrlRequest([
                    'return_url' => $returnUrl,
                ])
            )
            ->getUrl()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvelopeRecipientReference(?string $envelopeId, array $recipient, array $params = []): ?string
    {
        list('domain' => $domain, 'returnUrl' => $returnUrl, 'accountId' => $accountId) = $params;
        if (null === $envelopeId) {
            return null;
        }
        // Renew token
        $this->addAuthAccessTokenToApiClient();

        return $this->getEnvelopeApi()->createRecipientView(
            $accountId ?? $this->defaultAccountId,
            $envelopeId,
            \tap(
                new DocuSignModels\RecipientViewRequest(),
                function (DocuSignModels\RecipientViewRequest $request) use ($recipient, $returnUrl, $domain) {
                    $request->setReturnUrl($returnUrl);
                    $request->setAssertionId((string) Uuid::uuid6());
                    $request->setRecipientId((string) $recipient['uuid']);
                    $request->setClientUserId((string) $recipient['id_user']);
                    $request->setUserName((string) $recipient['full_name']);
                    $request->setEmail((string) $recipient['email']);
                    $request->setAuthenticationMethod('password');
                    $request->setAuthenticationInstant((new DateTimeImmutable())->format(DateTimeImmutable::RFC3339_EXTENDED));
                    $request->setSecurityDomain($domain);
                    $request->setXFrameOptions('same_origin');
                }
            )
        )->getUrl();
    }

    /**
     * Creates the DocuSign envelope definitin from the provided draft.
     *
     * @param iterable<FileInterface,ReferenceInterface> $documents
     */
    private function makeEnvelopeDefinitionFromDraft(array $draft, iterable $documents, array $params = []): DocuSignModels\EnvelopeDefinition
    {
        $envelopeDefinition = new DocuSignModels\EnvelopeDefinition();
        $envelopeDefinition->setStatus('created');
        $envelopeDefinition->setEmailSubject(\with($this->emailSubjectTemplate, function (?string $template) use ($draft) {
            $documentTitle = new UnicodeString($draft['display_title']);
            if (null === $template) {
                return $documentTitle->truncate(100, '...');
            }

            return (new UnicodeString($template))
                ->replace('{DOCUMENT_TITLE}', (string) $documentTitle->truncate(100 - \mb_strlen($template), '...'))
                ->replace('{DOCUMENT_TYPE}', $draft['display_type'])
                ->toString()
            ;
        }));
        if (!empty($this->emailMessageTemplate)) {
            $envelopeDefinition->setEmailBlurb(\with($this->emailMessageTemplate, function (string $template) use ($draft) {
                return (new UnicodeString($template))
                    ->replace('{DOCUMENT_TITLE}', $draft['display_title'])
                    ->replace('{DOCUMENT_TYPE}', $draft['display_type'])
                    ->toString()
                ;
            }));
        }

        if (isset($params['reassign'])) {
            $envelopeDefinition->setAllowReassign(\filter_var($params['reassign'], \FILTER_VALIDATE_BOOL) ? 'true' : 'false');
        }
        if (isset($params['history'])) {
            $envelopeDefinition->setAllowViewHistory(\filter_var($params['history'], \FILTER_VALIDATE_BOOL) ? 'true' : 'false');
        }

        $envelopeDefinition->setDocuments(
            iterator_to_array(
                \with(
                    $documents,
                    /** @param iterable<FileInterface,ReferenceInterface> $documents */
                    function (iterable $documents) {
                        foreach ($documents as $file => $token) {
                            yield (new DocuSignModels\Document())
                                ->setFileExtension($file->getExtension())
                                ->setRemoteUrl($token->getPath())
                                ->setDocumentId($file->getId())
                                ->setName($file->getOriginalName())
                            ;
                        }
                    }
                )
            )
        );

        return $envelopeDefinition;
    }

    /**
     * Makes the envelope recipients object from raw data.
     */
    private function makeEnvelopeRecipientsFromRaw(iterable $rawRecipients): DocuSignModels\Recipients
    {
        /**
         * @param DocuSignModels\CertifiedDelivery|DocuSignModels\Signer $envelopeRecipient
         */
        $makeRecipient = function ($envelopeRecipient, array $recipient) {
            $envelopeRecipient->setRecipientId((string) $recipient['uuid']);
            $envelopeRecipient->setClientUserId((string) $recipient['id_user']);
            $envelopeRecipient->setEmail(\cleanOutput($recipient['email']));
            $envelopeRecipient->setName(\cleanOutput($recipient['full_name']));
            $envelopeRecipient->setFirstName(\cleanOutput($recipient['first_name']));
            $envelopeRecipient->setLastName(\cleanOutput($recipient['last_name']));
            $envelopeRecipient->setFullName(\cleanOutput($recipient['full_name']));
            $envelopeRecipient->setRoutingOrder((string) $recipient['routing_order']);
            $envelopeRecipient->setEmbeddedRecipientStartUrl('SIGN_AT_DOCUSIGN');
            $envelopeRecipient->setCustomFields([
                (string) $recipient['id'],
                (string) $recipient['id_user'],
                (string) $recipient['uuid'],
            ]);
        };

        $signers = [];
        $viewers = [];
        foreach ($rawRecipients as $recipient) {
            if (RecipientTypes::OPERATOR === $recipient['type']) {
                continue;
            }

            if (RecipientTypes::SIGNER === $recipient['type']) {
                $signers[] = \tap(new DocuSignModels\Signer(), function (DocuSignModels\Signer $signer) use ($recipient, $makeRecipient) {
                    $makeRecipient($signer, $recipient);
                });

                continue;
            }

            if (RecipientTypes::VIEWER === $recipient['type']) {
                $viewers[] = \tap(new DocuSignModels\CertifiedDelivery(), function (DocuSignModels\CertifiedDelivery $viewer) use ($recipient, $makeRecipient) {
                    $makeRecipient($viewer, $recipient);
                });

                continue;
            }
        }
        $envelopeRecipients = new DocuSignModels\Recipients();
        $envelopeRecipients->setSigners($signers);
        $envelopeRecipients->setCertifiedDeliveries($viewers);

        return $envelopeRecipients;
    }

    /**
     * Renews access token in client.
     */
    private function addAuthAccessTokenToApiClient(): void
    {
        // In such way the fresh (kind of) API token will be set in the client configuration for later use.
        $this->apiClient->getConfig()->setAccessToken(
            $this->authClient->getAccessToken()
        );
    }
}
