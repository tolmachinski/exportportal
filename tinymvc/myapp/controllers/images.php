<?php

use Hoa\File\Read;
use Hoa\Mime\Mime;

/**
 * Controller Images.
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 *
 * @property \TinyMVC_Load              $load
 * @property \TinyMVC_View              $view
 * @property \TinyMVC_Library_URI       $uri
 * @property \TinyMVC_Library_Session   $session
 * @property \TinyMVC_Library_Cookies   $cookies
 * @property \TinyMVC_Library_Upload    $upload
 * @property \TinyMVC_Library_validator $validator
 * @property \Translations_Model        $translations
 */
class Images_Controller extends TinyMVC_Controller
{
    public function avatar()
    {
        $user_id = (int) $this->uri->segment(3);
        $photo = base64_decode(rawurldecode($this->uri->segment(4)));
        $image_path = getDisplayImageLink(array('{ID}' => $user_id, '{FILE_NAME}' => $photo), 'users.main', array( 'thumb_size' => 0 ));
        $mimetype = new Mime(new Read($image_path));
        $content = file_get_contents($image_path);
        $encoded = gzencode($content);
        $filesize = strlen($encoded);
        $modified = filemtime($image_path);
        $expires = 3600 * 24;

        header("Content-Type: image/{$mimetype->getType()}; charset=utf-8", true, 200);
        header("Content-Length: {$filesize}");
        header("Content-Encoding: gzip");
        header('Content-Disposition: inline');
        header("Cache-Control: public, max-age={$expires}, must-revalidate");
        header("Pragma: cache");
        header("Expires: " . gmdate('D, d M Y H:i:s \G\M\T', time() + $expires));
        header("Last-Modified: " . gmdate('D, d M Y H:i:s \G\M\T', $modified));
        header("Timing-Allow-Origin: *");
        header("Vary: Accept-Encoding");
        header("X-Content-Type-Options: no-sniff");
        print($encoded);
    }
}

// End of file images.php
// Location: /tinymvc/myapp/controllers/images.php
