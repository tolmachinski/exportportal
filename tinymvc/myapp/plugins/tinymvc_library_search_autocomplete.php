<?php

use App\Common\Autocomplete\LazyCollection;
use App\Common\Exceptions\AccessDeniedException;
use App\Common\Exceptions\OwnershipException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ramsey\Uuid\UuidFactory;
use Symfony\Component\HttpFoundation\Request;

use const App\Common\Autocomplete\CONFIG_PATH;
use const App\Common\Autocomplete\RECORDS_PER_TYPE;
use const App\Common\Autocomplete\TYPES;
use const App\Common\Autocomplete\VERSION;

/**
 * Library Search_Autocomplete.
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [03.12.2021]
 * library refactoring: code style
 */
class TinyMVC_Library_Search_Autocomplete
{
    private const HASH_ALGO = 'sha512';
    private const COOKIE_KEY = '_ep_cmpl';
    private const COOKIE_TTL = 60 * 60 * 24 * 365 * 50;
    private const USER_REF_TTL = 60 * 60 * 24 * 365 * 50;
    private const USER_REF_COOKIE = '_ep_ucmpl';
    private const USER_TRACK_COOKIE = 'ANALITICS_CT_SUID';

    /**
     * The name of the current host.
     *
     * @var string
     */
    private $currentHost;

    /**
     * The name of the text host.
     *
     * @var string
     */
    private $textHost;

    /**
     * The user's ID value.
     *
     * @var null|int
     */
    private $userId;

    /**
     * The user's reference.
     *
     * @var null|string
     */
    private $userRef;

    /**
     * The collection of the user's records.
     *
     * @var Collection
     */
    private $records;

    /**
     * The records' repository.
     *
     * @var Search_Autocomplete_Model
     */
    private $repository;

    /**
     * The metadata that contols renewal mode.
     *
     * @var array
     */
    private $renewalMetadata;

    /**
     * The base domain name.
     *
     * @var string
     */
    private $baseDomain;

    /**
     * The list of subdomains.
     *
     * @var array
     */
    private $subdomains = array();

    /**
     * The flag that determines if the records list is intialized.
     *
     * @var bool
     */
    private $initialized = false;

    /**
     * The user ref cookie TTL.
     */
    private $userTrackingTtl;

    /**
     * The key of the cookie that contains user reference.
     *
     * @var string
     */
    private $userReferenceKey;

    /**
     * The flag that indicates if the user reference has been regenerated.
     *
     * @var bool
     */
    private $regeneratedUserReference = false;

    /**
     * The key of the cookie that contains user tracking code.
     *
     * @var string
     */
    private $userTrackingKey;

    /**
     * The base cookie key for autocomplete.
     *
     * @var string
     */
    private $cookieKey;

    /**
     * The autocomplete cookie TTL.
     *
     * @var int
     */
    private $cookieTtl;

    /**
     * The name of the hash algorithm used to create autocomplete hash.
     *
     * @var string
     */
    private $hashAlgorithm;

    /**
     * Library Search_Autocomplete constructor.
     */
    public function __construct()
    {
        $config = $this->loadConfig();

        $this->userReferenceKey = $config['user_ref_cookie'] ?? static::USER_REF_COOKIE;
        $this->userTrackingKey = $config['user_track_cookie'] ?? static::USER_TRACK_COOKIE;
        $this->userTrackingTtl = $config['user_ref_ttl'] ?? static::USER_REF_TTL;
        $this->hashAlgorithm = $config['has_algo'] ?? static::HASH_ALGO;
        $this->currentHost = request()->getHost();
        $this->repository = model(Search_Autocomplete_Model::class);
        $this->cookieTtl = $config['cookie_ttl'] ?? static::COOKIE_TTL;
        $this->cookieKey = $config['cookie_key'] ?? static::COOKIE_KEY;
        $this->textHost = $this->currentHost;
        $this->userRef = $this->resolveUserReference();
        $this->userId = $this->resolveUserId();
        $this->records = new LazyCollection($this->repository, $this->userRef);
        $this->baseDomain = config('env.WWW_SUBDOMAIN', '') . __HTTP_HOST_ORIGIN;

        $this->addSubdomainHosts($config['url_mapping'] ?? array());
    }

    /**
     * Check if autocomplete records must be renewed.
     */
    public function isRenewalRequired(?int $type = null): bool
    {
        $renewalMetadata = $this->getRenewalMetadata($type);
        if ($this->isSubdomainHost()) {
            return (bool) ($renewalMetadata['refreshSubDomains'][$this->getSubdomainAbbreviation($this->currentHost)] ?? 0);
        }

        return (bool) ($renewalMetadata['refreshMainDomain'] ?? 0);
    }

