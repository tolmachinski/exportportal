<?php

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Response;

if (!function_exists('httpGet')) {
    /**
     * Makes the HTTP GET request to the specified URI address.
     *
     * @param string $uri     - link
     * @param array  $options - http options
     *
     * @return Response - http response
     *
     * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/c.-Urls,-Http,-Domains#httpGet
     */
    function httpGet(string $uri, array $options = []): Response
    {
        return (new HttpClient($options))->get($uri);
    }
}

if (!function_exists('httpPost')) {
    /**
     * Makes the HTTP POST request to the specified URI address.
     *
     * @param string $uri     - link
     * @param array  $options - http options
     *
     * @return Response - http response
     *
     * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/c.-Urls,-Http,-Domains#httpPost
     */
    function httpPost(string $uri, array $options = []): Response
    {
        return (new HttpClient($options))->post($uri);
    }
}
