<?php

use App\Legacy\Upload;
use Psr\Container\ContainerInterface;

/**
 * @author Bendiucov Tatiana
 * @deprecated [03.12.2021]
 * old functionality
 * @see filesystem
 */
class TinyMVC_Library_Upload extends Upload
{

/*
| -------------------------------------------------------------------
| MIME TYPES
| -------------------------------------------------------------------
| This file contains an array of mime types.  It is used by the
| Upload class to help identify allowed file types.
|
*/
private $_mimes =  array(
    'hqx'    =>    array('application/mac-binhex40', 'application/mac-binhex', 'application/x-binhex40', 'application/x-mac-binhex40'),
    'cpt'    =>    'application/mac-compactpro',
    'csv'    =>    array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain'),
    'bin'    =>    array('application/macbinary', 'application/mac-binary', 'application/octet-stream', 'application/x-binary', 'application/x-macbinary'),
    'dms'    =>    'application/octet-stream',
    'lha'    =>    'application/octet-stream',
    'lzh'    =>    'application/octet-stream',
    'exe'    =>    array('application/octet-stream', 'application/x-msdownload'),
    'class'    =>    'application/octet-stream',
    'psd'    =>    array('application/x-photoshop', 'image/vnd.adobe.photoshop'),
    'so'    =>    'application/octet-stream',
    'sea'    =>    'application/octet-stream',
    'dll'    =>    'application/octet-stream',
    'oda'    =>    'application/oda',
    'pdf'    =>    array('application/pdf', 'application/force-download', 'application/x-download', 'binary/octet-stream'),
    'ai'    =>    array('application/pdf', 'application/postscript'),
    'eps'    =>    'application/postscript',
    'ps'    =>    'application/postscript',
    'smi'    =>    'application/smil',
    'smil'    =>    'application/smil',
    'mif'    =>    'application/vnd.mif',
    'xls'    =>    array('application/vnd.ms-excel', 'application/msexcel', 'application/x-msexcel', 'application/x-ms-excel', 'application/x-excel', 'application/x-dos_ms_excel', 'application/xls', 'application/x-xls', 'application/excel', 'application/download', 'application/vnd.ms-office', 'application/msword'),
    'ppt'    =>    array('application/powerpoint', 'application/vnd.ms-powerpoint', 'application/vnd.ms-office', 'application/msword'),
    'pptx'    =>     array('application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/x-zip', 'application/zip'),
    'wbxml'    =>    'application/wbxml',
    'wmlc'    =>    'application/wmlc',
    'dcr'    =>    'application/x-director',
    'dir'    =>    'application/x-director',
    'dxr'    =>    'application/x-director',
    'dvi'    =>    'application/x-dvi',
    'gtar'    =>    'application/x-gtar',
    'gz'    =>    'application/x-gzip',
    'gzip'  =>    'application/x-gzip',
    'php'    =>    array('application/x-httpd-php', 'application/php', 'application/x-php', 'text/php', 'text/x-php', 'application/x-httpd-php-source'),
    'php4'    =>    'application/x-httpd-php',
    'php3'    =>    'application/x-httpd-php',
    'phtml'    =>    'application/x-httpd-php',
    'phps'    =>    'application/x-httpd-php-source',
    'js'    =>    array('application/x-javascript', 'text/plain'),
    'swf'    =>    'application/x-shockwave-flash',
    'sit'    =>    'application/x-stuffit',
    'tar'    =>    'application/x-tar',
    'tgz'    =>    array('application/x-tar', 'application/x-gzip-compressed'),
    'z'    =>    'application/x-compress',
    'xhtml'    =>    'application/xhtml+xml',
    'xht'    =>    'application/xhtml+xml',
    'zip'    =>    array('application/x-zip', 'application/zip', 'application/x-zip-compressed', 'application/s-compressed', 'multipart/x-zip'),
    'rar'    =>    array('application/x-rar', 'application/rar', 'application/x-rar-compressed'),
    'mid'    =>    'audio/midi',
    'midi'    =>    'audio/midi',
    'mpga'    =>    'audio/mpeg',
    'mp2'    =>    'audio/mpeg',
    'mp3'    =>    array('audio/mpeg', 'audio/mpg', 'audio/mpeg3', 'audio/mp3'),
    'aif'    =>    array('audio/x-aiff', 'audio/aiff'),
    'aiff'    =>    array('audio/x-aiff', 'audio/aiff'),
    'aifc'    =>    'audio/x-aiff',
    'ram'    =>    'audio/x-pn-realaudio',
    'rm'    =>    'audio/x-pn-realaudio',
    'rpm'    =>    'audio/x-pn-realaudio-plugin',
    'ra'    =>    'audio/x-realaudio',
    'rv'    =>    'video/vnd.rn-realvideo',
    'wav'    =>    array('audio/x-wav', 'audio/wave', 'audio/wav'),
    'bmp'    =>    array('image/bmp', 'image/x-bmp', 'image/x-bitmap', 'image/x-xbitmap', 'image/x-win-bitmap', 'image/x-windows-bmp', 'image/ms-bmp', 'image/x-ms-bmp', 'application/bmp', 'application/x-bmp', 'application/x-win-bitmap'),
    'gif'    =>    'image/gif',
    'jpeg'    =>    array('image/jpeg', 'image/pjpeg'),
    'jpg'    =>    array('image/jpeg', 'image/pjpeg'),
    'jpe'    =>    array('image/jpeg', 'image/pjpeg'),
    'jp2'    =>    array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
    'j2k'    =>    array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
    'jpf'    =>    array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
    'jpg2'    =>    array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
    'jpx'    =>    array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
    'jpm'    =>    array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
    'mj2'    =>    array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
    'mjp2'    =>    array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
    'png'    =>    array('image/png',  'image/x-png'),
    'tiff'    =>    'image/tiff',
    'tif'    =>    'image/tiff',
    'css'    =>    array('text/css', 'text/plain'),
    'html'    =>    array('text/html', 'text/plain'),
    'htm'    =>    array('text/html', 'text/plain'),
    'shtml'    =>    array('text/html', 'text/plain'),
    'txt'    =>    'text/plain',
    'text'    =>    'text/plain',
    'log'    =>    array('text/plain', 'text/x-log'),
    'rtx'    =>    'text/richtext',
    'rtf'    =>    'text/rtf',
    'xml'    =>    array('application/xml', 'text/xml', 'text/plain'),
    'xsl'    =>    array('application/xml', 'text/xsl', 'text/xml'),
    'mpeg'    =>    'video/mpeg',
    'mpg'    =>    'video/mpeg',
    'mpe'    =>    'video/mpeg',
    'qt'    =>    'video/quicktime',
    'mov'    =>    'video/quicktime',
    'avi'    =>    array('video/x-msvideo', 'video/msvideo', 'video/avi', 'application/x-troff-msvideo'),
    'movie'    =>    'video/x-sgi-movie',
    'doc'    =>    array('application/msword', 'application/vnd.ms-office'),
    'docx'    =>    array('application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/msword', 'application/x-zip'),
    'dot'    =>    array('application/msword', 'application/vnd.ms-office'),
    'dotx'    =>    array('application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/msword'),
    'xlsx'    =>    array('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip', 'application/vnd.ms-excel', 'application/msword', 'application/x-zip'),
    'word'    =>    array('application/msword', 'application/octet-stream'),
    'xl'    =>    'application/excel',
    'eml'    =>    'message/rfc822',
    'json'  =>    array('application/json', 'text/json'),
    'pem'   =>    array('application/x-x509-user-cert', 'application/x-pem-file', 'application/octet-stream'),
    'p10'   =>    array('application/x-pkcs10', 'application/pkcs10'),
    'p12'   =>    'application/x-pkcs12',
    'p7a'   =>    'application/x-pkcs7-signature',
    'p7c'   =>    array('application/pkcs7-mime', 'application/x-pkcs7-mime'),
    'p7m'   =>    array('application/pkcs7-mime', 'application/x-pkcs7-mime'),
    'p7r'   =>    'application/x-pkcs7-certreqresp',
    'p7s'   =>    'application/pkcs7-signature',
    'crt'   =>    array('application/x-x509-ca-cert', 'application/x-x509-user-cert', 'application/pkix-cert'),
    'crl'   =>    array('application/pkix-crl', 'application/pkcs-crl'),
    'der'   =>    'application/x-x509-ca-cert',
    'kdb'   =>    'application/octet-stream',
    'pgp'   =>    'application/pgp',
    'gpg'   =>    'application/gpg-keys',
    'sst'   =>    'application/octet-stream',
    'csr'   =>    'application/octet-stream',
    'rsa'   =>    'application/x-pkcs7',
    'cer'   =>    array('application/pkix-cert', 'application/x-x509-ca-cert'),
    '3g2'   =>    'video/3gpp2',
    '3gp'   =>    array('video/3gp', 'video/3gpp'),
    'mp4'   =>    'video/mp4',
    'm4a'   =>    'audio/x-m4a',
    'f4v'   =>    array('video/mp4', 'video/x-f4v'),
    'flv'    =>    'video/x-flv',
    'webm'    =>    'video/webm',
    'aac'   =>    'audio/x-acc',
    'm4u'   =>    'application/vnd.mpegurl',
    'm3u'   =>    'text/plain',
    'xspf'  =>    'application/xspf+xml',
    'vlc'   =>    'application/videolan',
    'wmv'   =>    array('video/x-ms-wmv', 'video/x-ms-asf'),
    'au'    =>    'audio/x-au',
    'ac3'   =>    'audio/ac3',
    'flac'  =>    'audio/x-flac',
    'ogg'   =>    'audio/ogg',
    'kmz'    =>    array('application/vnd.google-earth.kmz', 'application/zip', 'application/x-zip'),
    'kml'    =>    array('application/vnd.google-earth.kml+xml', 'application/xml', 'text/xml'),
    'ics'    =>    'text/calendar',
    'ical'    =>    'text/calendar',
    'zsh'    =>    'text/x-scriptzsh',
    '7zip'    =>    array('application/x-compressed', 'application/x-zip-compressed', 'application/zip', 'multipart/x-zip'),
    'cdr'    =>    array('application/cdr', 'application/coreldraw', 'application/x-cdr', 'application/x-coreldraw', 'image/cdr', 'image/x-cdr', 'zz-application/zz-winassoc-cdr'),
    'wma'    =>    array('audio/x-ms-wma', 'video/x-ms-asf'),
    'jar'    =>    array('application/java-archive', 'application/x-java-application', 'application/x-jar', 'application/x-compressed'),
    'svg'    =>    array('image/svg+xml', 'application/xml', 'text/xml'),
    'vcf'    =>    'text/x-vcard',
    'srt'    =>    array('text/srt', 'text/plain'),
    'vtt'    =>    array('text/vtt', 'text/plain'),
    'ico'    =>    array('image/x-icon', 'image/x-ico', 'image/vnd.microsoft.icon')
);

