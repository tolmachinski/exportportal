<?php

namespace App\Common\Http;

use Symfony\Component\HttpFoundation\Request as BaseRequest;

class Request extends BaseRequest
{
    /**
     * Returns true if the request is a AJAX.
     *
     * It works if your JavaScript library sets an X-Requested-With HTTP header or X-Fancybox HTTP header.
     *
     * @return bool true if the request is an AJAX, false otherwise
     */
    public function isAjaxRequest()
    {
        return $this->isXmlHttpRequest() || null !== $this->headers->get('x-fancybox');
    }
}
