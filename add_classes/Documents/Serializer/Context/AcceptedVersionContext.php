<?php

namespace App\Documents\Serializer\Context;

use App\Common\Serializer\Context\AbstractContext;
use App\Common\Serializer\PropertyNormalizer;
use App\Documents\Versioning\VersionInterface;
use DateTimeImmutable;

final class AcceptedVersionContext extends AbstractContext
{
    /**
     * {@inheritdoc}
     */
    protected function getCachebleContext()
    {
        return array(
            PropertyNormalizer::DENORMALIZATION_CALLBACKS => array(
                'acceptanceDate' => function ($innerObject) {
                    return is_string($innerObject) ? DateTimeImmutable::createFromFormat(VersionInterface::DATE_FORMAT, $innerObject) : null;
                },
            ),
            PropertyNormalizer::NORMALIZATION_CALLBACKS => array(
                'acceptanceDate' => function ($innerObject) {
                    return $innerObject instanceof DateTimeImmutable ? $innerObject->format(VersionInterface::DATE_FORMAT) : null;
                },
            ),
        );
    }
}