    public function __construct()
    {
        // Add some shenanigans with the class intialization.
        // Given that this class is intialized in TWO different wasy with the TWO differents sets of parameters
        // we need to give parnt a proper sets of arguments.
        $args = [array(), 'en_GB'];
        $firstArg = func_get_arg(0);
        if (!$firstArg instanceof ContainerInterface) {
            $args = func_get_args();
        }

        parent::__construct(...$args);
    }

    private function code_to_message($code){
        switch ($code) {
            case 1:
            case UPLOAD_ERR_INI_SIZE:
                $message = "The uploaded file exceeds the ".ini_get('upload_max_filesize');
                break;
            case 2:
            case UPLOAD_ERR_FORM_SIZE:
                $message = "The uploaded file exceeds the ".ini_get('upload_max_filesize');
                break;
            case 3:
            case UPLOAD_ERR_PARTIAL:
                $message = "The uploaded file was only partially uploaded";
                break;
            case 4:
            case UPLOAD_ERR_NO_FILE:
                $message = "No file was uploaded";
                break;
            case 6:
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = "Missing a temporary folder";
                break;
            case 7:
            case UPLOAD_ERR_CANT_WRITE:
                $message = "Failed to write file to disk";
                break;
            case 8:
            case UPLOAD_ERR_EXTENSION:
                $message = "File upload stopped by extension";
                break;

            default:
                $message = "Unknown upload error";
                break;
        }
        return $message;
    }


    function is_landscape($photo){
        return ($photo->img_src_x > $photo->img_src_y) ? true : false;
    }

