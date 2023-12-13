<?php

namespace App\Filesystem;

/**
 * The path generator for shipping methods.
 *
 * @author Anton Zencenco
 */
final class ShippingMethodsPathGenerator
{
    /**
     * Generate path to the shipping methods image.
     */
    public static function methodImage(string $fileName): string
    {
        return "shipping_methods/{$fileName}";
    }

    /**
     * Generate path to the shipping method files directory.
     */
    public static function methodDirectory(): string
    {
        return "shipping_methods";
    }
}
