<?php

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class External_Forms_Controller extends TinyMVC_Controller {

    function popup_forms() {
		if (!isAjaxRequest())
			headerRedirect();

		$op = $this->uri->segment(3);

		switch ($op) {
            case 'other_iframes':
                $data['iframes'] = array(
                    0 => array(
                        'logged' => false,
                        'rights' => '',
                        'title' => translate('external_forms_list_item_2_title'),
                        'desc' => translate('external_forms_list_item_2_text'),
                        'link' => config('external_forms_list_item_2_link'),
                    ),
                    1 => array(
                        'logged' => false,
                        'rights' => '',
                        'title' => translate('external_forms_list_item_3_title'),
                        'desc' => translate('external_forms_list_item_3_text'),
                        'link' => config('external_forms_list_item_3_link'),
                    )
                );

                $data['popup_text'] = translate('external_forms_popup_other_text');
                $data['popup_title_text'] = translate('external_forms_popup_other_title', null, true);

				$this->view->display('new/external_forms/popup_view', $data);
            break;
			case 'problem_iframes':

				$data['iframes'] = array(
                    0 => array(
                        'logged' => false,
                        'rights' => '',
                        'title' => translate('external_forms_list_item_1_title'),
                        'desc' => translate('external_forms_list_item_1_text'),
                        'link' => config('external_forms_list_item_1_link'),
                    ),
                    1 => array(
                        'logged' => false,
                        'rights' => '',
                        'title' => translate('external_forms_list_item_4_title'),
                        'desc' => translate('external_forms_list_item_4_text'),
                        'link' => config('external_forms_list_item_4_link'),
                    ),
                    2 => array(
                        'logged' => false,
                        'rights' => '',
                        'title' => translate('external_forms_list_item_5_title'),
                        'desc' => translate('external_forms_list_item_5_text'),
                        'link' => config('external_forms_list_item_5_link'),
                    ),
                    3 => array(
                        'logged' => false,
                        'rights' => '',
                        'title' => translate('external_forms_list_item_6_title'),
                        'desc' => translate('external_forms_list_item_6_text'),
                        'link' => config('external_forms_list_item_6_link'),
                    ),
                );

                $data['popup_text'] = translate('external_forms_popup_text');
                $data['popup_title_text'] = translate('external_forms_popup_title', null, true);

				$this->view->display('new/external_forms/popup_view', $data);
            break;
        }
    }

}