    private function _validate_file($image, $rules){
        if(empty($rules))
            return true;

        $messages = array(
            'ratio' => $image->file_src_name . ' : The picture dimensions should not be more than 1:[VAL].',
            'size' => $image->file_src_name . ' : The maximum file size has to be [VAL] MB.',
            'height' => $image->file_src_name . ' : The picture height has to be [VAL] pixels.',
            'width' => $image->file_src_name . ' : The picture width has to be [VAL] pixels.',
            'max_height' => $image->file_src_name . ' : The maximum picture height has to be [VAL] pixels.',
            'max_width' => $image->file_src_name . ' : The maximum picture width has to be [VAL] pixels.',
            'min_height' => $image->file_src_name . ' : The minimum picture height has to be [VAL] pixels.',
            'min_width' => $image->file_src_name . ' : The minimum picture width has to be [VAL] pixels.',
            'format' =>  $image->file_src_name . ' : Invalid file format. List of supported formats ([VAL]).',
            'mime-type' =>  $image->file_src_name . ' : File has not available mime-type ([VAL]).'
        );

        $result = array();
        if(!empty($rules['ratio'])
            && (($image->image_src_x / $image->image_src_y) > floatval($rules['ratio']) || ($image->image_src_y / $image->image_src_x) > floatval($rules['ratio']))
        ){
            $result[] = str_replace('[VAL]', $rules['ratio'], $messages['ratio']);
        }

        if(!empty($rules['size']) && $rules['size'] < $image->file_src_size){
            $result[] = str_replace('[VAL]', $rules['size']/1000000, $messages['size']);
        }

        if(!empty($rules['height']) && $rules['height'] !== $image->image_src_y){
            $result[] = str_replace('[VAL]', $rules['height'], $messages['height']);
        }

        if(!empty($rules['width']) && $rules['width'] !== $image->image_src_x){
            $result[] = str_replace('[VAL]', $rules['width'], $messages['width']);
        }

        if(!empty($rules['max_height']) && $rules['max_height'] < $image->image_src_y){
            $result[] = str_replace('[VAL]', $rules['max_height'], $messages['max_height']);
        }

        if(!empty($rules['max_width']) && $rules['max_width'] < $image->image_src_x){
            $result[] = str_replace('[VAL]', $rules['max_width'], $messages['max_width']);
        }

        if(!empty($rules['min_height']) && $rules['min_height'] > $image->image_src_y){
            $result[] = str_replace('[VAL]', $rules['min_height'], $messages['min_height']);
        }

        if(!empty($rules['min_width']) && $rules['min_width'] > $image->image_src_x){
            $result[] = str_replace('[VAL]', $rules['min_width'], $messages['min_width']);
        }

        if(!empty($rules['format'])){
            $allow_formats_array = explode(',', $rules['format']);
            if(!in_array($image->file_src_name_ext, $allow_formats_array)){
                $result[] = str_replace('[VAL]', $rules['format'], $messages['format']);
            }
        }
        if (isset($this->_mimes[$image->file_src_name_ext]))
        {
            $file_mime = $image->file_src_mime;
            if(is_array($this->_mimes[$image->file_src_name_ext])){
                if(!in_array($file_mime, $this->_mimes[$image->file_src_name_ext], TRUE))
                    $result[] = str_replace('[VAL]', $file_mime, $messages['mime-type']);
            } elseif($this->_mimes[$image->file_src_name_ext] !== $file_mime){
                $result[] = str_replace('[VAL]', $file_mime, $messages['mime-type']);
            }
        }

        if(empty($result))
            return true;

        return $result;
    }

    function upload_images_data($conditions){
        $convert = 'jpg';
		$destination = '';
		$use_original_name = false;
        $resize = array(
            'width' => 960,
            'height' => 'R'
        );
        $thumbs = null;
        $watermark = false;
        $watermark_src = 'public/img/watermark.png';
        $watermark_position = 'RB';
        $files = array();
        $rules = array();
        $format_default = 'jpg,jpeg,png,gif,bmp';

        extract($conditions);

        if(!isset($rules['format'])) {
            $rules['format'] = $format_default;
        }

        $images = array();
        if(is_array($files['name'])){
            foreach($files as $propr => $vals){
                foreach($vals as $key => $val){
                    $images[$key][$propr] = $val;
                }
            }
        } else {
            $images[] = $files;
        }

        $res['errors'] = array();
        $handlers = array();

        foreach ($images as $key => $img) {
            if ($img['error']) {
                $res['errors'][] = $img['name'].' : '.$this->code_to_message($img['error']).'.';
                continue;
            }

            $handlers[$key] = new self($img);
            if(($result = $this->_validate_file($handlers[$key], $rules)) !== true || !empty($res['errors'])){
                if($result !== true){
                    $res['errors'] = array_merge($result, $res['errors']);
                }
                continue;
            }
        }

        if(!empty($res['errors']))
            return $res;

        $res = array();

        foreach($handlers as $key=>$handle_one){
            if($handlers[$key]->uploaded){
                $handlers[$key]->image_interlace = true;
                $handlers[$key]->image_resize  = true;
                $handlers[$key]->image_ratio_crop  = true;

                if ($use_original_name) {
                    $new_name = $handlers[$key]->file_src_name_body;
                } else {
                    $new_name = uniqid();
                }

                $handlers[$key]->file_new_name_body = $new_name;
                $res[$key]['old_name'] = $handlers[$key]->file_src_name_body;

                if(!is_null($convert)) $handlers[$key]->image_convert  = $convert;


                $isLandscape = ($handlers[$key]->image_src_x > $handlers[$key]->image_src_y) ? true : false;

                //resize
                if(!is_null($resize)){
                    $xm = $resize['width'];
                    $ym = $resize['height'];

                    if(is_numeric($xm) && is_numeric($ym)){
                        if($isLandscape){
                            $handlers[$key]->image_ratio_x = true;
                            $handlers[$key]->image_y = $ym;
                        }else{
                            $handlers[$key]->image_x = $xm;
                            $handlers[$key]->image_ratio_y = true;
                        }

                    }else{
                        if(is_numeric($xm) && ($handlers[$key]->image_src_x > $xm)){
                            $handlers[$key]->image_x = $xm;
                        }elseif(is_numeric($xm) && $handlers[$key]->image_src_x <= $xm){
                            $handlers[$key]->image_resize  = false;
                            $handlers[$key]->image_ratio_crop  = false;
                        }elseif($xm == 'R'){
                            $handlers[$key]->image_ratio_x = true;
                        }

                        if(is_numeric($ym) && ($handlers[$key]->image_src_y > $ym)){
                            $handlers[$key]->image_y = $ym;
                        }elseif(is_numeric($ym) && ($handlers[$key]->image_src_y <= $ym)){
                            $handlers[$key]->image_resize  = false;
                            $handlers[$key]->image_ratio_crop  = false;
                        }elseif($ym == 'R'){
                            $handlers[$key]->image_ratio_y = true;
                        }
                    }
                }

                if($watermark === true){
                    $handlers[$key]->image_watermark = $watermark_src;
                    $handlers[$key]->image_watermark_position = $watermark_position;
                }

                $handlers[$key]->process($destination);

                if($handlers[$key]->processed){
                    $res[$key]['image_type'] = ($isLandscape) ? 'landscape' : 'portrait';
                    $res[$key]['ok_main'] = 1;
                    $res[$key]['new_name'] = $handlers[$key]->file_dst_name;
                } else{
                    $res[$key]['ok_main'] = 0;
                    if(DEBUG_MODE){
                        $res['errors'][] = $handlers[$key]->error;
                    }
                    $res['errors'][] = 'Image cannot be processed.';
                    continue;
                }

                if(!is_null($thumbs)){
                    foreach($thumbs as $kth => $thumb){
                        $xt = $thumb['w'];
                        $yt = $thumb['h'];
                        $name_th = str_replace('{THUMB_NAME}', $new_name, $thumb['name']);

                        if(!is_null($convert)) $handlers[$key]->image_convert  = $convert;

                        $handlers[$key]->image_resize  = true;
                        $handlers[$key]->image_interlace = true;
                        $handlers[$key]->image_ratio_crop  = true;

                        if(is_numeric($xt) && is_numeric($yt)){
                            if($isLandscape){
                                $handlers[$key]->image_ratio_x = true;
                                $handlers[$key]->image_y = $yt;
                            }else{
                                $handlers[$key]->image_x = $xt;
                                $handlers[$key]->image_ratio_y = true;
                            }
                        }else{
                            if(is_numeric($xt))
                                $handlers[$key]->image_x = $xt;
                            elseif($xt == 'R')
                                $handlers[$key]->image_ratio_x = true;

                            if(is_numeric($yt))
                                $handlers[$key]->image_y = $yt;
                            elseif($yt == 'R')
                                $handlers[$key]->image_ratio_y = true;
                        }

                        $handlers[$key]->file_new_name_body = $name_th;

                        $handlers[$key]->process($destination);

                        if($handlers[$key]->processed){
                            $res[$key]['thumbs'][$kth]['ok_thumb'] = 1;
                            $res[$key]['thumbs'][$kth]['thumb_name'] = $handlers[$key]->file_dst_name;
                            $res[$key]['thumbs'][$kth]['thumb_key'] = $thumb;
                        }else{
                            $res[$key]['thumbs'][$kth]['ok_thumb'] = 0;
                            $res['errors'][] = "{$name_th} don't processed";
                        }
                    }
                }

                $handlers[$key]->clean();
            }
         }

        return $res;
    }

