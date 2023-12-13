<?php

declare(strict_types=1);

namespace App\Services\EditRequest;

use App\Common\Contracts\Document\DocumentTypeCategory;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * The service that contains business logic for operations on the profile edit request documents.
 *
 * @author Anton Zencenco
 */
final class ProfileEditRequestDocumentsService extends AbstractEditRequestDocumentsService
{
    /**
     * {@inheritDoc}
     */
    protected function getDocumentType(): string
    {
        return 'profile_document';
    }

    /**
     * {@inheritDoc}
     */
    protected function getUserExisitingDocuments(int $userId, bool $attachTypes = false): ArrayCollection
    {
        return new ArrayCollection(
            $this->verificationDocumentsRepository->findAllBy([
                'with'   => \array_filter([$attachTypes ? 'type' : null]),
                'exists' => [
                    $this
                        ->verificationDocumentsRepository
                        ->getRelationsRuleBuilder()
                        ->whereHas('type', function (QueryBuilder $builder, RelationInterface $relation) {
                            $relation->getRelated()->getScope('category')->call($relation->getRelated(), $builder, DocumentTypeCategory::PERSONAL());
                        }),
                ],
                'scopes' => ['user' => $userId],
            ])
        );
    }
}