    /**
     * Check if current is a subdomain.
     */
    public function isSubdomainHost(): bool
    {
        return $this->baseDomain !== $this->currentHost;
    }

    /**
     * Check if host providing autocomplete is a subdomain.
     */
    public function isSubdomainTextHost(): bool
    {
        return $this->baseDomain !== $this->textHost;
    }

    /**
     * Determine if the records list is intialized.
     */
    public function isInitialized(): bool
    {
        return $this->initialized;
    }

    /**
     * Set the host from where the processing text came from.
     */
    public function setTextHost(string $host): void
    {
        $this->textHost = $host;
    }

    /**
     * Set the text hostfrom referer.
     */
    public function setTextHostFromReferer(Request $request): void
    {
        $referer = $request->server->get('HTTP_REFERER') ?? null;
        if (
            null !== $referer
            && null !== ($referer_host = parse_url($referer, PHP_URL_HOST) ?: null)
            && endsWith($referer_host, request()->getHost())
        ) {
            $this->setTextHost((string) $referer_host);
        }
    }

    /**
     * Add many subdomains.
     */
    public function addSubdomainHosts(array $subdomains): void
    {
        foreach ($subdomains as $abbreviation => $subdomain) {
            $host = parse_url($subdomain, PHP_URL_HOST) ?: null;
            if (null === $host) {
                continue;
            }

            $this->addSubdomainHost($abbreviation, (string) $host);
        }
    }

    /**
     * Adds one subdonain to the list.
     */
    public function addSubdomainHost(string $abbreviation, string $subdomain): void
    {
        if (isset($this->subdomains["_{$abbreviation}"])) {
            throw new DomainException('The subdomain with such name already exists');
        }

        $this->subdomains["_{$abbreviation}"] = $subdomain;
    }

    /**
     * Get subdomain abbreviation.
     */
    public function getSubdomainAbbreviation(string $subdomain): ?string
    {
        return array_search($subdomain, $this->subdomains, true) ?: null;
    }

    /**
     * Clears all known subdomains.
     */
    public function clearSubdomainHosts(): void
    {
        $this->subdomains = array();
    }

    /**
     * Handle the search request.
     */
    public function handleSearchRequest(Request $request, ?string $searchQueryKey, ?int $type): void
    {
        $keywords = decodeUrlString($request->get($searchQueryKey ?? 'q'));
        if (empty($keywords)) {
            return;
        }

        $this->setTextHostFromReferer($request);
        $this->processText($keywords, $type);
    }

    /**
     * Processes text for autocompletion.
     */
    public function processText(string $text, ?int $type = null): void
    {
        if ($record = $this->findRecordIfTextExists($text, $type)) {
            $this->repository->hit($record['id']);
            $this->records->removeElement($record);
            $this->reformStoredRecords($record);
        } else {
            if (empty($this->userRef)) {
                return;
            }

            if ($id = $this->repository->write($text, $type, $this->userRef, $this->userId, $token)) {
                $this->reformStoredRecords(
                    array(
                        'id'    => (int) $id,
                        'type'  => $type,
                        'text'  => $text,
                        'token' => $token,
                        'user'  => $this->userId,
                        'ref'   => $this->userRef,
                        'new'   => true,
                    )
                );
            }

            $this->writeRenewalInformation($type);
        }
    }

    /**
     * Removes the record from autocomplete.
     */
    public function removeAutocompleteRecord(?string $deleteToken, ?string $userRef): void
    {
        if (null === $deleteToken) {
            throw new AccessDeniedException('The delete token of the autocomplete record is required.');
        }
        if (empty($userRef)) {
            throw new AccessDeniedException('The user must be specified.');
        }
        if (!empty($userRef) && $userRef !== $this->userRef) {
            throw new OwnershipException('User can delete only his own autocomplete records');
        }

        $record = with(
            $this->repository->findOneBy(array(
                'columns'    => array('id', 'id_user AS user', 'user_ref as ref', 'type', 'text', 'token'),
                'conditions' => array(
                    'token'    => $deleteToken,
                    'user_ref' => $userRef,
                ),
            )),
            function (?array $record) {
                if (null !== $record) {
                    $record['new'] = false;
                }

                return $record;
            }
        );
        if (null === $record) {
            return;
        }

        $this->repository->deleteOne($record['id']);
        if ($this->isInitialized()) {
            $this->getRecords()->removeElement($record);
        }
        $this->writeRenewalInformation((int) $record['type'] ?: null);
    }

