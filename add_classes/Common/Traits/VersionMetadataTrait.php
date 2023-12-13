<?php

namespace App\Common\Traits;

use App\Documents\Versioning\AcceptedVersionInterface;
use App\Documents\Versioning\ContentContextEntries;
use App\Documents\Versioning\ExpiringVersionInterface;
use App\Documents\Versioning\PendingVersionInterface;
use App\Documents\Versioning\RejectedVersionInterface;
use App\Documents\Versioning\ReplaceableVersionInterface;
use App\Documents\Versioning\VersionInterface;
use DateInterval;
use DateTimeImmutable;

trait VersionMetadataTrait
{
    /**
     * Returns the version metadata.
     *
     * @return array
     */
    private function getVersionMetadata(VersionInterface $version = null)
    {
        /** @var null|AcceptedVersionInterface|ExpiringVersionInterface|PendingVersionInterface|RejectedVersionInterface|ReplaceableVersionInterface|VersionInterface $version */
        $now = new DateTimeImmutable();
        $hasComment = null !== $version ? !empty($version->getComment()) : false;
        $requiresDynamicFields = null !== $version ? $version->getContext()->has(ContentContextEntries::REQUIRES_DYNAMIC_FIELDS) : false;
        $hasDynamicFields = $requiresDynamicFields && !empty($version->getContext()->get(ContentContextEntries::DYNAMIC_FIELDS_NAMES_LIST));
        $currentDate = $now->modify('midnight');
        $isVersionPending = $version instanceof PendingVersionInterface;
        $isVersionRejected = $version instanceof RejectedVersionInterface;
        $isVersionAccepted = $version instanceof AcceptedVersionInterface;
        $isVersionExpirable = $version instanceof ExpiringVersionInterface;
        $isVersionReplaceable = $version instanceof ReplaceableVersionInterface;
        $isDownloadable = null !== $version;
        $isUploaded = null !== $version;
        $isExpired = $isVersionExpirable ? $version->isExpired() : false;
        $created = $isUploaded && $version->hasCreationDate() ? $version->getCreationDate()->format(DATE_ISO8601) : null;
        $expires = $isVersionExpirable && $version->hasExpirationDate() ? $version->getExpirationDate()->format(DATE_ISO8601) : null;
        $accepted = $isVersionAccepted && $version->hasAcceptanceDate() ? $version->getAcceptanceDate()->format(DATE_ISO8601) : null;
        $rejected = $isVersionRejected && $version->hasRejectionDate() ? $version->getRejectionDate()->format(DATE_ISO8601) : null;
        $rejectionCode = $isVersionRejected ? $version->getReasonCode() : null;
        $rejectionTitle = $isVersionRejected ? $version->getReasonTitle() : null;
        $rejectionMessage = $isVersionRejected ? $version->getReason() : null;
        $isVersionExpiringSoon = false;
        if ($isVersionExpirable && $version->hasExpirationDate()) {
            $expirationDate = $version->getExpirationDate()->modify('midnight');
            $expirationBoundary = $currentDate->add(new DateInterval(config('document_expiration_initial_threshold', 'P7D')));
            $isVersionExpiringSoon = !$isExpired && ($expirationDate > $currentDate && $expirationDate <= $expirationBoundary);
        }
        $isUploadable = null === $version || $isVersionRejected || $isExpired || $isVersionExpiringSoon;
        $isReUploadable = false;
        if ($isVersionReplaceable && ($version->hasCreationDate() || $version->hasOriginalCreationDate())) {
            $creationDate = $version->hasOriginalCreationDate() ? $version->getOriginalCreationDate() : $version->getCreationDate();
            $reUploadTimeBoundary = $creationDate->add(new DateInterval(config('document_re_upload_time_threshold', 'PT12H')));
            $reUploadAmountBoundary = (int) config('document_re_upload_amount_threshold', 5);
            $isReUploadable = ($reUploadTimeBoundary && $now < $reUploadTimeBoundary)
                && ($reUploadAmountBoundary && $version->getReplacementAttempt() < $reUploadAmountBoundary);
        }

        return array(
            'created'                 => $created,
            'expires'                 => $expires,
            'accepted'                => $accepted,
            'rejected'                => $rejected,
            'has_comment'             => $hasComment,
            'rejection_code'          => $rejectionCode,
            'rejection_title'         => $rejectionTitle,
            'rejection_message'       => $rejectionMessage,
            'has_dynamic_fields'      => $hasDynamicFields,
            'is_version_pending'      => $isVersionPending,
            'is_version_rejected'     => $isVersionRejected,
            'is_version_accepted'     => $isVersionAccepted,
            'is_version_expirable'    => $isVersionExpirable,
            'is_version_replaceable'  => $isVersionReplaceable,
            'requires_dynamic_fields' => $requiresDynamicFields,
            'is_expiring_soon'        => $isVersionExpiringSoon,
            'is_downloadable'         => $isDownloadable,
            'is_reuploadable'         => $isReUploadable,
            'is_uploadable'           => $isUploadable,
            'is_uploaded'             => $isUploaded,
            'is_expired'              => $isExpired,
        );
    }
}
