<?php

namespace App\Common\Traits\Items;

/**
 * @deprecated
 */
trait ImagesValidationTrait
{
    protected function validateItemImages(array $images, $mainPhoto, array &$errors = array())
    {
        if (empty($images)) {
            $errors['images_empty'] = 'Please upload at least one image.';
        }

        if (empty($mainPhoto) || empty($images[$mainPhoto])) {
            $errors['images_main_required'] = 'The main image is not found. Please delete and re-upload the main image and try to submit the form one more time.';
        }

        return empty($errors);
    }
}
