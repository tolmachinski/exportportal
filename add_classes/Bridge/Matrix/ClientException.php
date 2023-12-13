<?php

declare(strict_types=1);

namespace App\Bridge\Matrix;

use ExportPortal\Matrix\Client\ApiException;
use ExportPortal\Matrix\Client\Model\Error;
use ExportPortal\Matrix\Client\Model\RateLimitError;

final class ClientException extends \RuntimeException
{
    /**
     * The response object.
     *
     * @var Error|RateLimitError
     */
    private $response;

    /**
     * The response headers.
     *
     * @var string[]
     */
    private array $responseHeaders;

    /**
     * @param Error|RateLimitError $response
     * @param string[]             $responseHeaders
     */
    public function __construct(string $message, $response, array $responseHeaders = [], int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->response = $response;
        $this->responseHeaders = $responseHeaders;
    }

    /**
     * Creates the client excpetion from instance of api exception.
     */
    public static function fromApiException(ApiException $exception): self
    {
        /** @var null|Error|RateLimitError */
        $response = $exception->getResponseObject();
        // If response is already parsed, then we can just use it right away.
        // Otherwise, we need to prepare the response object.
        if (!$response instanceof Error && !$response instanceof RateLimitError) {
            $errorText = null;
            $errorCode = null;
            if (null === $errorText && null === $errorCode) {
                $responseBody = $exception->getResponseBody();
                if (\is_string($responseBody)) {
                    $decodedResponseBody = \json_decode($responseBody);
                    $responseBody = $decodedResponseBody ?? $responseBody;
                }

                $errorText = $responseBody;
                $errorCode = null;
                if ($responseBody instanceof \stdClass) {
                    $errorCode = $responseBody->errcode;
                    $errorText = $responseBody->error;
                } elseif (\is_array($responseBody)) {
                    $errorCode = $responseBody['errcode'];
                    $errorText = $responseBody['error'];
                }
            }

            $response = 'M_RESOURCE_LIMIT_EXCEEDED' === $errorCode ? new RateLimitError() : new Error();
            $response->setErrcode($errorCode);
            $response->setError($errorText);
        }

        return new static($exception->getMessage(), $response, $exception->getResponseHeaders() ?? [], $exception->getCode(), $exception);
    }

    /**
     * Get the response object.
     *
     * @return Error|RateLimitError
     */
    public function getResponse(): object
    {
        return $this->response;
    }

    /**
     * Get the response headers.
     *
     * @return string[]
     */
    public function getResponseHeaders(): array
    {
        return $this->responseHeaders;
    }
}
