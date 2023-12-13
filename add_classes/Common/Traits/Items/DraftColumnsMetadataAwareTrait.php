<?php

namespace App\Common\Traits\Items;

/**
 * @deprecated
 */
trait DraftColumnsMetadataAwareTrait
{
    /**
     * Returns XLS columns metadata.
     *
     * @return array
     */
    public function getXlsColumnsMetadata()
    {
        return array(
            'title'             => array('validation_column' => 'title', 'db_column' => 'title'),
            'price'             => array('validation_column' => 'price', 'db_column' => 'price'),
            'sizes'             => array('validation_column' => 'sizes', 'db_column' => 'size'),
            'video'             => array('validation_column' => 'video', 'db_column' => 'video'),
            'weight'            => array('validation_column' => 'weight', 'db_column' => 'weight'),
            'discount'          => array('validation_column' => 'discount', 'db_column' => 'discount'),
            'quantity'          => array('validation_column' => 'quantity', 'db_column' => 'quantity'),
            'discount_price'    => array('validation_column' => 'discount_price', 'db_column' => 'final_price'),
            'min_sale_quantity' => array('validation_column' => 'min_sale_quantity', 'db_column' => 'min_sale_q'),
            'max_sale_quantity' => array('validation_column' => 'max_sale_quantity', 'db_column' => 'max_sale_q'),
        );
    }

    /**
     * Returns metadata about fields that will be stored in the DB.
     *
     * @return array
     */
    public function getStorageColumnsMetadata()
    {
        return array(
            'category'          => array('db_column' => 'id_cat'),
            'categories'        => array('db_column' => 'id_cat'),
            'product_city'      => array('db_column' => 'p_city'),
            'product_state'     => array('db_column' => 'state'),
            'product_country'   => array('db_column' => 'p_country'),
            'country_of_origin' => array('db_column' => 'origin_country'),
        );
    }
}
