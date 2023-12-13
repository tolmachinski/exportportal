<?php

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [03.12.2021]
 * library refactoring: code style, optimize code
 */
class TinyMVC_Library_VideoThumb
{
    public $config;

    private $pathPrefix = 'temp/';

    /**
     * @param ContainerInterface $container The container instance
     */
    public function __construct(ContainerInterface $container)
    {
        $this->config = array_merge(
            [
                'imagesPath' => $this->pathPrefix,
                'imagesUrl'  => $this->pathPrefix,
                'emptyImage' => '',
            ],
            $container->hasParameter('library.video_thumb.params') ? $container->getParameter('library.video_thumb.params') : []
        );

        /*if (!is_dir($this->config['imagesPath'])) {
            mkdir($this->config['imagesPath']);
        }*/
    }

    /*
     * Return error message from lexicon array
     * @param string $msg Array key
     * @return string Message
     * */
    public function lexicon($msg = '')
    {
        $array = [
            'video_err_ns' => 'Empty video URL.',
            'video_err_nf' => translate('validation_video_link_incorrect'),
        ];

        return @$array[$msg];
    }

    /*
     * Check and format video link, then fire download of preview image
     * @param string $video Remote url on video hosting
     * @return array $array Array with formatted video link and preview url
     * */
    public function process($video = '')
    {
        if (empty($video)) {
            return ['error' => $this->lexicon('video_err_ns')];
        }

        if (!preg_match('/^(http|https)\:\/\//i', $video)) {
            $video = 'https://' . $video;
        }

        $info = $this->getVID($video);

        if (!$info['v_id']) {
            return ['error' => $this->lexicon('video_err_nf')];
        }

        switch ($info['type']) {
            case 'youtube': // YouTube
                $video = 'https://www.youtube.com/embed/' . $info['v_id'];
                $image = 'http://i3.ytimg.com/vi/' . $info['v_id'] . '/maxresdefault.jpg';
                if (!$this->fileExist($image)) {
                    $image = 'https://img.youtube.com/vi/' . $info['v_id'] . '/0.jpg';
                }

                $array = [
                    'video' => $video,
                    'image' => $this->getRemoteImage($image),
                    'v_id'  => $info['v_id'],
                    'type' 	=> $info['type'],
                ];

            break;
            case 'vimeo': // Vimeo
                $video = 'https://player.vimeo.com/video/' . $info['v_id'];
                $image = '';

                if ($json_data = file_get_contents('http://vimeo.com/api/v2/video/' . $info['v_id'] . '.json')) {
                    $json_data = json_decode($json_data);
                    $image = $json_data[0]->thumbnail_large ? (string) $json_data[0]->thumbnail_large : $json_data[0]->thumbnail_medium;
                    $images = explode('_', $image);
                    $image = $this->getRemoteImage($images[0] . '_800.jpg');
                }
                $array = [
                    'video' => $video,
                    'image' => $image,
                    'v_id' 	=> $info['v_id'],
                    'type' 	=> $info['type'],
                ];

            break;

            default:
                $array = ['error' => $this->lexicon('video_err_nf')];
        }

        return $array;
    }

    /**
     * Returns video embed URL.
     *
     * @throws RuntimeException if video empty or video provider is not supported
     */
    public function getEmbedUrl(string $videoUrl): string
    {
        if (empty($videoUrl)) {
            throw new RuntimeException($this->lexicon('video_err_ns'));
        }

        if (!preg_match('/^(http|https)\:\/\//i', $videoUrl)) {
            $videoUrl = "https://{$videoUrl}";
        }

        $metadata = $this->getVID($videoUrl);
        if (empty($metadata['v_id'])) {
            throw new RuntimeException($this->lexicon('video_err_nf'));
        }

        switch ($metadata['type']) {
            case 'youtube': // YouTube
                $url = "https://www.youtube.com/embed/{$metadata['v_id']}";

                break;
            case 'vimeo': // Vimeo
                $url = "https://player.vimeo.com/video/{$metadata['v_id']}";

                break;

            default:
                throw new RuntimeException($this->lexicon('video_err_nf'));
        }

        return $url;
    }

