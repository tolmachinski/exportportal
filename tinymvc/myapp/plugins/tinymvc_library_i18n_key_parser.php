<?php

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;
use SplFileInfo;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [02.12.2021]
 * library refactoring code style
 */
class TinyMVC_Library_I18n_Key_Parser
{
    private $basePath;

    private $i18nKeys = array();

    private $paths = array();

    private $found = array();

    private $failures = array();

    private $guessMatches = array();

    private $extensions = array(
        'php',
    );

    private $patterns = array(
        array('/translate\((?>((["\'])((?:(?=(\\\\?))\4.)*?)\2))(.*?)?\)/m', 3),
        array('/translate\((?>((["\'])((?:(?=(\\\\?))\4.)*?)\2))/m', 3),
    );

    public function __construct()
    {
        $this->basePath = trim(str_replace(array('\\', DIRECTORY_SEPARATOR), '/', $_SERVER['DOCUMENT_ROOT']), '/');
    }

    public function withBasePath($basePath)
    {
        if (!is_string($basePath)) {
            $type = gettype($basePath);
            $method = __METHOD__;

            throw new \InvalidArgumentException("Invalid argument on position 1 at {$method} - string expected, got {$type}");
        }

        if (!file_exists($basePath)) {
            throw new \RuntimeException('Provided path is not found on this server');
        }
        if (!is_dir($basePath)) {
            throw new \RuntimeException('Provided path is not a valid directory');
        }

        $this->basePath = $basePath;

        return $this;
    }

    public function withKeys(array $keys = array())
    {
        $this->i18nKeys = $keys;

        return $this;
    }

    public function withPath($path)
    {
        if (!is_string($path)) {
            $type = gettype($path);
            $method = __METHOD__;

            throw new \InvalidArgumentException("Invalid argument on position 1 at {$method} - string expected, got {$type}");
        }

        $this->paths[] = $path;

        return $this;
    }

    public function withPattern($pattern, $group)
    {
        if (!is_string($pattern)) {
            $type = gettype($pattern);
            $method = __METHOD__;

            throw new \InvalidArgumentException("Invalid argument on position 1 at {$method} - string expected, got {$type}");
        }
        if (!is_int($group)) {
            $type = gettype($group);
            $method = __METHOD__;

            throw new \InvalidArgumentException("Invalid argument on position 2 at {$method} - integer expected, got {$type}");
        }

        $this->patterns[] = array($pattern, $group);

        return $this;
    }

    public function withGuess($pattern, $keys)
    {
        if (!is_string($pattern)) {
            $type = gettype($pattern);
            $method = __METHOD__;

            throw new \InvalidArgumentException("Invalid argument on position 1 at {$method} - string expected, got {$type}");
        }

        $this->guessMatches[] = array(
            'pattern'    => $pattern,
            'keys'       => $keys,
            'isCallable' => is_callable($keys),
        );

        return $this;
    }

    public function withExtension($extension)
    {
        if (!is_string($extension)) {
            $type = gettype($extension);
            $method = __METHOD__;

            throw new \InvalidArgumentException("Invalid argument on position 1 at {$method} - string expected, got {$type}");
        }

        $this->extensions[] = trim($extension, '.');

        return $this;
    }

    public function clearPaths()
    {
        $this->paths = array();

        return $this;
    }

    public function clearPatterns()
    {
        $this->patterns = array();

        return $this;
    }

    public function clearGuesses()
    {
        $this->guessMatches = array();

        return $this;
    }

    public function clearExtensions()
    {
        $this->extensions = array();

        return $this;
    }

    public function parse()
    {
        $files = array();
        $failed = array();
        $foundMessages = array();
        foreach ($this->processPaths($this->paths) as /* @var SplFileInfo $file */ $file) {
            $path = $file->getRealPath();
            list($lines, $failures) = $this->lintKeys($path);

            $failed = array_merge($failed, array_values($failures));
            $foundMessages = array_merge($foundMessages, array_values($lines));
        }

        // Guess failed message key
        foreach ($failed as &$message) {
            if (empty($message)) {
                continue;
            }

            $possibleKeys = $this->guessFailedMessage($message['message']);
            $possibleMessages = array_map(function ($key) use ($message) {
                $message['message'] = $key;

                return $message;
            }, $possibleKeys);

            $message = null;
            $foundMessages = array_merge($foundMessages, $possibleMessages);
        }
        $failed = array_filter($failed, function ($item) { return !empty($item); });

        $this->found = $foundMessages;
        $this->failures = $failed;

        return $this;
    }

