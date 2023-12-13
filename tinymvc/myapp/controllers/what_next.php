<?php

declare(strict_types=1);

use App\Common\Traits\VersionMetadataTrait;
use App\Common\Traits\VersionStatusesMetadataAwareTrait;
use App\Documents\Serializer\VersionSerializerStatic;
use App\Documents\Versioning\VersionCollectionInterface;
use App\Documents\Versioning\VersionInterface;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class What_Next_Controller extends TinyMVC_Controller
{
    use VersionMetadataTrait;
    use VersionStatusesMetadataAwareTrait;

    public function popup_forms()
    {
        checkIsAjax();
        checkIsLoggedAjaxModal();
        checkDomainForGroup();
        checkPermisionAjaxModal('manage_personal_documents,manage_user_documents');

        $action = uri()->segment(3);
        $user_id = (int) privileged_user_id();

        switch ($action) {
            case 'status':
                checkPermisionAjaxModal('manage_personal_documents');

                if (empty($user = model('user')->getSimpleUser($user_id))) {
                    messageInModal(translate('systmess_error_user_does_not_exist'));
                }

                $fetchContent = $this->show_popup($user_id, $user);

                jsonResponse(
                    null,
                    'success',
                    [
                        'content' => views()->fetch('new/popups/show_preactivation_view', $fetchContent),
                        'footer' => views()->fetch('new/popups/what_next_footer_view', ['cookie_name' => "ep_open_what_next_verification"]),
                    ],
                );

                break;
            case 'activation':
                // show_preactivation
                checkPermisionAjax('manage_personal_documents');

                if ( empty($user = model('user')->getSimpleUser($user_id))) {
                    jsonResponse(translate('systmess_error_user_does_not_exist'));
                }

                $fetchContent = $this->show_popup($user_id, $user);
                widgetPopupsSystemRemoveOneItem("show_preactivation");

                jsonResponse(
                    null,
                    'success',
                    [
                        "title"     => translate('accreditation_verify_documents_btn_data_title', null, true),
                        "subTitle"  => "Check out the verification timeline",
                        'content'   => views()->fetch('new/popups/show_preactivation_view', $fetchContent),
                        'footer'    => views()->fetch('new/popups/show_preactivation_footer_view'),
                    ],
                );

                break;
        }
    }

    private function show_popup($user_id, $user)
    {
        $progress = 'uncompleted';

        //region steps
        $documents = array_map(
            function ($document) {
                $document['title'] = translate('personal_documents_unknown_document_title');
                $document['versions'] = VersionSerializerStatic::deserialize(arrayGet($document, 'versions'), VersionCollectionInterface::class, 'json');
                $document['latest_version'] = VersionSerializerStatic::deserialize(arrayGet($document, 'latest_version'), VersionInterface::class, 'json');
                $document['metadata'] = $this->getVersionMetadata($document['latest_version']);
                if (!empty($document['type'])) {
                    if (null !== $title = accreditation_i18n($document['type']['document_i18n'], 'title', null, $document['type']['document_title'])) {
                        $document['title'] = $title;
                    }
                }

                return $document;
            },
            array_filter(
                (array) model('user_personal_documents')->get_documents(array(
                    'with'       => array('type'),
                    'order'      => array('id_document' => 'ASC'),
                    'conditions' => array(
                        'user' => (int) $user['idu'],
                    ),
                ))
            )
        );

        //region Vars
        $required_documents = array_filter($documents, function ($document) { return $document['metadata']['is_uploadable']; });
        $has_uploadable_documents = (bool) count($required_documents);
        //endregion Vars

        //region Profile completion
        $profile_completion = arrayByKey(model('complete_profile')->get_user_profile_options((int) $user_id), 'option_alias');
        unset($profile_completion['account_verification']);
        $profile_completion = array_filter($profile_completion, function ($profile_option) {
            return 0 == $profile_option['option_completed'];
        });
        $has_completable_options = (bool) count($profile_completion);
        //endregion Profile completion

        $view_messages = array();
        $messages = array(
            'upload_documents' => array(
                'has_link'    => true,
                'is_optional' => false,
                'link'        => 'verification',
                'title'       => 'Upload your documents',
                'text'        => '2-5 business days for verification',
            ),
            'review_documents' => array(
                'has_link'    => true,
                'is_optional' => false,
                'link'        => 'verification',
                'title'       => 'Document review',
                'text'        => '2-5 business days for verification',
            ),
            'confirmations_email' => array(
                'has_link'    => false,
                'is_optional' => false,
                'link'        => '',
                'title'       => 'Receive confirmation email',
                'text'        => '',
            ),
            'activation_email' => array(
                'has_link'    => false,
                'is_optional' => false,
                'link'        => '',
                'title'       => 'Receive activation email',
                'text'        => '',
            ),
            'ep_features' => array(
                'has_link'    => true,
                'is_optional' => false,
                'link'        => 'about/features',
                'title'       => 'Explore Export Portal’s features',
                'text'        => '',
            ),
            'fill_profile' => array(
                'has_link'    => true,
                'is_optional' => false,
                'link'        => 'user/preferences',
                'title'       => 'Fill out your profile',
                'text'        => '',
            ),
            'upgrade_account' => array(
                'has_link'    => true,
                'is_optional' => true,
                'link'        => 'upgrade',
                'title'       => 'Upgrade your account',
                'text'        => '',
            ),
        );

        if (!empty($has_completable_options)) {
            if ($has_uploadable_documents) {
                $view_messages[] = $messages['upload_documents'];
            } else {
                $view_messages[] = $messages['review_documents'];
            }

            $view_messages[] = $messages['confirmations_email'];
        }else{
            $progress = 'completed';
            $view_messages[] = $messages['activation_email'];
        }

        $view_messages[] = $messages['ep_features'];

        if (!empty($has_completable_options)) {
            $view_messages[] = $messages['fill_profile'];
        }

        if (
            empty(model('upgrade')->get_latest_request(array(
                'conditions' => array(
                    'user'           => (int) $user_id,
                    'status'         => array('new'),
                    'is_not_expired' => true,
                ),
            )))
            && have_right('upgrade_group')
        ) {
            $view_messages[] = $messages['upgrade_account'];
        }
        //endregion steps

        //region topics
        $topics = array(
            'why_ep' => array(
                'link'  => __SITE_URL . 'about/why_exportportal',
                'title' => 'Why EP',
                'image' => 'image-1.jpg',
            ),
            'buying' => array(
                'link'  => __SITE_URL . 'buying',
                'title' => 'Buying',
                'image' => 'image-2.jpg',
            ),
            'selling' => array(
                'link'  => __SITE_URL . 'selling',
                'title' => 'Selling',
                'image' => 'image-7.jpg',
            ),
            'lern_more' => array(
                'link'  => __SITE_URL . 'learn_more',
                'title' => 'Learn More',
                'image' => 'image-3.jpg',
            ),
            'blog' => array(
                'link'  => __BLOG_URL,
                'title' => 'Blog',
                'image' => 'image-4.jpg',
            ),
            'news' => array(
                'link'  => __SITE_URL . 'about/in_the_news',
                'title' => 'News',
                'image' => 'image-5.jpg',
            ),
            'faq' => array(
                'link'  => __SITE_URL . 'faq/all',
                'title' => 'FAQ',
                'image' => 'image-6.jpg',
            ),
        );

        $user_group = user_group_type();
        if ('Seller' == $user_group) {
            unset($topics['buying']);
        } elseif ('Buyer' == $user_group) {
            unset($topics['selling']);
        } elseif ('Shipper' == $user_group) {
            unset($topics['selling'], $topics['buying']);
        }
        //endregion topics

        $fetchContent = [
            'view_messages'             => $view_messages,
            'topics'                    => $topics,
            'documents'                 => $required_documents,
            'has_uploadable_documents'  => $has_uploadable_documents,
            'progress'                  => $progress,
            'freePeriodDate'            => ''
        ];

        if(filter_var(config('env.FREE_CERTIFICATION'), FILTER_VALIDATE_BOOLEAN)){
            $freePeriod = model('packages')->getPeriodById(5);
            $fetchContent['freePeriodDate'] = getDateFormat($freePeriod['fixed_end_date'], 'Y-m-d', 'j M, Y');
        }

        return $fetchContent;
    }
}
