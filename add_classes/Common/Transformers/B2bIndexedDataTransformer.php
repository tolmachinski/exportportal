<?php

declare(strict_types=1);

namespace App\Common\Transformers;

use League\Fractal\TransformerAbstract;
use App\Common\Contracts\B2B\B2bRequestLocationType;

class B2bIndexedDataTransformer extends TransformerAbstract
{
	public function transform(array $request)
	{
        return [
            'id_request'        => $request['id'],
            'id_company'        => $request['companyId'],
            'id_user'           => $request['userId'],
            'b2b_radius'        => $request['radius'],
            'b2b_title'         => $request['title'],
            'b2b_tags'          => $request['tags'],
            'viewed_count'      => $request['countView'],
            'countAdvices'      => $request['countAdvices'],
            'type_location'     => $request['type_location'] ? B2bRequestLocationType::tryFrom($request['type_location']) : null,
            'b2b_message'       => $request['message'],
            'b2b_date_update'   => $request['updateDate'],
            'b2b_date_register' => $request['registerDate'],
            'mainImage'         => ['photo' => $request['main_image']],
            'company'           => [
                'id_company'             => (int) $request['company']['id'],
                'name_company'           => $request['company']['displayedName'],
                'legal_name_company'     => $request['company']['legalName'],
                'index_name'             => $request['company']['indexName'],
                'type_company'           => $request['company']['type'],
                'latitude'               => $request['company']['latitude'],
                'longitude'              => $request['company']['longitude'],
                'parent_company'         => (int) $request['company']['parent'],
                'logo_company'           => $request['company']['logo'],
                'address_company'        => $request['company']['address'],
                'zip_company'            => $request['company']['zip'],
                'id_country'             => (int) $request['company']['country']['id'],
                'country'                => $request['company']['country']['name'],
                'country_alias'          => $request['company']['country']['slug'],
                'id_state'               => (int) $request['company']['state']['id'],
                'state_name'             => $request['company']['state']['name'],
                'id_city'                => (int) $request['company']['city']['id'],
                'city'                   => $request['company']['city']['name'],
            ],
            'partnerType' => $request['partnerType'],
            'industries'  => $request['industries'],
            'categories'  => $request['categories'],
            'photos'      => $request['photos'],
            'countries'   => array_map(function ($country) {
                $newCountry['id'] = $country['id'];
                $newCountry['country'] = $country['name'];

                return $newCountry;
            }, $request['countries']),
        ];
	}
}