    public function makeI18nFromRaw(array $keys, $transformCallback = null)
    {
        if (null === $transformCallback) {
            $transformCallback = function ($item) {
                if (!isset($item['id_key']) || !isset($item['translation_key'])) {
                    return null;
                }

                return array(
                    'id'  => $item['id_key'],
                    'key' => $item['translation_key'],
                );
            };
        }

        $keys = array_filter(
            array_map(
                function ($rawItem) use ($transformCallback) {
                    $item = $transformCallback($rawItem);
                    if (
                        null !== $item &&
                        (
                            !isset($item['id']) ||
                            !isset($item['key'])
                        )
                    ) {
                        throw new \RuntimeException("Transform callback must return an associative array with 'id' and 'key' keys");
                    }

                    return $item;
                },
                $keys
            ),
            function ($item) {
                return null !== $item;
            }
        );

        return arrayByKey($keys, 'key');
    }

    public function toArray()
    {
        return array(
            'messages' => $this->found,
            'failures' => $this->failures,
        );
    }

    public function toImportArray()
    {
        $import = array();
        foreach ($this->found as $item) {
            $key = $item['message'];
            if (!isset($this->i18nKeys[$key])) {
                continue;
            }

            $key = (int) $this->i18nKeys[$key]['id'];
            $import[$key]['id_key'] = $key;
            $import[$key]['translation_file_entries']['list'][] = str_replace(array('\\', DIRECTORY_SEPARATOR, $this->basePath), array('/', '/', ''), $item['path']);
        }
        usort($import, function ($a, $b) {
            if ($a['id_key'] == $b['id_key']) {
                return 0;
            }

            return ($a['id_key'] < $b['id_key']) ? -1 : 1;
        });

        return $import;
    }

    private function findFiles($path, \AppendIterator $collector, $extensions = 'php')
    {
        $directory = new RecursiveDirectoryIterator(
            $path,
            FilesystemIterator::SKIP_DOTS
        );
        $directory = new RecursiveIteratorIterator(
            $directory,
            RecursiveIteratorIterator::CHILD_FIRST
        );
        $files = new RegexIterator($directory, "/^.+\.({$extensions})$/i", RecursiveRegexIterator::MATCH);
        $collector->append($files);
    }

    private function processPaths($paths, array $extensions = array('php'))
    {
        $collector = new \AppendIterator();
        foreach ($paths as $key => $path) {
            $path = rtrim(str_replace(array('\\', DIRECTORY_SEPARATOR), array('/', '/'), $path), '/');
            if (false === strpos($path, $this->basePath)) {
                $path = "{$this->basePath}/" . ltrim($path, '/');
            }

            $this->findFiles($path, $collector, implode('|', $this->extensions));
        }

        foreach ($collector as $file) {
            yield $file;
        }
    }

    private function lintKeys($path)
    {
        $lines = array();
        $failures = array();
        $content = file_get_contents($path);
        foreach ($this->patterns as list($pattern, $group)) {
            if (preg_match_all($pattern, $content, $matches, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE)) {
                $lines = array_merge($lines, iterator_to_array($this->matchMessage($matches[$group], $path)));
                $failures = array_merge($failures, array_values(iterator_to_array($this->matchFaiure($matches[$group], $path))));
            }
        }

        return array($lines, $failures);
    }

    private function matchMessage(array $matches = array(), $path)
    {
        foreach ($matches as $message) {
            $message = $message[0];
            if (is_string($message) && false !== strpos($message, '$')) {
                continue;
            }
            $placeholders = false !== mb_strpos($message, '[') and false !== mb_strpos($message, ']');

            yield $message => compact('message', 'placeholders', 'path');
        }
    }

    private function matchFaiure(array $matches = array(), $path)
    {
        foreach ($matches as $message) {
            $offset = $message[1];
            $message = $message[0];
            if (false === strpos($message, '$')) {
                continue;
            }
            $placeholders = false !== mb_strpos($message, '[') and false !== mb_strpos($message, ']');

            yield $message => compact('message', 'placeholders', 'path');
        }
    }

    private function guessFailedMessage($message)
    {
        $possibleKeys = array();
        foreach ($this->guessMatches as $guess) {
            if (!preg_match($guess['pattern'], $message)) {
                continue;
            }
            $possibleKeys = $guess['keys'];
            if ($guess['isCallable']) {
                $callable = $guess['keys'];
                $possibleKeys = $callable();
            }
        }

        return $possibleKeys;
    }
}