    /**
     * Get the list of raw records for given type (if any).
     */
    public function getRecords(?int $type = null): Collection
    {
        $this->initialized = true;

        if (null === $type) {
            return new ArrayCollection(
                iterator_to_array($this->records->getIterator())
            );
        }

        return $this->records->filter(function (array $record) use ($type) {
            return $type === ((int) $record['type'] ?: null);
        });
    }

    /**
     * Get the hash of the autocomplete records for user.
     */
    public function getRecordsHash(?int $type = null): string
    {
        return hash(
            $this->hashAlgorithm,
            json_encode(
                $this->getRecords($type)
                    ->map(function (array $record) {
                        return array(
                            $record['id'] ?? null,
                            $record['text'] ?? null,
                            $record['ref'] ?? null,
                            (int) $record['user'] ?: null,
                            (int) $record['type'] ?: null,
                        );
                    })
                    ->getValues()
            )
        );
    }

    /**
     * Get the list f autocomplete records for given type.
     */
    public function getAutocompleteRecords(?int $type = null, bool $onlyNew = false): Collection
    {
        $records = $this->getRecords($type);
        if ($onlyNew) {
            $records = $records->filter(function (array $record) { return $record['new']; });
        }

        return $this->prepareRecordsForAutocomplete($records);
    }

    /**
     * Get renewal metadata for given type.
     */
    public function getRenewalMetadata(?int $type = null): array
    {
        if (null === ($this->renewalMetadata[$type ?? '_default'] ?? null)) {
            $this->resolveRenewalMetadata($type);
        }

        return $this->renewalMetadata[$type ?? '_default'];
    }

    /**
     * Write information into cookies that the autocomplete records are renewed.
     */
    public function finishRenewal(?int $type = null): void
    {
        $renewalMetadata = $this->getRenewalMetadata($type);
        $hostAbbreviation = $this->getSubdomainAbbreviation($this->currentHost);
        $refreshMainDomain = !$this->isSubdomainHost() ? 0 : (int) $renewalMetadata['refreshMainDomain'] ?? 0;
        $refreshSubDomains = array();
        foreach ($renewalMetadata['refreshSubDomains'] as $abbr => $state) {
            $refreshSubDomains[$abbr] = $this->isSubdomainHost() && $abbr === $hostAbbreviation ? 0 : (int) $state ?? 0;
        }
        if (null === ($hash = $renewalMetadata['hash'] ?? null)) {
            $hash = $this->getRecordsHash();
        }

        $this->updateMetadataCookie($type, $hash, $refreshMainDomain, $refreshSubDomains);
        $this->updateRenewalMetadata($type, $hash, $refreshMainDomain, $refreshSubDomains);
    }

    /**
     * Write information into cookies that will be used for stored values renewal.
     */
    public function writeRenewalInformation(?int $type = null, bool $renewAll = false): void
    {
        $renewAll = false;
        $renewalMetadata = $this->getRenewalMetadata($type);
        if (null === ($renewalMetadata['hash'] ?? null)) {
            $renewAll = true;
        }
        $hash = $this->getRecordsHash();
        $refreshMainDomain = $this->isSubdomainTextHost() || $renewAll ? 1 : 0;
        $hostAbbreviation = $this->getSubdomainAbbreviation($this->currentHost);
        $refreshSubDomains = array();
        foreach ($renewalMetadata['refreshSubDomains'] as $abbr => $state) {
            if ($renewAll) {
                $refreshSubDomains[$abbr] = 1;

                continue;
            }

            $refreshSubDomains[$abbr] = !$this->isSubdomainTextHost() || $abbr !== $hostAbbreviation ? 1 : 0;
        }

        $this->updateMetadataCookie($type, $hash, $refreshMainDomain, $refreshSubDomains);
        $this->updateRenewalMetadata($type, $hash, $refreshMainDomain, $refreshSubDomains);
    }

    /**
     * Prepare records to be stored as autocomplete entities.
     */
    private function prepareRecordsForAutocomplete(Collection $records): Collection
    {
        return $records->map(function (array $record) {
            return array(
                $record['text'],
                VERSION,
                array((int) $record['type']),
                array('dl' => getUrlForGroup("/autocomplete/remove?deltok={$record['token']}&ref={$record['ref']}")),
            );
        });
    }

    /**
     * Find record ID if text is already stored in the list.
     */
    private function findRecordIfTextExists(string $text, ?int $type = null): ?array
    {
        return $this->getRecords($type)
            ->filter(function (array $record) use ($text, $type) {
                if (null === $type) {
                    return ($record['text'] ?? '') === $text;
                }

                return ($record['text'] ?? '') === $text && ($record['type'] ?? null) === $type;
            })
            ->first() ?: null
        ;
    }

