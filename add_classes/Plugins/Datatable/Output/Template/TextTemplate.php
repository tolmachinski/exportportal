<?php

declare(strict_types=1);

namespace App\Plugins\Datatable\Output\Template;

use Traversable;

final class TextTemplate extends AbstractTemplate
{
    /**
     * The template text.
     */
    private string $templateText;

    /**
     * Creates instance of the template.
     *
     * @param mixed $attributes
     */
    public function __construct(string $templateText, $attributes = [])
    {
        parent::__construct($attributes);

        $this->templateText = $templateText;
    }

    /**
     * {@inheritdoc}
     */
    public function render(): string
    {
        if (null === $this->templateText) {
            return '';
        }

        $attributes = $this->getAttributes();
        if ($attributes instanceof Traversable) {
            $attributes = \iterator_to_array($attributes);
        }

        return $this->interpolate($this->templateText, $attributes);
    }

    /**
     * Interpolates arguments values into the template placeholders.
     * Supports array dot notation.
     */
    private function interpolate(string $templateText, array $arguments = []): string
    {
        if (!preg_match_all('/(\\{([a-zA-Z0-9\\_\\.]+?)\\})/m', $templateText, $matches, PREG_PATTERN_ORDER)) {
            return $templateText;
        }

        if (empty($matches[2])) {
            return $templateText;
        }

        $replace = [];
        foreach ($matches[2] as $key) {
            $value = null;
            if (false !== strpos($key, '.')) {
                $exists = true;
                $keys = explode('.', $key);
                $location = $arguments;
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
                if (isset($arguments[$key])) {
                    $value = $arguments[$key];
                }
            }

            $replace["{{{$key}}}"] = (string) $value;
        }

        return strtr($templateText, $replace);
    }
}
