<?php

declare(strict_types=1);

namespace App\Validators;

use Doctrine\Common\Collections\ArrayCollection;

final class EpEventAddressValidator extends AddressValidator
{
    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $fields = $this->getFields();

        return (new ArrayCollection(parent::rules()))
            ->filter(function (array $rule) use ($fields) { return ($rule['field'] ?? null) !== $fields->get('postal_code', 'postal_code'); })
            ->getValues()
        ;
    }
}
