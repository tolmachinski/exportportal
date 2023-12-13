<?php

namespace App\Common\Traits;

trait InterpolatableTrait
{
    /**
     * Interpolates context values into the message placeholders.
     * Supports array dot notation.
     *
     * @param string $message The message
     * @param array  $context The context
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
