<?php

declare(strict_types=1);

namespace App\Bridge\Matrix;

use App\Common\Exceptions\ContextAwareException;
use ExportPortal\Matrix\Client\ApiException;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Throwable;

class ExceptionHandler implements ExceptionHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @param LoggerInterface $logger the logger
     */
    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function handleException(Throwable $exception, ?string $message, array $context = [], string $level = LogLevel::ERROR): void
    {
        switch (\get_class($exception)) {
            case ContextAwareException::class:
                $this->handleContextAwareException($exception, $message, $context, $level);

                break;
            case RequestException::class:
                $this->handleRequestException($exception, $message, $context, $level);

                break;
            case ApiException::class:
                $this->handleApiException($exception, $message, $context, $level);

                break;
            case ClientException::class:
                $this->handleClientException($exception, $message, $context, $level);

                break;

            default:
                if (!$this->logger) {
                    return;
                }
                if (null !== $message) {
                    $message = \trim($message, '.') . ' due to error: ';
                }

                $this->logger->log($level, ($message ?? 'The operation failed') . $exception->getMessage(), $this->makeLogContext($context));

                break;
        }
    }

    /**
     * Handles request exception.
     */
    protected function handleRequestException(RequestException $exception, ?string $message, array $context = [], string $level = LogLevel::ERROR): void
    {
        if (!$this->logger) {
            return;
        }

        $message = $message ?? $exception->getMessage();
        $response = $exception->getResponse();
        if (null !== $response) {
            $responseBody = \json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
            $context = ['response' => $responseBody];
        }

        $this->logger->log($level, $message, $this->makeLogContext($context));
    }

    /**
     * Handles api exception.
     */
    protected function handleApiException(ApiException $exception, ?string $message, array $context = [], string $level = LogLevel::ERROR): void
    {
        if (!$this->logger) {
            return;
        }

        $this->handleClientException(ClientException::fromApiException($exception), $message, $context, $level);
    }

    /**
     * Handles client exception.
     */
    protected function handleClientException(ClientException $exception, ?string $message, array $context = [], string $level = LogLevel::ERROR): void
    {
        if (!$this->logger) {
            return;
        }

        $message = $message ?? $exception->getMessage();
        $response = $exception->getResponse();
        $errorText = $response->getError() ?? null;
        $errorCode = $response->getErrcode() ?? null;
        if (!empty($errorCode)) {
            $message = \sprintf('%s due to error "%s"', \rtrim($message, '.'), $errorText);
            $context['error_text'] = $errorText;
        }
        if (!empty($errorText)) {
            $message = \sprintf('%s with code "%s"', \rtrim($message, '.'), $errorCode);
            $context['error_code'] = $errorCode;
        }
        $message = $message . '.';

        $this->logger->log($level, $message, $this->makeLogContext($context));
    }

    /**
     * Handles the context exception.
     */
    protected function handleContextAwareException(ContextAwareException $exception, ?string $message, array $context = [], string $level = LogLevel::ERROR): void
    {
        if (null !== $previous = $exception->getPrevious()) {
            if ($previous instanceof ApiException) {
                $this->handleApiException($previous, sprintf($exception->getMessage()), array_merge($exception->getContext(), $context), $level);

                return;
            }

            if ($previous instanceof ClientException) {
                $this->handleClientException($previous, sprintf($exception->getMessage()), array_merge($exception->getContext(), $context), $level);

                return;
            }

            if ($previous instanceof RequestException) {
                $this->handleRequestException($previous, $message ?? 'Failed to send request to the matrix server', $context, $level);
            }
        }

        throw $prev ?? $exception;
    }

    /**
     * Makes the context for logger.
     */
    protected function makeLogContext(array $additionalContext = [])
    {
        return \array_merge([], $additionalContext);
    }
}