    /**
     * Reforms the stored values.
     */
    private function reformStoredRecords(array $newRecord): void
    {
        $this->records = new ArrayCollection(array_merge(array($newRecord), $this->records->getValues()));
        // Remove excess records
        foreach ($this->getRecords($newRecord['type'] ?? null)->slice(RECORDS_PER_TYPE) as $record) {
            $this->records->removeElement($record);
        }
    }

    /**
     * Resolve the renewal metadata.
     */
    private function resolveRenewalMetadata(?int $type): void
    {
        $tokenKey = $this->makeTokenKey($type ?? '_default');
        $subdomainsRenewalMetadata = array_fill_keys(array_keys($this->subdomains), 1);

        try {
            list('_h' => $hash, '_r' => $refreshMainDomain, '_rd' => $refreshSubDomains) = json_decode(
                cookies()->getCookieParam($tokenKey, null) ?: null,
                true,
                512,
                JSON_THROW_ON_ERROR
            ) ?? array();
        } catch (JsonException $exception) {
            $hash = $this->getRecordsHash($type);
            $refreshMainDomain = 1;
            $refreshSubDomains = array();
        }

        $this->updateRenewalMetadata(
            $type,
            $hash,
            (int) $refreshMainDomain ?: 0,
            array_merge($subdomainsRenewalMetadata, $refreshSubDomains ?? array())
        );
    }

    /**
     * Updates the renewal metadata record for given type (if any).
     */
    private function updateRenewalMetadata(?int $type, ?string $hash, int $refreshMainDomain, array $refreshSubDomains): void
    {
        $this->renewalMetadata[$type ?? '_default'] = compact('hash', 'refreshMainDomain', 'refreshSubDomains');
    }

    /**
     * Update metadata cookie record.
     */
    private function updateMetadataCookie(?int $type, ?string $hash, int $refreshMainDomain, array $refreshSubDomains): void
    {
        // Make key
        $tokenKey = $this->makeTokenKey($type = $type ?? '_default');

        // Renew cookies
        unset($_COOKIE[$tokenKey]);
        setcookie(
            $tokenKey,
            json_encode(array('_h' => $hash, '_r' => $refreshMainDomain, '_rd' => $refreshSubDomains)),
            array(
                'expires'  => time() + $this->cookieTtl,
                'path'     => '/',
                'domain'   => __JS_COOKIE_DOMAIN,
                'secure'   => false,
                'httponly' => false,
                'samesite' => 'Strict',
            )
        );
    }

    /**
     * Update cookie record.
     */
    private function updateUserRefCookie(?string $userRef): void
    {
        // Renew cookies
        unset($_COOKIE[$this->userReferenceKey]);
        setcookie(
            $this->userReferenceKey,
            $userRef,
            array(
                'expires'  => time() + $this->userTrackingTtl,
                'path'     => '/',
                'domain'   => __JS_COOKIE_DOMAIN,
                'secure'   => false,
                'httponly' => false,
                'samesite' => 'Strict',
            )
        );
    }

    /**
     * Makes token key from provided type (id any).
     *
     * @param null|int|string $type
     */
    private function makeTokenKey($type = null): string
    {
        $postfix = TYPES[$type][1] ?? '';

        return $this->cookieKey . $postfix;
    }

    /**
     * Resolve user reference key.
     */
    private function resolveUserReference(): string
    {
        $userRef = cookies()->getCookieParam($this->userReferenceKey, null) ?: null;
        if (null === $userRef) {
            $this->regeneratedUserReference = true;
            $this->updateUserRefCookie(
                $userRef = hash($this->hashAlgorithm, cookies()->getCookieParam($this->userTrackingKey) ?? (new UuidFactory())->uuid4())
            );
        }

        return $userRef;
    }

    /**
     * Resolves the user ID value.
     */
    private function resolveUserId(): ?int
    {
        return (int) id_session() ?: null;
    }

    /**
     * Load configuration file.
     */
    private function loadConfig(): array
    {
        $path = CONFIG_PATH;
        if (!file_exists($path)) {
            throw new RuntimeException('The configurations for autocomplete are not found.');
        }

        // the closure forbids access to the private scope in the included file
        $load = \Closure::bind(
            function ($path) { return include $path; },
            $this,
            TinyMVC_Library_Search_Autocomplete::class
        );

        return (array) $load($path);
    }
}

// End of file tinymvc_library_search_autocomplete.php
// Location: /tinymvc/myapp/plugins/tinymvc_library_search_autocomplete.php