    function copy_images_data($conditions){
        $images = array();
        $destination = '';
        $convert = 'jpg';
        $resize = null;
        $watermark = false;
        $watermark_src = 'public/img/watermark.png';
        $watermark_position = 'RB';
        $thumbs = null;
        $res = array();
        $change_name = true;

        extract($conditions);

        if(!is_array($images)){
            $images = array($images);
        }

        foreach($images as $key => $img){
            $handle = new self($img);

            if($handle->uploaded){
                $info = pathinfo($img);
                if($change_name){
                    $new_name = uniqid();
                } else{
                    $new_name =  basename($img,'.'.$info['extension']);
                }
                $res[$key]['old_name'] = basename($img);

                $handle->file_new_name_body = $new_name;
                if(!is_null($convert)) $handle->image_convert = $convert;

                if(!is_null($resize)){
                    $xm = $resize['width'];
                    $ym = $resize['height'];

                    $handle->image_resize  = true;
                    $handle->image_ratio_crop  = true;

                    $isLandscape = $this->is_landscape($handle);

                    if(is_numeric($xm) && is_numeric($ym)){
                        if($isLandscape){
                            $handle->image_ratio_x = true;
                            $handle->image_y = $ym;
                        }else{
                            $handle->image_x = $xm;
                            $handle->image_ratio_y = true;
                        }

                    }else{
                        if(is_numeric($xm) && ($handle->image_src_x > $xm)){
                            $handle->image_x = $xm;
                        }elseif(is_numeric($xm) && $handle->image_src_x <= $xm){
                            $handle->image_resize  = false;
                            $handle->image_ratio_crop  = false;
                        }elseif($xm == 'R'){
                            $handle->image_ratio_x = true;
                        }

                        if(is_numeric($ym) && ($handle->image_src_y > $ym)){
                            $handle->image_y = $ym;
                        }elseif(is_numeric($ym) && ($handle->image_src_y <= $ym)){
                            $handle->image_resize  = false;
                            $handle->image_ratio_crop  = false;
                        }elseif($ym == 'R'){
                            $handle->image_ratio_y = true;
                        }
                    }
                }

                if($watermark === true){
                    $handle->image_watermark = $watermark_src;
                    $handle->image_watermark_position = $watermark_position;
                }
                $handle->image_interlace = true;
                $handle->process($destination);

                if($handle->processed){
                    $res[$key]['image_type'] = ($isLandscape) ? 'landscape' : 'portrait';
                    $res[$key]['ok_main'] = 1;
                    $res[$key]['new_name'] = $handle->file_dst_name;
                } else{
                    $res[$key]['ok_main'] = 0;
                    continue;
                }

                if(!is_null($thumbs)){
                    $res[$key]['thumbs'] = $this->create_thumbs_data($img, $res[$key]['new_name'], $conditions);
                }

                $handle->clean();
            } else{
                $res['errors'][] = $this->code_to_message(UPLOAD_ERR_NO_FILE.'1');
            }
        }

        return $res;
    }

