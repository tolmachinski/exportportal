<?php

namespace App\Common\Traits;

use Hoa\Mime\Mime;

trait FileuploadOptionsAwareTrait
{
    /**
     * Returns the fileupload options.
     *
     * @param array       $extensions
     * @param int         $total
     * @param int         $allowed
     * @param int         $size
     * @param null|string $sizePlaceholder
     * @param array       $imageOptions
     * @param null|string $uploadUrl
     * @param null|string $deleteUrl
     *
     * @return array
     */
    private function getFileuploadOptions(
        array $extensions = array(),
        $total = 0,
        $allowed = 0,
        $size = 0,
        $sizePlaceholder = null,
        array $imageOptions = array(),
        $uploadUrl = null,
        $deleteUrl = null
    ) {
        $mimeTypes = $this->getMimeTypesFromExtensions($extensions);
        $acceptedTypes = $this->getFileuploadAcceptedTypes($mimeTypes, $extensions);
        $uploadFolder = $this->getFileuploadFolder();
        $urls = array_filter(array(
            'upload' => $uploadUrl,
            'delete' => $deleteUrl,
        ));

        return array(
            'directory' => $uploadFolder,
            'limits'    => array(
                'amount'   => array(
                    'total'     => (int) $total,
                    'allowed'   => (int) $allowed,
                ),
                'image'    => $imageOptions,
                'type'     => array(
                    'accept'            => $acceptedTypes,
                    'mimetypes'         => $mimeTypes,
                    'extensions'        => $extensions,
                ),
                'filesize' => array(
                    'size'        => (int) $size,
                    'placeholder' => (string) $sizePlaceholder,
                ),
                'maxRowsItems' => (int) config('item_drafts_allowed_lines_amount', 100),
            ),
            'urls'      => array_filter(array_map(function ($url) use ($uploadFolder) { return "{$url}/{$uploadFolder}"; }, $urls)),
        );
    }

    /**
     * Returns the upload folder path.
     *
     * @return string
     */
    private function getFileuploadFolder()
    {
        return encriptedFolderName();
    }

    /**
     * Retruns the value for the 'accept' attribute.
     *
     * @param array $mimes
     * @param array $fallbackExtensions
     *
     * @return string
     */
    private function getFileuploadAcceptedTypes(array $mimes = array(), array $extensions = array())
    {
        return implode(
            ',',
            array_filter(array(
                $this->getAcceptedTypesFromMimeTypes(array_unique($mimes)),
                $this->getAcceptedTypesFromExtensions(array_unique($extensions)),
            ))
        );
    }

    /**
     * Returns the list of mimetypes for provided extensions.
     *
     * @param array $extensions
     *
     * @return array
     */
    private function getMimeTypesFromExtensions(array $extensions = array())
    {
        if (empty($extensions)) {
            return array();
        }

        return array_filter(array_unique(array_map(
            function ($extension) { return Mime::getMimeFromExtension($extension); },
            $extensions
        )));
    }

    /**
     * Returns the list of extensions.
     *
     * @param array $extensions
     *
     * @return string
     */
    private function getAcceptedTypesFromExtensions(array $extensions = array())
    {
        if (empty($extensions)) {
            return null;
        }

        return implode(
            ',',
            array_filter(array_map(
                function ($extension) {
                    if (null === $extension) {
                        return null;
                    }

                    if (0 !== strpos($extension, '.')) {
                        $extension = ".{$extension}";
                    }

                    return $extension;
                },
                $extensions
            ))
        );
    }

    /**
     * Returns the list of mime-types.
     *
     * @param array $mimes
     *
     * @return string
     */
    private function getAcceptedTypesFromMimeTypes(array $mimes = array())
    {
        if (empty($mimes)) {
            return null;
        }

        return implode(',', array_filter($mimes));
    }
}
