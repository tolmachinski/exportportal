<?php

namespace App\Common\Traits;

trait ModalUriReferenceTrait
{
    /**
     * Returns parsed query paramters for return URL reference.
     *
     * @return array
     */
    private function getParsedUriReference()
    {
        if (!arrayHas($_GET, 'type') || !arrayHas($_GET, 'path')) {
            return null;
        }

        $isModal = $this->hasModalUriParameter();
        $path = base64UrlDecode(cleanInput(arrayGet($_GET, 'path')));
        $type = cleanInput(arrayGet($_GET, 'type'));
        $params = array_map(
            function ($paramter) { return base64UrlDecode(cleanInput($paramter)); },
            array_filter(
                with(
                    arrayGet($_GET, 'params', array()),
                    function ($params) { return is_array($params) ? $params : (array) $params; }
                )
            )
        );
        $url = $this->resolveUriReferenceType($type, $path, $params);
        $title = cleanInput(arrayGet($_GET, 'title'));
        $modalTitle = $isModal ? cleanInput(arrayGet($_GET, 'modal_title')) : null;

        return array(
            'url'     => null !== $url ? $url : __SITE_URL . ltrim($path, '/'),
            'options' => arrayGet($_GET, 'options', array()),
            'titles'  => array_filter(array(
                'link'  => $title,
                'modal' => $isModal ? $modalTitle : null,
            )),
        );
    }

    /**
     * Returns the query paramters string for return URL reference from the $_GET global variable.
     *
     * @return string
     */
    private function getUriReferenceQuery()
    {
        if (!$this->hasModalUriParameter()) {
            return null;
        }

        $keys = array(
            'modal_title',
            'options',
            'params',
            'modal',
            'title',
            'path',
            'type',
        );
        $params = array_intersect_key($_GET, array_flip($keys));

        return !empty($params) ? '?' . http_build_query($params) : null;
    }

    /**
     * Creates the query paramters string for return URL reference.
     *
     * @param string      $type
     * @param string      $path
     * @param null|string $title
     * @param array       $params
     * @param array       $options
     * @param bool        $isModal
     * @param null|string $modalTitle
     *
     * @return string
     */
    private function makeUriReferenceQuery(
        $type,
        $path,
        $title = null,
        array $params = array(),
        array $options = array(),
        $isModal = true,
        $modalTitle = null
    ) {
        return http_build_query(
            array_filter(array(
                'type'        => $type,
                'path'        => base64UrlEncode($path),
                'modal'       => (int) $isModal,
                'options'     => $options,
                'title'       => $title,
                'modal_title' => $modalTitle,
                'params'      => array_map(
                    function ($param) { return base64UrlEncode($param); },
                    array_values($params)
                ),
            ))
        );
    }

    /**
     * Checks if query has modal flag.
     *
     * @return bool
     */
    private function hasModalUriParameter()
    {
        return (bool) arrayGet($_GET, 'modal', false);
    }

    /**
     * Resolves reference by type.
     *
     * @param string $type
     * @param string $path
     * @param array  $params
     *
     * @return mixed
     */
    private function resolveUriReferenceType($type, $path, array $params)
    {
        return null;
    }
}