    function create_thumbs_data($img, $file_name = '', $conditions = array()){
        if($file_name == ''){
            $file_name = basename($img);
        }

        extract($conditions);

        $handle = new self($img);
        $isLandscape = $this->is_landscape($handle);
        $res = array();

        if(!$handle->uploaded){
            $res['errors'][] = $this->code_to_message(UPLOAD_ERR_NO_FILE.'2');
            return $res;
        }

        $image_info = pathinfo($destination . '/' . $file_name);

        foreach($thumbs as $kth => $thumb){
            $xt = $thumb['w'];
            $yt = $thumb['h'];
            $name_th = str_replace('{THUMB_NAME}', $image_info['filename'], $thumb['name']);

            $handle->image_resize  = true;
            $handle->image_interlace = true;
            $this->png_compression = 9;
            $this->jpeg_quality = 45;
            $handle->image_ratio_crop  = true;

            if(is_numeric($xt) && is_numeric($yt)){
                if($isLandscape){
                    $handle->image_ratio_x = true;
                    $handle->image_y = $yt;
                }else{
                    $handle->image_x = $xt;
                    $handle->image_ratio_y = true;
                }
            }else{
                if(is_numeric($xt))
                    $handle->image_x = $xt;
                elseif($xt == 'R')
                    $handle->image_ratio_x = true;

                if(is_numeric($yt))
                    $handle->image_y = $yt;
                elseif($yt == 'R')
                    $handle->image_ratio_y = true;
            }

            $handle->file_new_name_body = $name_th;

            $handle->process($destination);

            if($handle->processed){
                $res[$kth]['ok_thumb'] = 1;
                $res[$kth]['thumb_name'] = $handle->file_dst_name;
                $res[$kth]['thumb_key'] = $thumb;
            }else{
                $res[$kth]['ok_thumb'] = 0;
            }
        }

        return $res;
    }

    function upload_images($files, $destination, $convert=null, $resize=null, $thumbs=null, $allow_formats='jpg,png,gif,bmp'){
         if(is_array($files['name'])){
            foreach($files as $propr => $vals){
                foreach($vals as $key => $val){
                    $images[$key][$propr] = $val;
                }
            }
        }else{
            $images[] = $files;
        }
        $allow_formats_array = explode(',',$allow_formats);
         foreach($images as $key => $img){
                if($img['error']){
                    $res['errors'][] = $img['name'].' Error: '.$this->code_to_message($img['error']).'.';
                    continue;
                }
                $new_name = uniqid();
                $handle = new self($img);
                //print_r($img);
                if($handle->uploaded){
                    //echo 567;
                    $res[$key]['old_name'] = $img['name'];
                    //echo $handle->image_src_type;

                    if(!in_array($handle->image_src_type, $allow_formats_array)){
                        $res['errors'][] = $img['name'].' has not available formats ('.$allow_formats.').';
                        continue;
                    }

                    $params = explode("x", $resize);
                    $xm = $params[0];
                    $ym = $params[1];

                    $handle->image_resize  = true;
                    $handle->image_ratio_crop  = true;

                    $handle->file_new_name_body = $new_name;

                    if(!is_null($convert)) $handle->image_convert  = $convert;

                    $isLandscape = ($handle->image_src_x > $handle->image_src_y) ? true : false;

                    //resize
                    if(is_numeric($xm) && is_numeric($ym)){
                        if($isLandscape){
                            $handle->image_ratio_x = true;
                            $handle->image_y = $ym;
                        }else{
                            $handle->image_x = $xm;
                            $handle->image_ratio_y = true;
                        }

                    }else{
                        if(is_numeric($xm) && ($handle->image_src_x > $xm)){
                            $handle->image_x = $xm;
                        }elseif(is_numeric($xm) && $handle->image_src_x <= $xm){
                            $handle->image_resize  = false;
                            $handle->image_ratio_crop  = false;
                        }elseif($xm == 'R'){
                            $handle->image_ratio_x = true;
                        }

                        if(is_numeric($ym) && ($handle->image_src_y > $ym)){
                            $handle->image_y = $ym;
                        }elseif(is_numeric($ym) && ($handle->image_src_y <= $ym)){
                            $handle->image_resize  = false;
                            $handle->image_ratio_crop  = false;
                        }elseif($ym == 'R'){
                            $handle->image_ratio_y = true;
                        }
                    }
                    $handle->process($destination);

                    if($handle->processed){
                        $res[$key]['ok_main'] = 1;
                        $res[$key]['new_name'] = $handle->file_dst_name;
                    } else{
                        $res[$key]['ok_main'] = 0;
                        $res['errors'][] = 'Image cannot be processed.';
                        continue;
                    }

                    //thumbs
                    if(!is_null($thumbs)){
                        $thumbs_apart = explode(",", $thumbs);
                        foreach($thumbs_apart as $kth => $thumb){
                            $params = explode("x", $thumb);
                            $xt = $params[0];
                            $yt = $params[1];

                            if(!is_null($convert)) $handle->image_convert  = $convert;

                            $handle->image_resize  = true;
                            $handle->image_ratio_crop  = true;

                            if(is_numeric($xt) && is_numeric($yt)){
                                if($isLandscape){
                                    $handle->image_ratio_x = true;
                                    $handle->image_y = $yt;
                                }else{
                                    $handle->image_x = $xt;
                                    $handle->image_ratio_y = true;
                                }
                            }else{
                                if(is_numeric($xt))
                                    $handle->image_x = $xt;
                                elseif($xt == 'R')
                                    $handle->image_ratio_x = true;

                                if(is_numeric($yt))
                                    $handle->image_y = $yt;
                                elseif($yt == 'R')
                                    $handle->image_ratio_y = true;
                            }

                            $handle->file_new_name_body = $new_name;
                            $handle->file_name_body_pre = 'thumb_'.$thumb.'_';

                            $handle->process($destination);

                            if($handle->processed){
                                $res[$key]['thumbs'][$kth]['ok_thumb'] = 1;
                                $res[$key]['thumbs'][$kth]['thumb_name'] = $handle->file_dst_name;
                                $res[$key]['thumbs'][$kth]['thumb_key'] = $thumb;
                            }else{
                                $res[$key]['thumbs'][$kth]['ok_thumb'] = 0;
                                $res['errors'][] = 'thumb_' .$xt.'x'.$yt.' don\'t processed';
                            }
                        }
                    }
                    $handle->clean();
                }
             }
             return $res;
    }

