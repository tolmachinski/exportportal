<?php

declare(strict_types=1);

namespace App\Common\Http;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * {@inheritDoc}
 */
class LegacyJsonResponse extends JsonResponse
{
    /**
     * Creates instance of response.
     *
     * @param mixed $data
     */
    public function __construct(
        ?string $message = null,
        ?string $messageType = 'error',
        $data = null,
        int $status = 200,
        array $headers = [],
        bool $json = false
    ) {
        if (\is_string($data) && false === $json) {
            throw new InvalidArgumentException('');
        }

        parent::__construct(
            \is_string($data) ? $data : \array_merge($data ?? [], ['message' => $message, 'mess_type' => $messageType ?? 'error']),
            $status,
            $headers,
            $json
        );
    }

    /**
     * {@inheritDoc}
     */
    public static function fromJsonString(string $data, int $status = 200, array $headers = [])
    {
        return new static(null, null, $data, $status, $headers, true);
    }

    /**
     * {@inheritDoc}
     */
    public function setData($data = [])
    {
        if (\is_array($data)) {
            $data = \array_merge(['message' => null, 'mess_type' => 'error'], $data);
        }

        return parent::setData($data);
    }

    /**
     * {@inheritDoc}
     */
    public static function create($data = null, int $status = 200, array $headers = [])
    {
        trigger_deprecation('symfony/http-foundation', '5.1', 'The "%s()" method is deprecated, use "new %s()" instead.', __METHOD__, static::class);

        return new static(null, null, $data, $status, $headers);
    }
}
