<?php

declare(strict_types=1);

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller City
 */
class City_Controller extends TinyMVC_Controller
{
    /**
     * This method is created for updating crm leads timezone
     */
    public function get_timezone()
    {
        if (Request::METHOD_POST !== request()->getMethod()) {
            show_404();
        }

        $request = request()->request;

        if (
            empty($cityName = $request->get('city'))
            || empty($stateName = $request->get('state'))
            || empty($countryName = $request->get('country'))
        ) {
            return new Response(
                json_encode([
                    'status'  => 'error',
                    'message' => 'Some of the required fields are not found',
                ]),
                200,
                [
                    'Content-Type' => 'application/json'
                ]
            );
        }

        /** @var Elasticsearch_Cities_Model $elasticsearchCitiesModel */
        $elasticsearchCitiesModel = model(Elasticsearch_Cities_Model::class);

        $cities = $elasticsearchCitiesModel->getCities([
            'cityName'    => $cityName,
            'stateName'   => $stateName,
            'countryName' => $countryName,
        ]);

        if (empty($cities)) {
            return new Response(
                json_encode([
                    'status'  => 'error',
                    'message' => 'Location not found',
                ]),
                200,
                [
                    'Content-Type' => 'application/json'
                ]
            );
        }

        if (count($cities) > 1) {
            return new Response(
                json_encode([
                    'status'  => 'error',
                    'message' => 'Too many results found',
                ]),
                200,
                [
                    'Content-Type' => 'application/json'
                ]
            );
        }

        $city = array_shift($cities);

        try {
            $utcOffset = (new DateTime())->setTimezone(new DateTimeZone($city['timezone']))->format('Z') / 3600;
        } catch (\Throwable $th) {
            $utcOffset = '';
        }

        return new Response(
            json_encode([
                'status'   => 'success',
                'timezone' => $city['timezone'],
                'offset'   => $utcOffset,
            ]),
            200,
            [
                'Content-Type' => 'application/json'
            ]
        );
    }
}

// End of file city.php
// Location: /tinymvc/myapp/controllers/city.php