    function copy_images($images, $destination, $convert=null, $resize=null, $thumbs=null){
        foreach($images as $key => $img){
            $handle = new self($img);

            if($handle->uploaded){
                $new_name = uniqid();
                $res[$key]['old_name'] = basename($img);

                $handle->file_new_name_body = $new_name;
                if(!is_null($convert)) $handle->image_convert  = $convert;

                if(!is_null($resize)){
                    $params = explode("x", $resize);
                    $xm = $params[0];
                    $ym = $params[1];

                    $handle->image_resize  = true;
                    $handle->image_ratio_crop  = true;

                    $isLandscape = $this->is_landscape($handle);

                    if(is_numeric($xm) && is_numeric($ym)){
                        if($isLandscape){
                            $handle->image_ratio_x = true;
                            $handle->image_y = $ym;
                        }else{
                            $handle->image_x = $xm;
                            $handle->image_ratio_y = true;
                        }

                    }else{
                        if(is_numeric($xm) && ($handle->image_src_x > $xm)){
                            $handle->image_x = $xm;
                        }elseif(is_numeric($xm) && $handle->image_src_x <= $xm){
                            $handle->image_resize  = false;
                            $handle->image_ratio_crop  = false;
                        }elseif($xm == 'R'){
                            $handle->image_ratio_x = true;
                        }

                        if(is_numeric($ym) && ($handle->image_src_y > $ym)){
                            $handle->image_y = $ym;
                        }elseif(is_numeric($ym) && ($handle->image_src_y <= $ym)){
                            $handle->image_resize  = false;
                            $handle->image_ratio_crop  = false;
                        }elseif($ym == 'R'){
                            $handle->image_ratio_y = true;
                        }
                    }
                }
                $handle->process($destination);

                if($handle->processed){
                    $res[$key]['ok_main'] = 1;
                    $res[$key]['new_name'] = $handle->file_dst_name;
                } else{
                    $res[$key]['ok_main'] = 0;
                    continue;
                }

                if(!is_null($thumbs)){
                    $thumbs_apart = explode(",", $thumbs);
                    foreach($thumbs_apart as $kth => $thumb){
                        $params = explode("x", $thumb);
                        $xt = $params[0];
                        $yt = $params[1];

                        if(!is_null($convert)) $handle->image_convert  = $convert;

                        $handle->image_resize  = true;
                        $handle->image_ratio_crop  = true;

                        if(is_numeric($xt) && is_numeric($yt)){
                            if($isLandscape){
                                $handle->image_ratio_x = true;
                                $handle->image_y = $yt;
                            }else{
                                $handle->image_x = $xt;
                                $handle->image_ratio_y = true;
                            }
                        }else{
                            if(is_numeric($xt))
                                $handle->image_x = $xt;
                            elseif($xt == 'R')
                                $handle->image_ratio_x = true;

                            if(is_numeric($yt))
                                $handle->image_y = $yt;
                            elseif($yt == 'R')
                                $handle->image_ratio_y = true;
                        }
                        $handle->file_new_name_body = $new_name;
                        $handle->file_name_body_pre = 'thumb_'.$thumb.'_';
                        $handle->process($destination);

                        if($handle->processed){
                            $res[$key]['thumbs'][$kth]['ok_thumb'] = 1;
                            $res[$key]['thumbs'][$kth]['thumb_name'] = $handle->file_dst_name;
                            $res[$key]['thumbs'][$kth]['thumb_key'] = $thumb;
                        }else{
                            $res[$key]['thumbs'][$kth]['ok_thumb'] = 0;
                        }
                    }
                }
                $handle->clean();
            }
        }
        return $res;
    }

    function upload_files($data_files, $destination, $size = 1024, $allow_formats='pdf,doc,docx,xls,xlsx,tif,tiff'){

        if(is_array($data_files['name'])){
            foreach($data_files as $propr => $vals){
                foreach($vals as $key => $val){
                    $files[$key][$propr] = $val;
                }
            }
        }else{
            $files[] = $data_files;
        }

        $allow_formats_array = explode(',',$allow_formats);
        foreach($files as $key => $file){
            $new_name = uniqid();

            $handle = new self($file);

            if($handle->uploaded){
                $res[$key]['old_name'] = $file['name'];
                if(!in_array($handle->file_src_name_ext, $allow_formats_array)){
                    $res['errors'][] = $file['name'].' has not available formats ('.$allow_formats.').';
                    continue;
                }
                $permitedSize = (int)$size * 1024;
                if($handle->file_src_size > $permitedSize){
                    $res['errors'][] = $file['name'].' has excide the maximum allowed size('.$size.').';
                    continue;
                }

                $handle->file_new_name_body = $new_name;

                $handle->process($destination);

                if($handle->processed){
                    $res[$key]['ok_main'] = 1;
                    $res[$key]['new_name'] = $handle->file_dst_name;
                    $res[$key]['type'] = $handle->file_src_name_ext;
                } else{
                    $res[$key]['ok_main'] = 0;
                    $res['errors'][] = 'Image cannot be processed.';
                    continue;
                }

                $handle->clean();
            }
        }
        return $res;
    }

    function copy_files($files, $destination, $preserve_original = false){
        $res = array();
        foreach($files as $key => $file){
            $handle = new self($file);

            if($handle->uploaded){
                $new_name = uniqid();
                $res[$key]['old_name'] = basename($file);

                $handle->file_new_name_body = $new_name;

                $handle->process($destination);

                if($handle->processed){
                    $res[$key]['ok_main'] = 1;
                    $res[$key]['new_name'] = $handle->file_dst_name;
                    $res[$key]['type'] = $handle->file_src_name_ext;
                    $res[$key]['size'] = $handle->file_src_size;
                } else{
                    $res[$key]['ok_main'] = 0;
                    continue;
                }

                if (!$preserve_original) {
                    $handle->clean();
                }
            }
        }
        return $res;
    }