    /**
     * Returns the video thubmnail URL.
     *
     * @throws RuntimeException if video empty, video provider is not supported or image is empty
     */
    public function getVideoThumbnailUrl(string $videoUrl): string
    {
        if (empty($videoUrl)) {
            throw new RuntimeException($this->lexicon('video_err_ns'));
        }

        if (!preg_match('/^(http|https)\:\/\//i', $videoUrl)) {
            $videoUrl = "https://{$videoUrl}";
        }

        $metadata = $this->getVID($videoUrl);
        if (empty($metadata['v_id'])) {
            throw new RuntimeException($this->lexicon('video_err_nf'));
        }

        switch ($metadata['type']) {
            case 'youtube': // YouTube
                $image = "https://img.youtube.com/vi/{$metadata['v_id']}/0.jpg";

                break;
            case 'vimeo': // Vimeo
                try {
                    $response = httpGet("https://vimeo.com/api/v2/video/{$metadata['v_id']}.json");
                    $info = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
                    $image = $info[0]['thumbnail_large'] ?? $info[0]['thumbnail_medium'] ?? $info[0]['thumbnail_small'] ?? null;
                } catch (Exception $exception) {
                    throw new RuntimeException($this->lexicon('video_err_nf'), 0, $exception);
                }

                break;

            default:
                throw new RuntimeException($this->lexicon('video_err_nf'));
        }

        if (empty($image)) {
            throw new RuntimeException($this->lexicon('video_err_nf'));
        }

        return $image;
    }

    /*
     * Return video's type and id
     * @param string $video Remote url on video hosting
     * @return array $array Array with type of video and its id
     * */
    public function getVID($video = '')
    {
        if ($this->getVimeoID($video)) {
            return [
                'type' => 'vimeo',
                'v_id' => $this->getVimeoID($video),
            ];
        }
        if ($this->getYoutubeID($video)) {
            return [
                'type' => 'youtube',
                'v_id' => $this->getYoutubeID($video),
            ];
        }

        return false;
    }

    /*
     * Check and return video's id from Vimeo URL
     * @param string $video Remote url on video hosting
     * @return string $matches[1] with id of video
     * */
    public function getVimeoID($video)
    {
        if (preg_match('/[http|https]+:\/\/(?:www\.|)vimeo\.com\/([a-zA-Z0-9_\-]+)(&.+)?/i', $video, $matches)
            || preg_match('/[http|https]+:\/\/player\.vimeo\.com\/video\/([a-zA-Z0-9_\-]+)(&.+)?/i', $video, $matches)) {
            return $matches[1];
        }
    }

    /*
     * Check and return video's id from YouTube URL
     * @param string $video Remote url on video hosting
     * @return string $matches[1] with id of video
     * */
    public function getYoutubeID($video)
    {
        if (preg_match('/[http|https]+:\/\/(?:www\.|)youtube\.com\/watch\?(?:.*)?v=([a-zA-Z0-9_\-]+)/i', $video, $matches)
            || preg_match('/[http|https]+:\/\/(?:www\.|)youtube\.com\/embed\/([a-zA-Z0-9_\-]+)/i', $video, $matches)
            || preg_match('/[http|https]+:\/\/(?:www\.|)youtu\.be\/([a-zA-Z0-9_\-]+)/i', $video, $matches)) {
            return $matches[1];
        }
    }

    /*
     * Download ans save image from remote service
     * @param string $url Remote url
     * @return string $image Url to image or false
     * */
    public function getRemoteImage($url = '')
    {
        if (empty($url)) {
            return false;
        }

        $image = '';
        $response = $this->Curl($url);
        if (!empty($response)) {
            $tmp = explode('.', $url);
            $ext = '.' . end($tmp);

            $filename = md5($url) . $ext;
            if (file_put_contents($this->config['imagesPath'] . $filename, $response)) {
                $image = $this->config['imagesUrl'] . $filename;
            }
        }
        if (empty($image)) {
            $image = $this->config['emptyImage'];
        }

        return $image;
    }

    /**
     * Removes the path prefix from the temporary image path.
     *
     * @param string $imagePath the path to the temp thumb for video
     */
    public function removePathPrefix(string $imagePath): string
    {
        if (false === strpos($imagePath, $this->pathPrefix)) {
            return $imagePath;
        }

        return substr($imagePath, strlen($this->pathPrefix));
    }

    /*
     * Method for loading remote url
     * @param string $url Remote url
     * @return mixed $data Results of an request
     * */
    public function Curl($url = '')
    {
        if (empty($url)) {
            return false;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);

        return curl_exec($ch);
    }

    public function fileExist($url = '')
    {
        $headers = get_headers($url);
        if ('200' != substr($headers[0], 9, 3)) {
            return false;
        }

        return true;
    }
}
