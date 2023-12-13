<?php

declare(strict_types=1);

namespace App\Validators;

use Doctrine\Common\Collections\ArrayCollection;

final class ItemAddressValidator extends AddressValidator
{
    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $fields = $this->getFields();

        return (new ArrayCollection(parent::rules()))
            ->filter(function (array $rule) use ($fields) { return ($rule['field'] ?? null) !== $fields->get('address', 'address'); })
            ->getValues()
        ;
    }
}