    function upload_images_new($conditions){
        $convert = 'jpg';
		$destination = '';
		$use_original_name = false;
        $resize = '960xR';
        $thumbs = null;
        $watermark = false;
        $watermark_src = 'public/img/watermark.png';
        $watermark_position = 'RB';
        $files = array();
        $rules = array();
        $format_default = 'jpg,jpeg,png,gif,bmp';
        extract($conditions);

        if(!isset($rules['format'])) {
            $rules['format'] = $format_default;
        }


        $images = array();
        if(is_array($files['name'])){
            foreach($files as $propr => $vals){
                foreach($vals as $key => $val){
                    $images[$key][$propr] = $val;
                }
            }
        } else {
            $images[] = $files;
        }

        $res['errors'] = array();
        $handlers = array();

        foreach ($images as $key => $img) {
            if ($img['error']) {
                $res['errors'][] = $img['name'].' : '.$this->code_to_message($img['error']).'.';
                continue;
            }

            $handlers[$key] = new self($img);
            // $handlers[$key]->init();
            if(($result = $this->_validate_file($handlers[$key], $rules)) !== true || !empty($res['errors'])){
                if($result !== true){
                    $res['errors'] = array_merge($result, $res['errors']);
                }
                continue;
            }
        }

        if(!empty($res['errors']))
            return $res;

        $res = array();

        foreach($handlers as $key=>$handle_one){
            if($handlers[$key]->uploaded){
                $handlers[$key]->image_interlace = true;
                $handlers[$key]->image_resize  = true;
                $handlers[$key]->image_ratio_crop  = true;

                if ($use_original_name) {
                    $new_name = $handlers[$key]->file_src_name_body;
                } else {
                    $new_name = uniqid();
                }

                $handlers[$key]->file_new_name_body = $new_name;
                $res[$key]['old_name'] = $handlers[$key]->file_src_name_body;

                if(!is_null($convert)) $handlers[$key]->image_convert  = $convert;


                $isLandscape = ($handlers[$key]->image_src_x > $handlers[$key]->image_src_y) ? true : false;

                //resize
                if(!is_null($resize)){
                    $params = explode("x", $resize);
                    $xm = $params[0];
                    $ym = $params[1];

                    if(is_numeric($xm) && is_numeric($ym)){
                        if($isLandscape){
                            $handlers[$key]->image_ratio_x = true;
                            $handlers[$key]->image_y = $ym;
                        }else{
                            $handlers[$key]->image_x = $xm;
                            $handlers[$key]->image_ratio_y = true;
                        }

                    }else{
                        if(is_numeric($xm) && ($handlers[$key]->image_src_x > $xm)){
                            $handlers[$key]->image_x = $xm;
                        }elseif(is_numeric($xm) && $handlers[$key]->image_src_x <= $xm){
                            $handlers[$key]->image_resize  = false;
                            $handlers[$key]->image_ratio_crop  = false;
                        }elseif($xm == 'R'){
                            $handlers[$key]->image_ratio_x = true;
                        }

                        if(is_numeric($ym) && ($handlers[$key]->image_src_y > $ym)){
                            $handlers[$key]->image_y = $ym;
                        }elseif(is_numeric($ym) && ($handlers[$key]->image_src_y <= $ym)){
                            $handlers[$key]->image_resize  = false;
                            $handlers[$key]->image_ratio_crop  = false;
                        }elseif($ym == 'R'){
                            $handlers[$key]->image_ratio_y = true;
                        }
                    }
                }

                if($watermark === true){
                    $handlers[$key]->image_watermark = $watermark_src;
                    $handlers[$key]->image_watermark_position = $watermark_position;
                }

                $handlers[$key]->process($destination);

                if($handlers[$key]->processed){
                    $res[$key]['image_type'] = ($isLandscape) ? 'landscape' : 'portrait';
                    $res[$key]['ok_main'] = 1;
                    $res[$key]['new_name'] = $handlers[$key]->file_dst_name;
                } else{
                    $res[$key]['ok_main'] = 0;
                    if(DEBUG_MODE){
                        $res['errors'][] = $handlers[$key]->error;
                    }
                    $res['errors'][] = 'Image cannot be processed.';
                    continue;
                }

                //thumbs
                if(!is_null($thumbs)){
                    $thumbs_apart = explode(",", $thumbs);
                    foreach($thumbs_apart as $kth => $thumb){
                        $params = explode("x", $thumb);
                        $xt = $params[0];
                        $yt = $params[1];

                        if(!is_null($convert)) $handlers[$key]->image_convert  = $convert;

                        $handlers[$key]->image_resize  = true;
                        $handlers[$key]->image_interlace = true;
                        $handlers[$key]->image_ratio_crop  = true;

                        if(is_numeric($xt) && is_numeric($yt)){
                            if($isLandscape){
                                $handlers[$key]->image_ratio_x = true;
                                $handlers[$key]->image_y = $yt;
                            }else{
                                $handlers[$key]->image_x = $xt;
                                $handlers[$key]->image_ratio_y = true;
                            }
                        }else{
                            if(is_numeric($xt))
                                $handlers[$key]->image_x = $xt;
                            elseif($xt == 'R')
                                $handlers[$key]->image_ratio_x = true;

                            if(is_numeric($yt))
                                $handlers[$key]->image_y = $yt;
                            elseif($yt == 'R')
                                $handlers[$key]->image_ratio_y = true;
                        }

                        $handlers[$key]->file_new_name_body = $new_name;
                        $handlers[$key]->file_name_body_pre = 'thumb_'.$thumb.'_';

                        $handlers[$key]->process($destination);

                        if($handlers[$key]->processed){
                            $res[$key]['thumbs'][$kth]['ok_thumb'] = 1;
                            $res[$key]['thumbs'][$kth]['thumb_name'] = $handlers[$key]->file_dst_name;
                            $res[$key]['thumbs'][$kth]['thumb_key'] = $thumb;
                        }else{
                            $res[$key]['thumbs'][$kth]['ok_thumb'] = 0;
                            $res['errors'][] = 'thumb_' .$xt.'x'.$yt.' don\'t processed';
                        }
                    }
                }

                $handlers[$key]->clean();
            }
         }

        return $res;
    }

    function copy_images_new($conditions){
        $images = array();
        $destination = '';
        $convert = 'jpg';
        $interlace = true;
        $resize = null;
        $watermark = false;
        $watermark_src = 'public/img/watermark.png';
        $watermark_position = 'RB';
        $thumbs = null;
        $res = array();
        $change_name = true;
        $delete_original = true;

        extract($conditions);

        foreach($images as $key => $img){
            $handle = new self($img);

            if($handle->uploaded){
                $info = pathinfo($img);
                if($change_name){
                    $new_name = uniqid();
                } else{
                    $new_name =  basename($img,'.'.$info['extension']);
                }
                $res[$key]['old_name'] = basename($img);

                $handle->file_new_name_body = $new_name;
                if(!is_null($convert)) $handle->image_convert = $convert;

                if(!is_null($resize)){
                    $params = explode("x", $resize);
                    $xm = $params[0];
                    $ym = $params[1];

                    $handle->image_resize  = true;
                    $handle->image_ratio_crop  = true;

                    $isLandscape = $this->is_landscape($handle);

                    if(is_numeric($xm) && is_numeric($ym)){
                        if($isLandscape){
                            $handle->image_ratio_x = true;
                            $handle->image_y = $ym;
                        }else{
                            $handle->image_x = $xm;
                            $handle->image_ratio_y = true;
                        }

                    }else{
                        if(is_numeric($xm) && ($handle->image_src_x > $xm)){
                            $handle->image_x = $xm;
                        }elseif(is_numeric($xm) && $handle->image_src_x <= $xm){
                            $handle->image_resize  = false;
                            $handle->image_ratio_crop  = false;
                        }elseif($xm == 'R'){
                            $handle->image_ratio_x = true;
                        }

                        if(is_numeric($ym) && ($handle->image_src_y > $ym)){
                            $handle->image_y = $ym;
                        }elseif(is_numeric($ym) && ($handle->image_src_y <= $ym)){
                            $handle->image_resize  = false;
                            $handle->image_ratio_crop  = false;
                        }elseif($ym == 'R'){
                            $handle->image_ratio_y = true;
                        }
                    }
                }

                if($watermark === true){
                    $handle->image_watermark = $watermark_src;
                    $handle->image_watermark_position = $watermark_position;
                }
                if ($interlace) {
                    $handle->image_interlace = true;
                }
                $handle->process($destination);

                if($handle->processed){
                    $res[$key]['ok_main'] = 1;
                    $res[$key]['new_name'] = $handle->file_dst_name;
                } else{
                    $res[$key]['ok_main'] = 0;
                    continue;
                }

                if(!is_null($thumbs)){
                    $res[$key]['thumbs'] = $this->create_thumbs($img, $res[$key]['new_name'], $conditions);
                }

                if ($delete_original) {
                    $handle->clean();
                }
            } else{
                $res['errors'][] = $this->code_to_message(UPLOAD_ERR_NO_FILE);
            }
        }

        return $res;
    }

