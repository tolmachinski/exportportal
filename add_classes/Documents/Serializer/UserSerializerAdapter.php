<?php

namespace App\Documents\Serializer;

use App\Common\Serializer\AbstractSerializerAdapter;
use App\Common\Serializer\Context\AggregatedContext;
use App\Common\Serializer\Mapping\ClassDiscriminator;
use App\Common\Serializer\Mapping\ClassDiscriminatorEntityInterface;
use App\Common\Serializer\PropertyNormalizer;
use App\Documents\Serializer\Context\ManagerContext;
use App\Documents\User\Manager;
use App\Documents\User\UserInterface;
use App\Documents\User\UserTypesInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorMapping;
use Symfony\Component\Serializer\Serializer;

final class UserSerializerAdapter extends AbstractSerializerAdapter
{
    /**
     * {@inheritdoc}
     */
    protected function getDefaultSerializer()
    {
        return new Serializer(
            array(new PropertyNormalizer(null, null, null, $this->classDiscriminatorResolver)),
            array(new JsonEncoder())
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultContext()
    {
        return new AggregatedContext(array(
            new ManagerContext(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultClassDiscriminatorResolver()
    {
        return new ClassDiscriminator(array(
            UserInterface::class => new ClassDiscriminatorMapping(ClassDiscriminatorEntityInterface::DISCRIMINATOR_KEY, array(
                UserTypesInterface::MANAGER => Manager::class,
            )),
        ));
    }
}
