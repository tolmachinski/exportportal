<?php

namespace App\Common\Traits;

use App\Documents\Versioning\AcceptedVersion;
use App\Documents\Versioning\PendingVersion;
use App\Documents\Versioning\RejectedVersion;

trait VersionStatusesMetadataAwareTrait
{
    /**
     * Returns the statuses version.
     *
     * @return array
     */
    private function getVersionStatusesMetadata()
    {
        return array(
            PendingVersion::class => array(
                'type'        => 'pending',
                'title'       => translate('accreditation_documents_status_processing', null, true),
                'icon'        => 'ep-icon_hourglass-processing',
                'color'       => 'txt-orange',
                'description' => translate('accreditation_document_is_processing', null, true),
                'raw_texts'   => array(
                    'title'       => array('*' => translate('accreditation_documents_status_processing', null, true)),
                    'description' => array('*' => translate('accreditation_document_is_processing', null, true)),
                ),
            ),
            AcceptedVersion::class  => array(
                'type'        => 'accepted',
                'title'       => translate('accreditation_documents_status_confirmed', null, true),
                'icon'        => 'ep-icon_ok',
                'color'       => 'txt-green',
                'description' => translate('systmess_success_document_has_been_confirmed', null, true),
                'raw_texts'   => array(
                    'title'       => array('*' => translate('accreditation_documents_status_confirmed', null, true)),
                    'description' => array('*' => translate('systmess_success_document_has_been_confirmed', null, true)),
                ),
            ),
            RejectedVersion::class => array(
                'type'        => 'rejected',
                'title'       => translate('accreditation_documents_status_decline', null, true),
                'icon'        => 'ep-icon_remove',
                'color'       => 'txt-red',
                'description' => translate('systmess_success_document_has_been_declined', null, true),
                'raw_texts'   => array(
                    'title'       => array('*' => translate('accreditation_documents_status_decline', null, true)),
                    'description' => array('*' => translate('systmess_success_document_has_been_declined', null, true)),
                ),
            ),
        );
    }

    private function getVersionExpirationMetadata()
    {
        return array(
            'expired' => array(
                'type'        => 'expired',
                'title'       => translate('accreditation_documents_status_expired', null, true),
                'icon'        => 'ep-icon_hourglass',
                'color'       => 'txt-red',
                'description' => translate('systmess_success_document_has_been_expired', null, true),
                'raw_texts'   => array(
                    'title'       => array('*' => translate('accreditation_documents_status_expired', null, true)),
                    'description' => array('*' => translate('systmess_success_document_has_been_expired', null, true)),
                ),
            ),
            'expires' => array(
                'type'        => 'expires',
                'title'       => function (\DateTimeImmutable $date) {
                    $now = new \DateTimeImmutable();
                    $currentDate = $now->modify('midnight');
                    $expriationDate = $date->modify('midnight');
                    if ($currentDate->add(new \DateInterval('P1D')) == $expriationDate) {
                        return translate('accreditation_documents_status_expire_tomorrow', null, true);
                    }

                    $difference = $expriationDate->diff($currentDate, true);
                    $thresholdDifference = $currentDate->diff($currentDate->add(new \DateInterval(config('document_expiration_initial_threshold', 'P7D'))), true);
                    if (
                        false !== $difference->days
                        && $difference->days > 0
                        && $difference->days <= $thresholdDifference->days
                    ) {
                        return translate('accreditation_documents_status_expire_in_days', array('[[DAYS]]' => $difference->days), true);
                    }

                    return translate('accreditation_documents_status_expire_soon', null, true);
                },
                'icon'        => 'ep-icon_hourglass',
                'color'       => 'txt-blue2',
                'description' => translate('systmess_success_document_expiring_soon', null, true),
                'raw_texts'   => array(
                    'title'       => array(
                        'interval' => translate('accreditation_documents_status_expire_in_days', null, true),
                        'tomorrow' => translate('accreditation_documents_status_expire_tomorrow', null, true),
                        '*'        => translate('accreditation_documents_status_expire_soon', null, true),
                    ),
                    'description' => array('*' => translate('systmess_success_document_expiring_soon', null, true)),
                ),
            ),
        );
    }

    /**
     * Returns the default version status information.
     *
     * @return array
     */
    private function getVersionDeafultStatusesMetadata()
    {
        return array(
            'title'       => translate('accreditation_documents_status_init', null, true),
            'icon'        => 'ep-icon_minus-circle',
            'color'       => 'txt-gray',
            'description' => translate('accreditation_user_have_to_upload_document', null, true),
        );
    }
}
