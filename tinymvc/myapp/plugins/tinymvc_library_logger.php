<?php

use Symfony\Component\DependencyInjection\ContainerInterface;

class TinyMVC_Library_Logger
{
    /**
     * Logger data layer.
     *
     * @var \Logs_Model
     */
    private $dl;

    /**
     * @param ContainerInterface $container The container instance
     */
    public function __construct(ContainerInterface $container)
    {
        if (null === $container->get('library.logger.data_layer', ContainerInterface::NULL_ON_INVALID_REFERENCE)) {
            $dataLayer = $this->getDefaultDataLayer($container);
        }

        $this->dl = $dataLayer;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     *
     * @throws \InvalidArgumentException if message is not of type string
     * @throws \RuntimeException         if message is empty
     */
    public function log($level, $message, array $context = [])
    {
        if (!is_string($message)) {
            $method = __METHOD__;
            $type = gettype($message);

            throw new \InvalidArgumentException("Invalid argument on position 2 at {$method} - string expected, got {$type}");
        }

        if (empty($message)) {
            throw new \RuntimeException('Log message cannot be empty');
        }

        $log = [
            'log_level'   => $level,
            'log_date'    => new \DateTime(),
            'log_message' => $this->interpolate($message, $context),
        ];
        if (!empty($context)) {
            $log['log_context'] = $context;
        }

        $this->write($log);
    }

    /**
     * System is unusable.
     *
     * @param string $message
     */
    public function emergency($message, array $context = [])
    {
        return $this->log('emergency', $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     */
    public function alert($message, array $context = [])
    {
        return $this->log('alert', $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     */
    public function critical($message, array $context = [])
    {
        return $this->log('critical', $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     */
    public function error($message, array $context = [])
    {
        return $this->log('error', $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     */
    public function warning($message, array $context = [])
    {
        return $this->log('warning', $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     */
    public function notice($message, array $context = [])
    {
        return $this->log('notice', $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     */
    public function info($message, array $context = [])
    {
        return $this->log('info', $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     */
    public function debug($message, array $context = [])
    {
        return $this->log('debug', $message, $context);
    }

    /**
     * Returns default data layer.
     *
     * @return \TinyMVC_Model
     */
    protected function getDefaultDataLayer()
    {
        return \model(Logs_Model::class, 'logs');
    }

    /**
     * Write log record through the data layer.
     */
    protected function write(array $log)
    {
        $this->dl->create($log);
    }

    /**
     * Interpolates context values into the message placeholders.
     *
     * @param string $message is a log message
     * @param array  $context is a log context
     *
     * @return string
     */
    private function interpolate($message, array $context = [])
    {
        // build a replacement array with braces around the context keys
        $replace = [];
        foreach ($context as $key => $val) {
            // check that the value can be casted to string
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        return strtr($message, $replace);
    }
}
