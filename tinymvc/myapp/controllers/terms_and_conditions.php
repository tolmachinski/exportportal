<?php

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Terms_and_Conditions_Controller extends TinyMVC_Controller
{
    public function index(){
        show_404();
    }

    public function tc_order_now()
    {
        $menu = [
            '#general_condition'    => 'General Condition',
            '#only_venue'           => 'ExportPortal is Only a Venue',
            '#items_sale'           => 'Items for Sale',
            '#order_processing'     => 'Order Processing and Fulfillment',
            '#order_information'    => 'Order Information &amp; Personal Data',
            '#after_transaction'    => 'After Transaction is Processed',
            '#eligibility'          => 'Eligibility',
            '#cancellation'         => 'Cancellation',
            '#fees'                 => 'Fees',
            '#sellers'              => 'Sellers',
            '#bidding_buying'       => 'Bidding, Buying, and Conditions of Sale',
            '#video_audio'          => 'Video and Audio',
            '#privacy'              => 'Privacy',
            '#no_warranty'          => 'No Warranty',
            '#dispute_resolution'   => 'Dispute Resolution',
            '#comments'             => 'Comments',
            '#limitation_liability' => 'Limitation of Liability',
            '#liability'            => 'Your Liability',
        ];

        $this->showTermsAndConditions('tc_order_now', $menu);
    }

    public function tc_bloggers()
    {
        $this->showTermsAndConditions('tc_bloggers', [], ['{{BLOGGER_ACCESS_KEY}}' => config('blogger_access_key'), '{{BLOGGERS_URL}}' => __BLOGGERS_URL]);
    }

    public function tc_terms_of_use()
    {
        $menu = [
            '#application_acceptance'       => 'Application and Acceptance of the Terms',
            '#provision_services'           => 'Provision of Services',
            '#users_generally'              => 'Users Generally',
            '#member_accounts'              => 'Member Accounts',
            '#members_responsibilities'     => 'Member’s Responsibilities',
            '#breaches_members'             => 'Breaches by Members',
            '#transactions_between'         => 'Transactions Between Buyers and Sellers',
            '#limitation_liability'         => 'Limitation of Liability',
            '#force_majeure'                => 'Force Majeure',
            '#intellectual_property_rights' => 'Intellectual Property Rights',
            '#notices'                      => 'Notices',
            '#general_provisions'           => 'General Provisions',
        ];
        $this->showTermsAndConditions('tc_terms_of_use', $menu);
    }

    public function tc_privacy_policy()
    {
        $menu = [
            '#COLLECTION_INFORMATION'   => 'COLLECTION OF INFORMATION',
            '#USE_PERSONAL_DATA'        => 'USE OF PERSONAL DATA',
            '#DISCLOSURE_PERSONAL_DATA' => 'DISCLOSURE OF PERSONAL DATA',
            '#CORRECT_PERSONAL_DATA'    => 'RIGHT TO ACCESS/CORRECT PERSONAL DATA',
            '#COOKIES'                  => 'COOKIES',
            '#MINORS'                   => 'MINORS',
            '#SECURITY_MEASURES'        => 'SECURITY MEASURES',
            '#CHANGES_PRIVACY_POLICY'   => 'CHANGES TO THIS PRIVACY POLICY',
            '#YOUR_FEEDBACK'            => 'YOUR FEEDBACK',
        ];

        $this->showTermsAndConditions('tc_privacy_policy', $menu);
    }

    public function tc_photo_and_video_upload()
    {
        checkPermision('create_item,edit_item');

        $menu = [
            '#js_terms_terms_governing_your_submission_images_videos' => 'Terms Governing Your Submission of Images and Videos',
            '#js_terms_license_us'                                    => 'License to Us',
            '#js_terms_image_video_upload'                            => 'Image and Video Upload',
            '#js_terms_ownership_copyright'                           => 'Ownership of Copyright',
            '#js_terms_exclusion_liability'                           => 'Exclusion of Liability',
            '#js_terms_indemnity'                                     => 'Indemnity',
            '#js_terms_miscellaneous'                                 => 'Miscellaneous',
            '#js_terms_jurisdiction'                                  => 'Jurisdiction',
        ];

        $this->showTermsAndConditions('tc_photo_and_video_upload', $menu);
    }

    public function tc_register_seller()
    {
        $webpackData = cleanInput($this->uri->segment(3));

        $menu = [
            '#customer-requirements'    => 'Customer Requirements',
            '#registration-information' => 'Registration Information',
            '#account-deactivation'     => 'Account Deactivation',
            '#services'                 => 'Services',
            '#sellers'                  => 'Sellers',
            '#buyers'                   => 'Buyers',
            '#shippers'                 => 'Freight Forwarders',
            '#international-sales'      => 'International Sales',
            '#paperwork-legal-advice'   => 'Paperwork/No Legal Advice',
            '#insurance'                => 'Insurance',
            '#resolving-party-disputes' => 'Resolving Party Disputes',
            '#registration-fee'         => 'Registration Fee',
            '#fees-payable-company'     => 'Fees Payable to the Company',
            '#payment'                  => 'Payment',
            '#refund-policy'            => 'Refund Policy',
            '#image-release'            => 'Image Release',
            '#code-conduct'             => 'Code of Conduct',
            '#assumption-risk'          => 'Assumption of Risk',
            '#indemnification'          => 'Indemnification',
            '#remedies'                 => 'Remedies',
            '#limitation-liability'     => 'Limitation of Liability',
            '#non-circumvention'        => 'Non-Circumvention',
            '#force-majeure'            => 'Force Majeure',
            '#disclaimer'               => 'Disclaimer',
            '#miscellaneous'            => 'Miscellaneous',
            '#privacy-statement'        => 'Privacy Statement',
            '#feedback'                 => 'Feedback',
            '#links-website'            => 'Links To and From this Website',
            '#intellectual-property'    => 'Intellectual Property',
            '#prohibitions'             => 'Prohibitions',
            '#international-use'        => 'International Use',
            '#complaint-procedures'     => 'Complaint Procedures',
            '#legal-disputes'           => 'Legal Disputes',
            '#termination'              => 'Termination',
            '#electronic-signature'     => 'Electronic Signature',
            '#copyright-notice'         => 'Copyright Notice',
        ];

        $this->showTermsAndConditions('tc_register_seller', $menu, [], $webpackData);
    }

    public function tc_register_buyer()
    {
        $this->showTermsAndConditions('tc_register_buyer');
    }

    public function tc_register_shipper()
    {
        $this->showTermsAndConditions('tc_register_shipper');
    }

    public function tc_make_offer()
    {
        $this->showTermsAndConditions('tc_make_offer');
    }

    public function tc_product_listing()
    {
        $this->showTermsAndConditions('tc_product_listing');
    }

    public function tc_subscription_terms_of_conditions()
    {
        $this->showTermsAndConditions('tc_subscription_terms_of_conditions');
    }

    public function tc_restricted_adult_items_policy()
    {
        $menu = [
            '#LISTING_POLICY' => 'LISTING POLICY',
            '#ACCESS_POLICY'  => 'ACCESS POLICY',
        ];

        $this->showTermsAndConditions('tc_restricted_adult_items_policy', $menu);
    }

    public function tc_giveaway_terms_of_conditions()
    {
        try {
            // Get current date
            $currentDate = new DateTimeImmutable();
            // Get giveaway start date
            $startDate = Carbon::createFromFormat(DateTimeInterface::RFC3339, config('giveaway_start_datetime'))->timezone($currentDate->getTimezone());
            // Get giveaway start date
            $endDate = Carbon::createFromFormat(DateTimeInterface::RFC3339, config('giveaway_end_datetime'))->timezone($currentDate->getTimezone());
        } catch (InvalidFormatException $e) {
            // If failed to parse datetime values than everything is empty
            $startDate = null;
            $endDate = null;
        }

        $this->showTermsAndConditions(
            'tc_giveaway_terms_of_conditions',
            [
                '#tc-eligibility'                       => 'Eligibility',
                '#tc-agreement-to-rules'                => 'Agreement to Rules',
                '#tc-contest-period'                    => 'Contest Period',
                '#tc-how-to-enter'                      => 'How to Enter',
                '#tc-prizes'                            => 'Prizes',
                '#tc-winner-selection-and-notification' => 'Winner Selection and Notification',
                '#tc-rights-granted-by-you'             => 'Rights Granted by You',
                '#tc-terms-conditions'                  => 'Terms &amp; Conditions',
                '#tc-limitation-of-liability'           => 'Limitation of Liability',
            ],
            [
                '[START_DATE]'        => null !== $startDate ? $startDate->translatedFormat('F d, Y') : null,
                '[START_TIME]'        => null !== $startDate ? $startDate->translatedFormat('H A') : null,
                '[END_DATE]'          => null !== $endDate ? $endDate->translatedFormat('F d, Y') : null,
                '[END_TIME]'          => null !== $endDate ? $endDate->translatedFormat('H A') : null,
                '[SMARTSHEET_FORM]'   => \config('giveaway_form_url', \getUrlForGroup('/404')),
                '[BRANDED_HASHTAG_1]' => \config('giveaway_hashtag_default', '#null'),
                '[BRANDED_HASHTAG_2]' => \config('giveaway_hashtag_contest', '#null'),
            ]
        );
    }

    private function showTermsAndConditions(string $name, array $menu = [], array $replacements = [], string $webpackData = '')
    {
        /** @var Text_block_Model $textBlocksRepository */
        $textBlocksRepository = \model(Text_block_Model::class);
        $termsTextBlock = $textBlocksRepository->get_text_block_by_shortname($name);
        if (!empty($replacements)) {
            $termsTextBlock = str_replace(array_keys($replacements), array_values($replacements), $termsTextBlock);
        }

        if (!isAjaxRequest()) {
            views('new/index_template_view', [
                'terms_menu'            => $menu,
                'terms_info'            => $termsTextBlock,
                'main_content'          => 'new/terms_and_conditions/index_view',
                'header_out_content'    => 'new/terms_and_conditions/header_view',
                'sidebar_right_content' => !empty($menu) ? 'new/terms_and_conditions/sidebar_view' : null,
            ]);
        } else {
            views(
                array_filter([!empty($menu) ? 'new/terms_and_conditions/sidebar_view' : null, 'new/terms_and_conditions/index_view']),
                array_filter(
                    [
                        'terms_menu'     => $menu,
                        'terms_info'     => $termsTextBlock,
                        'webpackData'    => !empty($webpackData) ? true : null,
                        'terms_in_modal' => true,
                    ],
                    fn ($v) => null !== $v,
                )
            );
        }
    }
}
