<?php

namespace App\Logger;

use App\Logger\Contracts\HandlerInterface;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Logger implements LoggerInterface
{
    /**
     * System is unusable.
     */
    const EMERGENCY = 0;

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc.
     * This should trigger the SMS alerts and wake you up.
     */
    const ALERT = 1;

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     */
    const CRITICAL = 2;

    /**
     * Runtime errors that do not require immediate action but should typically be logged and monitored.
     */
    const ERROR = 3;

    /**
     * Exceptional occurrences that are not errors.
     *
     * Examples: Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily wrong.
     */
    const WARNING = 4;

    /**
     * Normal but significant events.
     */
    const NOTICE = 5;

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     */
    const INFO = 6;

    /**
     * Detailed debug information.
     */
    const DEBUG = 7;

    /**
     * List of used levels.
     *
     * @var string[] Level number with level key
     */
    protected static $levels = array(
        self::EMERGENCY => LogLevel::EMERGENCY,
        self::ALERT     => LogLevel::ALERT,
        self::CRITICAL  => LogLevel::CRITICAL,
        self::ERROR     => LogLevel::ERROR,
        self::WARNING   => LogLevel::WARNING,
        self::NOTICE    => LogLevel::NOTICE,
        self::INFO      => LogLevel::INFO,
        self::DEBUG     => LogLevel::DEBUG,
    );

    /**
     * The handlers stack.
     *
     * @var HandlerInterface[]
     */
    private $handlers = array();

    /**
     * The processors stack.
     *
     * Processors can handle all records
     *
     * @var array
     */
    private $processors = array();

    /**
     * Current timezone.
     *
     * @var \DateTimeZone
     */
    private $timezone;

    public function __construct(array $handlers = array(), array $processors = array(), \DateTimeZone $timezone = null)
    {
        $this->setHandlers($handlers);
        $this->setProcessors($processors);
        $this->timezone = $timezone;
    }

    /**
     * Clears the handlers.
     *
     * @return self
     */
    public function clearHandlers()
    {
        $this->handlers = array();

        return $this;
    }

    /**
     * Pushes one log handler to the handlers stack.
     *
     * @param HandlerInterface $handler
     *
     * @return self
     */
    public function addHandler(HandlerInterface $handler)
    {
        $this->handlers[] = $handler;

        return $this;
    }

    /**
     * Add provided set of handlers to the handlers stack.
     *
     * The hashmap keys are ignored
     *
     * @param HandlerInterface[] $handlers
     *
     * @return self
     */
    public function setHandlers(array $handlers)
    {
        foreach ($handlers as $handler) {
            $this->addHandler($handler);
        }

        return $this;
    }

    /**
     * Clears the processors.
     *
     * @return self
     */
    public function clearProcessors()
    {
        $this->processors = array();

        return $this;
    }

    /**
     * Pushes one log processor to the processors stack.
     *
     * @param callable $processor
     *
     * @return self
     */
    public function addProcessor(callable $processor)
    {
        $this->processors[] = $processor;

        return $this;
    }

    /**
     * Add provided set of processors to the processors stack.
     *
     * The hashmap keys are ignored
     *
     * @param callable[] $processors
     *
     * @return self
     */
    public function setProcessors(array $processors)
    {
        foreach ($processors as $processor) {
            $this->addProcessor($processor);
        }

        return $this;
    }

    /**
     * Adds a log record at an arbitrary level.
     *
     * @param mixed  $level   The arbitrary log level
     * @param string $message The log message
     * @param array  $context The log context
     */
    public function log($level, $message, array $context = array()): void
    {
        $this->addRecord(static::toKnownLevel($level), (string) $message, $context);
    }

    /**
     * Adds a log record at the EMERGENCY level.
     *
     * @param string $message The log message
     * @param array  $context The log context
     */
    public function emergency($message, array $context = array()): void
    {
        $this->addRecord(static::EMERGENCY, (string) $message, $context);
    }

    /**
     * Adds a log record at the ALERT level.
     *
     * @param string $message The log message
     * @param array  $context The log context
     */
    public function alert($message, array $context = array()): void
    {
        $this->addRecord(static::ALERT, (string) $message, $context);
    }

    /**
     * Adds a log record at the CRITICAL level.
     *
     * @param string $message The log message
     * @param array  $context The log context
     */
    public function critical($message, array $context = array()): void
    {
        $this->addRecord(static::CRITICAL, (string) $message, $context);
    }

    /**
     * Adds a log record at the ERROR level.
     *
     * @param string $message The log message
     * @param array  $context The log context
     */
    public function error($message, array $context = array()): void
    {
        $this->addRecord(static::ERROR, (string) $message, $context);
    }

    /**
     * Adds a log record at the WARNING level.
     *
     * @param string $message The log message
     * @param array  $context The log context
     */
    public function warning($message, array $context = array()): void
    {
        $this->addRecord(static::WARNING, (string) $message, $context);
    }

    /**
     * Adds a log record at the NOTICE level.
     *
     * @param string $message The log message
     * @param array  $context The log context
     */
    public function notice($message, array $context = array()): void
    {
        $this->addRecord(static::NOTICE, (string) $message, $context);
    }

    /**
     * Adds a log record at the INFO level.
     *
     * @param string $message The log message
     * @param array  $context The log context
     */
    public function info($message, array $context = array()): void
    {
        $this->addRecord(static::INFO, (string) $message, $context);
    }

    /**
     * Adds a log record at the DEBUG level.
     *
     * @param string $message The log message
     * @param array  $context The log context
     */
    public function debug($message, array $context = array()): void
    {
        $this->addRecord(static::DEBUG, (string) $message, $context);
    }

    /**
     * Gets the name of the logging level.
     *
     * @param int $level The log level number
     *
     * @throws \Psr\Log\InvalidArgumentException If level is not defined
     */
    public static function getLevelName($level)
    {
        if (!isset(static::$levels[$level])) {
            throw new InvalidArgumentException("Level \"{$level}\" is not defined, you must use one of: " . implode(', ', array_keys(static::$levels)));
        }

        return static::$levels[$level];
    }

    /**
     * Gets the full levels map
     *
     * @return array<int,string>
     */
    public static function getLevels()
    {
        return static::$levels;
    }

    /**
     * Converts PSR-3 levels to Monolog ones if necessary.
     *
     * @param int|string $level Level number (monolog) or name (PSR-3)
     *
     * @throws \Psr\Log\InvalidArgumentException If level is not defined
     */
    public static function toKnownLevel($level)
    {
        if (is_string($level)) {
            $className = __CLASS__;
            $constantName = strtoupper($level);
            if (defined("{$className}::{$constantName}")) {
                return constant("{$className}::{$constantName}");
            }

            throw new InvalidArgumentException("Level \"{$level}\" is not defined, yo can use one of: " . implode(', ', array_keys(static::$levels)));
        }

        return $level;
    }

    /**
     * Add a log record.
     *
     * @param mixed  $level   The log level
     * @param string $message The log message
     * @param array  $context The log context
     */
    protected function addRecord($level, $message, array $context = array())
    {
        if (empty($this->handlers)) {
            return false;
        }

        $logRecord = $this->createRecord($level, $message, $context);

        try {
            if (!empty($this->processors)) {
                foreach ($this->processors as $processor) {
                    $logRecord = call_user_func($processor, $logRecord);
                }
            }

            foreach ($this->handlers as /* @var HandlerInterface  $handler */ $handler) {
                if ($handler->isClosed()) {
                    continue;
                }

                $handler->handle($logRecord);
            }
        } catch (\Exception $exception) {
            $this->handleException($exception, $logRecord);
        }

        return true;
    }

    protected function handleException(\Exception $exception, array $record)
    {
        // Handle exception
    }

    protected function createRecord($level, $message, array $context = array())
    {
        return array(
            'message'   => $this->interpolate($message, $context),
            'context'   => $context,
            'level'     => $level,
            'levelName' => static::getLevelName($level),
            'datetime'  => new \DateTimeImmutable('now', $this->timezone),
        );
    }

    /**
     * Interpolates context values into the message placeholders.
     * Supports array dot notation.
     *
     * @param string $message The log message
     * @param array  $context The log context
     *
     * @return string
     */
    private function interpolate($message, array $context = array())
    {
        if (!preg_match_all("/(\{([a-zA-Z0-9\_\.]+?)\})/m", $message, $matches, PREG_PATTERN_ORDER)) {
            return $message;
        }

        if (empty($matches[2])) {
            return $message;
        }

        $replace = array();
        foreach ($matches[2] as $key) {
            $value = null;
            if (false !== strpos($key, '.')) {
                $exists = true;
                $keys = explode('.', $key);
                $location = $context;
                foreach ($keys as $innerPath) {
                    if (!isset($location[$innerPath])) {
                        $exists = false;

                        break;
                    }

                    $location = $location[$innerPath];
                }

                if ($exists) {
                    $value = $location;
                }
            } else {
                if (isset($context[$key])) {
                    $value = $context[$key];
                }
            }

            if (null !== $value && !is_array($value) && (!is_object($value) || method_exists($value, '__toString'))) {
                $replace["{{$key}}"] = $value;
            }
        }

        return strtr($message, $replace);
    }
}
