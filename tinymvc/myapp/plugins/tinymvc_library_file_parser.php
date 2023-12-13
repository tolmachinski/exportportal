<?php

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;
use SplFileInfo;

/**
 * @author Bendiucov Tatiana
 * @todo Remove [01.12.2021]
 * Library not used
 */
class TinyMVC_Library_File_Parser
{
    private $basePath;

    private $files = array();

    private $directories = array();

    private $found = array();

    private $failures = array();

    private $guessWork = array();

    private $extensions = array(
        'php',
    );

    private $patterns = array();

    private $transformers = array();

    private $filters = array();

    private $matchers = array(
        'success' => array(),
        'failure' => array(),
    );

    public function __construct()
    {
        $this->basePath = trim(str_replace(array('\\', DIRECTORY_SEPARATOR), '/', $_SERVER['DOCUMENT_ROOT']), '/');
        $this->withMatcher(array($this, 'defaultSuccessMatcher'), 'success');
        $this->withMatcher(array($this, 'defaultFailureMatcher'), 'failure');
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

    public function withDirectory($path)
    {
        if (!is_string($path)) {
            $type = gettype($path);
            $method = __METHOD__;

            throw new \InvalidArgumentException("Invalid argument on position 1 at {$method} - string expected, got {$type}");
        }

        $path = ltrim($path, '/');
        $fullpath = "{$this->basePath}/{$path}";
        if(!is_dir($fullpath)) {
            throw new \RuntimeException("Only path to directory is accepted");

        }

        $this->directories[] = $path;

        return $this;
    }

    public function withFile($path)
    {
        if (!is_string($path)) {
            $type = gettype($path);
            $method = __METHOD__;

            throw new \InvalidArgumentException("Invalid argument on position 1 at {$method} - string expected, got {$type}");
        }

        $path = ltrim($path, '/');
        $fullpath = "{$this->basePath}/{$path}";
        if(!is_file($fullpath)) {
            throw new \RuntimeException("Only path to file is accepted");

        }

        $this->files[] = $path;

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

        $this->guessWork[] = array(
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

    public function withFilter($filter)
    {
        if (!is_callable($filter)) {
            $type = gettype($filter);
            $method = __METHOD__;

            throw new \InvalidArgumentException("Invalid argument on position 1 at {$method} - callable expected, got {$type}");
        }

        $this->filters[] = $filter;

        return $this;
    }

    public function withTransformer($transformer)
    {
        if (!is_callable($transformer)) {
            $type = gettype($transformer);
            $method = __METHOD__;

            throw new \InvalidArgumentException("Invalid argument on position 1 at {$method} - callable expected, got {$type}");
        }

        $this->transformers[] = $transformer;

        return $this;
    }

    public function withMatcher($matcher, $type)
    {
        if (!is_callable($matcher)) {
            $type = gettype($matcher);
            $method = __METHOD__;

            throw new \InvalidArgumentException("Invalid argument on position 1 at {$method} - callable expected, got {$type}");
        }
        if (!is_string($type)) {
            $type = gettype($type);
            $method = __METHOD__;

            throw new \InvalidArgumentException("Invalid argument on position 2 at {$method} - string expected, got {$type}");
        }

        if(!isset($this->matchers[$type])) {
            throw new \InvalidArgumentException("Unknown matcher type provided");
        }

        $this->matchers[$type][] = $matcher;

        return $this;
    }

    public function withSuccessMatcher($matcher)
    {
        return $this->withMatcher($matcher, 'success');
    }

    public function withFailureMatcher($matcher)
    {
        return $this->withMatcher($matcher, 'failure');
    }

    public function clearDirectories()
    {
        $this->directories = array();

        return $this;
    }

    public function clearFiles()
    {
        $this->directories = array();

        return $this;
    }

    public function clearPatterns()
    {
        $this->patterns = array();

        return $this;
    }

    public function clearGuessWork()
    {
        $this->guessWork = array();

        return $this;
    }

    public function clearExtensions()
    {
        $this->extensions = array();

        return $this;
    }

    public function clearFilters()
    {
        $this->transformers = array();

        return $this;
    }

    public function clearTransformers()
    {
        $this->transformers = array();

        return $this;
    }

    public function clearMatchers()
    {
        $this->matchers = array(
            'success' => array(),
            'failure' => array(),
        );
    }

    public function clearAll()
    {
        $this->clearDirectories();
        $this->clearExtensions();
        $this->clearFiles();
        $this->clearPatterns();
        $this->clearGuessWork();
        $this->clearFilters();
        $this->clearTransformers();
        $this->clearMatchers();
    }

    public function parse()
    {
        $this->requireMatchersPresence();

        $files = array();
        $failed = array();
        $foundMessages = array();
        $paths = array_merge($this->directories, $this->files);
        foreach ($this->processPaths($paths) as /** @var SplFileInfo $file */ $file) {
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

            $possibleValues = $this->guessFailedMessage($message['message']);
            if(null === $possibleValues || empty($possibleValues)) {
                continue;
            }

            $possibleMessages = array_map(function ($key) use ($message) {
                $message['message'] = $key;

                return $message;
            }, $possibleValues);

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

    public function getRawResult()
    {
        return array(
            'messages' => $this->found,
            'failures' => $this->failures,
        );
    }

    public function getFailures()
    {
        return $this->failures;
    }

    public function getMatches()
    {
        $matches = $this->found;
        if(!empty($this->filters)) {
            $matches = $this->filterEach($matches, $this->filters);
        }
        if(!empty($this->transformers)) {
            $matches = $this->transformEach($matches, $this->transformers);
        }

        return $matches;
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
        $file = new \SplFileInfo($path);
        if($file->isDir()) {
            $directory = new RecursiveDirectoryIterator(
                $path,
                FilesystemIterator::SKIP_DOTS
            );
            $directory = new RecursiveIteratorIterator(
                $directory,
                RecursiveIteratorIterator::CHILD_FIRST
            );
            $files = new RegexIterator($directory, "/^.+\.({$extensions})$/i", RecursiveRegexIterator::MATCH);
        } else {
            $files = new ArrayIterator(array($file));
        }

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
                $lines = array_merge($lines, $this->processMatchType($this->matchers['success'], $matches[$group], $path));
                $failures = array_merge($failures, $this->processMatchType($this->matchers['failure'], $matches[$group], $path));
            }
        }

        return array($lines, $failures);
    }

    private function processMatchType(array $matchers, array $matches, $path)
    {
        if(empty($matchers)) {
            return array();
        }

        $messages = array();
        foreach ($matchers as $matcher) {
            $matcherSet = $matcher($matches, $path);
            if(!is_array($matcherSet) && !is_iterable($matcherSet)) {
                throw new \UnexpectedValueException("Expected array or iterable to be returned by matcher");
            }
            if($matcherSet instanceof \Traversable) {
                $matcherSet = iterator_to_array($matcherSet);
            }

            $messages = array_merge($messages, $matcherSet);
        }

        return $messages;
    }

    private function guessFailedMessage($message)
    {
        $possibleValues = array();
        foreach ($this->guessWork as $guess) {
            if (!preg_match($guess['pattern'], $message)) {
                continue;
            }
            $possibleValues = $guess['keys'];
            if ($guess['isCallable']) {
                $callable = $guess['keys'];
                $possibleValues = $callable();
            }
        }

        return $possibleValues;
    }

    private function requireMatchersPresence()
    {
        if(empty($this->matchers['success'])) {
            throw new \RuntimeException("At least one success matcher is required");
        }
    }

    protected function transformEach(array $data, array $callables)
    {
        foreach ($callables as $callable) {
            $procesedResult = $callable($data);
            if(!is_array($procesedResult) && !is_iterable($procesedResult)) {
                throw new \UnexpectedValueException("Expected array or iterable to be returned");
            }

            if($procesedResult instanceof \Traversable) {
                $procesedResult = iterator_to_array($procesedResult);
            }

            $data = $procesedResult;
        }

        return $data;
    }

    protected function filterEach(array $data, array $callables)
    {
        foreach ($callables as $callable) {
            $data = array_filter($data, $callable);
        }

        return $data;
    }

    protected function defaultSuccessMatcher(array $matches = array(), $path)
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

    protected function defaultFailureMatcher(array $matches = array(), $path)
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
}
