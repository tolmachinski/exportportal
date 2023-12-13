<?php

/**
 * @author Bendiucov Tatiana
 * @todo Remove [01.12.2021]
 * Library not used
 */
class TinyMVC_Library_barcode
{
    public function new_barcode()
    {
        return new Picqer\Barcode\BarcodeGeneratorHTML();
    }

    public function png_barcode()
    {
        return new Picqer\Barcode\BarcodeGeneratorPNG();
    }

    public function jpg_barcode()
    {
        return new Picqer\Barcode\BarcodeGeneratorJPG();
    }

    public function svg_barcode()
    {
        return new Picqer\Barcode\BarcodeGeneratorSVG();
    }
}
