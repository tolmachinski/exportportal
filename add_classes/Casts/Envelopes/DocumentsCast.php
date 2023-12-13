<?php

declare(strict_types=1);

namespace App\Casts\Envelopes;

use App\Common\Database\AttributeCastInterface;
use App\Common\Database\Model;
use BadMethodCallException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class DocumentsCast implements AttributeCastInterface
{
    /**
     * {@inheritdoc}
     *
     * @param null|Collection $documents
     */
    public function get(AbstractPlatform $platform, Model $model, string $key, $documents, array $attributes = [])
    {
        if (null === $documents) {
            $documents = new ArrayCollection();
        }

        return [
            'all'      => $documents,
            'latest'   => $documents->filter(fn (array $document) => !$document['is_authoriative_copy'])->last() ?: null,
            'original' => $documents->filter(fn (array $document) => $document['is_authoriative_copy'])->first() ?: null,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function set(AbstractPlatform $platform, Model $model, string $key, $value, array $attributes = [])
    {
        throw new BadMethodCallException('This cast class is read-only.');
    }
}
