<?php

namespace App\Optimization;

use Spatie\ImageOptimizer\Image;
use Spatie\ImageOptimizer\Optimizers\BaseOptimizer;

class CustomCwebp extends BaseOptimizer
{
    public $binaryName = 'cwebp';

    public function canHandle(Image $image): bool
    {
        return true;
    }

    public function getCommand(): string
    {
        $optionString = implode(' ', $this->options);

        return "\"{$this->binaryPath}{$this->binaryName}\" {$optionString}"
            .' '.escapeshellarg($this->imagePath)
            .' -o '.escapeshellarg($this->imagePath);
    }
}
