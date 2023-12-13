<?php

use Hoa\Mime\Mime;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\MimeTypes;

/*
* @author Bendiucov Tatiana
* @todo Refactoring [15.12.2021]
* Controller Refactoring
*/
class Download_Controller extends TinyMVC_Controller
{
    public function index()
    {
        show_404();
    }

    public function promo_materials()
    {
        $token = uri()->segment(3);

        if ($token !== config('download_promo_materials_token')) {
            show_404();
        }

        $zipPath = \App\Common\PUBLIC_PATH . '/download/promo-package.zip';

        if (isAjaxRequest()) {
            try {
                if (false === $content = file_get_contents($zipPath)) {
                    throw new RuntimeException("File is empty");
                }

                list('basename' => $name, 'extension' => $extension) = pathinfo($zipPath);
                $encoded = base64_encode($content);
                $mime_type = (new MimeTypes())->guessMimeType($zipPath)
                    ?? Mime::getMimeFromExtension($extension)
                    ?? "application/octet-stream";
            } catch (\Throwable $exception) {
                jsonResponse(
                    translate('systmess_download_zip_file_error_message'),
                    'error',
                    withDebugInformation(array(), array('exception' => throwableToArray($exception)))
                );
            }

            jsonResponse(null, 'success', array(
                'name' => $name,
                'file' => "data:{$mime_type};base64,{$encoded}",
            ));
        }

       file_force_download($zipPath);
    }

    public function shipping_methods(): Response
    {
        return new BinaryFileResponse(
            new File(\App\Common\PUBLIC_PATH . '/download/Full Description of the Shipping Methods.pdf'),
            200,
            ['Content-Type' => 'application/pdf'],
            true,
            'inline'
        );
    }
}
