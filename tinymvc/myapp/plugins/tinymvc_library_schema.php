<?php

use Spatie\SchemaOrg\Schema;
/**
 *
 * @author Bendiucov Tatiana
 * @todo Remove [03.12.2021]
 * Not used
 */
class TinyMVC_Library_Schema
{
    //testing
    public function get_schema_for_organization($data)
    {
        if(empty($data)){
            return;
        }
        $organization = Schema::organization()
            ->name($data['name'])
            ->url($data['url'])
            ->contactPoint(array(
                Schema::contactPoint()->contactType('Free Call')->telephone($data['free_call']),
                Schema::contactPoint()->contactType('International Call')->telephone($data['international_call'])));

        return $organization->toScript();
    }

}