    function create_thumbs($img, $file_name = '', $conditions = array()){
        if($file_name == ''){
            $file_name = basename($img);
        }

        extract($conditions);

        $handle = new self($img);
        $isLandscape = $this->is_landscape($handle);
        $res = array();

        if(!$handle->uploaded){
            $res['errors'][] = $this->code_to_message(UPLOAD_ERR_NO_FILE);
            return $res;
        }

        $image_info = pathinfo($destination . '/' . $file_name);

        $thumbs_apart = explode(",", $thumbs);
        foreach($thumbs_apart as $kth => $thumb){
            $params = explode("x", $thumb);
            $xt = $params[0];
            $yt = $params[1];

            $handle->image_resize  = true;
            $handle->image_interlace = true;
            $this->png_compression = 9;
            $this->jpeg_quality = 45;
            $handle->image_ratio_crop  = true;

            if(is_numeric($xt) && is_numeric($yt)){
                if($isLandscape){
                    $handle->image_ratio_x = true;
                    $handle->image_y = $yt;
                }else{
                    $handle->image_x = $xt;
                    $handle->image_ratio_y = true;
                }
            }else{
                if(is_numeric($xt))
                    $handle->image_x = $xt;
                elseif($xt == 'R')
                    $handle->image_ratio_x = true;

                if(is_numeric($yt))
                    $handle->image_y = $yt;
                elseif($yt == 'R')
                    $handle->image_ratio_y = true;
            }
            $handle->file_new_name_body = $image_info['filename'];
            $handle->file_name_body_pre = 'thumb_'.$thumb.'_';
            $handle->process($destination);

            if($handle->processed){
                $res[$kth]['ok_thumb'] = 1;
                $res[$kth]['thumb_name'] = $handle->file_dst_name;
                $res[$kth]['thumb_key'] = $thumb;
            }else{
                $res[$kth]['ok_thumb'] = 0;
            }
        }

        return $res;
    }

    function upload_files_new($conditions){
        $convert = null;
        $resize = null;
        $thumbs = null;
        $format_default = 'pdf,doc,docx,xls,xlsx,tif,tiff';
        extract($conditions);
        if(!isset($rules['format']))
            $rules['format'] = $format_default;

        if(is_array($data_files['name'])){
            foreach($data_files as $propr => $vals){
                foreach($vals as $key => $val){
                    $files[$key][$propr] = $val;
                }
            }
        }else{
            $files[] = $data_files;
        }

        $res['errors'] = array();
        $handlers = array();

        foreach($files as $key => $file){
            if($file['error']){
                $res['errors'][] = $file['name'].' : '.$this->code_to_message($file['error']).'.';
                continue;
            }

            $handlers[$key] = new self($file);

            if(($result = $this->_validate_file($handlers[$key], $rules)) !== true || !empty($res['errors'])){
                if($result !== true){
                    $res['errors'] = array_merge($result, $res['errors']);
                }
                continue;
            }
        }

        if(!empty($res['errors']))
            return $res;

        $res = array();

        foreach($handlers as $key=>$handle_one){
            $new_name = uniqid();
            if($handlers[$key]->uploaded){
                $res[$key]['old_name'] = $files[$key]['name'];
                $handlers[$key]->file_new_name_body = $new_name;

                $handlers[$key]->process($path);

                if($handlers[$key]->processed){
                    $res[$key]['ok_main'] = 1;
                    $res[$key]['new_name'] = $handlers[$key]->file_dst_name;
                    $res[$key]['type'] = $handlers[$key]->file_src_name_ext;
                    $res[$key]['size'] = $handlers[$key]->file_src_size;
                } else{
                    $res[$key]['ok_main'] = 0;
                    $res['errors'][] = 'Image cannot be processed.';
                    continue;
                }

                $handlers[$key]->clean();
            }
        }
        return $res;
    }

    public function get_instance($files){
        return new self($files);
    }


    function check_remote_file($params = array()) {
        extract($params);
        if(!isset($remote_path)){
            return false;
        }

        $mime = getimagesize($remote_path);
        $mime = $mime['mime'];
        if(empty($extensions))
            $extensions = array('jpg','png','gif','jpeg');

        $extension = strtolower( pathinfo( $remote_path, PATHINFO_EXTENSION ) );

        if (in_array( $extension , $extensions ) && isset($this->_mimes[$extension])){
            if(is_array($this->_mimes[$extension])){
                if(!in_array($mime, $this->_mimes[$extension], TRUE))
                    return false;
            } elseif($this->_mimes[$extension] !== $mime){
                return false;
            }
        } else{
            return false;
        }

        return true;
    }

    function get_remote_file_ext($params = array()) {
        extract($params);
        if(!isset($remote_path)){
            return false;
        }

        return strtolower( pathinfo( $remote_path, PATHINFO_EXTENSION ) );
    }

    function get_remote_file($remote_url, $local_url){
        $fp = fopen ($_SERVER['DOCUMENT_ROOT'] . '/' . $local_url, 'w+');
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $remote_url );
        curl_setopt( $ch, CURLOPT_BINARYTRANSFER, true );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, false );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );

        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
        curl_setopt( $ch, CURLOPT_FILE, $fp );
        curl_exec( $ch );
        curl_close( $ch );
        fclose( $fp );

        if (filesize($local_url) > 0)
            return true;
    }

    function file_rename($path, $old_name, $new_name){
        if(!is_file($path . $old_name))
            return false;

        if(!rename($path . $old_name, $path . $new_name))
            return false;

        return true;
    }
}
