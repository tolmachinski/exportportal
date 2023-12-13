<?php

use Symfony\Component\DependencyInjection\ContainerInterface;

class TinyMVC_Library_URI
{
    public $path;
    public $replace_template;

    public function __construct(ContainerInterface $container)
    {
        $kernel = $container->get('kernel');
        $this->path = $kernel->url_segments;
        $this->replace_template = $kernel->my_config['replace_uri_template'] ?? '';
    }

    public function current_url()
    {
        $pageURL = 'http';
        if (isset($_SERVER['HTTPS']) && 'on' == $_SERVER['HTTPS']) {
            $pageURL .= 's';
        }

        $pageURL .= '://';
        if (!in_array($_SERVER['SERVER_PORT'], array('80', '443'))) {
            $pageURL .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
        } else {
            $pageURL .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        }

        return $pageURL;
    }

    public function hostname()
    {
        $host = 'http';
        if ('on' == $_SERVER['HTTPS']) {
            $host .= 's';
        }

        $host .= '://';
        if ('80' != $_SERVER['SERVER_PORT']) {
            $host .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'];
        } else {
            $host .= $_SERVER['SERVER_NAME'];
        }

        return $host;
    }

    public function path()
    {
        return substr($this->current_url(), mb_strlen($this->hostname()));
    }

    public function segments()
    {
        return !empty($this->path) ? $this->path : array();
    }

    public function segment($index)
    {
        return (!empty($this->path[$index])) ? $this->path[$index] : false;
    }

    public function uri_to_assoc($index = 0, $uri = array())
    {
        $path = empty($uri) ? $this->path : $uri;

        $assoc = array();
        $path_count = count($path);
        for ($x = $path_count, $y = $index - 1; $y <= $x; $y += 2) {
            if (isset($path[$y])) {
                $assoc[$path[$y]] = isset($path[$y + 1]) ? $path[$y + 1] : '';
            }
        }

        return $assoc;
    }

    public function uri_to_array($index = 0)
    {
        return (is_array($this->path)) ? array_slice($this->path, $index) : false;
    }

    public function assoc_to_uri($array)
    {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (0 !== $key && 0 !== $value) {
                    $str[] = $key . '/' . cleanInput($value);
                } else {
                    $str[] = $value;
                }
            }

            return !empty($str) ? implode('/', $str) . '/' : '';
        }

        return false;
    }

    public function make_templates(array $map, array $uri_segments = array(), $exclude_uri_segments = false)
    {
        //we need work only with segments contains in $map
        // $uri_segments = array_intersect_key($map, $uri_segments);
        $uri_filter_keys = $uri_map_diff = array();
        $current_get = $_GET;
        $query_segments = isset($current_get['lang']) ? array('lang' => $current_get['lang']) : array();

        foreach ($map as $index => $item) {
            if ($item['type'] === 'get' && isset($current_get[$index])) {
				$query_segments[$index] = $current_get[$index];
            }

            if (!(isset($item['type']) && 'uri' === $item['type'])) {
                continue;
            }
            $uri_filter_keys[$index] = null;
            if (!isset($uri_segments[$index])) {
                $uri_map_diff[$index] = 1;
            }
        }

        $uri_map_data = array_merge($uri_filter_keys, $uri_segments);
        $update_get = $update_uri = function (&$arr, $key) {
            $arr[$key] = $this->replace_template;
        };

        foreach ($map as $param => $entry) {
            $uri = $uri_map_data;
            $get = $query_segments;
            $no_data_clean = $uri_map_diff;

            if (isset($no_data_clean[$param])) {
                unset($no_data_clean[$param]);
            }

            $to_clean = (!empty($entry['deny'])) ? array_flip((array) $entry['deny']) : array();
            if (!empty($to_clean)) {
                $to_clean = array_merge($to_clean, $no_data_clean);
            } else {
                $to_clean = $no_data_clean;
            }

            $uri = array_diff_key($uri, $to_clean);
            $get = array_diff_key($get, $to_clean);

            if (isset($entry['type']) && isset(${"update_{$entry['type']}"})) {
                if (!$exclude_uri_segments) {
                    ${"update_{$entry['type']}"}(${$entry['type']}, $param);
                }
            }

            $uri = $this->assoc_to_uri($uri);
            $get = !empty($get) ? '?' . http_build_query($get) : '';
            $templates[$param] = $uri . $get;
        }

        return $templates;
    }
}
