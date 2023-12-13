<?php

use const App\Moderation\Types\TYPE_B2B;
/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class Bad_Words_Controller extends TinyMVC_Controller {

    public function index()
    {
		show_404();
	}

    /*public function test() {
        $this->load->model('Elasticsearch_Badwords_Model', 'elastic_bad_words');
        $res = $this->elastic_bad_words->is_clean('asdasd as sda asdasd');
    }*/

	public function manage() {
        if (!logged_in()) {
            jsonResponse(translate("systmess_error_should_be_logged"));
        }

        checkAdmin('manage_content');

        $data = array(
            'title' => 'Bad words'
        );

        $this->view->assign($data);

        $this->view->display('admin/header_view');
        $this->view->display('admin/bad_words/index_view');
        $this->view->display('admin/footer_view');
	}


    function bad_words_popup() {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse(translate("systmess_error_should_be_logged_in"));
        }

        checkAdmin('manage_content');

        if (empty($_GET['language'])) {
            $this->load->model('Translations_Model', 'translations');
            $languageList = $this->translations->get_languages(array(
                'lang_url_type' => "'domain'"
            ));
            $language = false;
            $words = [];
        } else {
            $languageList = array();
            $this->load->model('Bad_Words_Model', 'bad_words');
            $language = strtolower(cleanInput($_GET['language']));
            $words = $this->bad_words->get_bad_words(array(
                'per_p' => false,
                'language' => $language
            ));
        }

        $this->view->display('admin/bad_words/form_view', array(
            'words' => $words,
            'languageList' => $languageList,
            'language' => $language
        ));
    }


    function save_bad_words() {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse(translate("systmess_error_should_be_logged_in"));
        }

        checkAdmin('manage_content');


        if (empty($_POST['words'])) {
            jsonResponse(translate("systmess_error_field_is_empty", ["[FIELD_NAME]" => "words"]));
        }

        if (empty($_POST['language'])) {
            jsonResponse(translate("systmess_error_field_is_empty", ["[FIELD_NAME]" => "language"]));
        }

        if (empty($_POST['action'])) {
            jsonResponse(translate("systmess_error_field_is_empty", ["[FIELD_NAME]" => "action"]));
        }

        $this->load->model('Bad_Words_Model', 'bad_words');
        $language = strtolower(cleanInput($_POST['language']));
        $words = $this->bad_words->get_bad_words(array(
            'per_p' => false,
            'language' => $language
        ));

        if ($_POST['action'] === 'add' && !empty($words)) {
            jsonResponse(translate("systmess_error_bad_words_lang_already_exists"));
        }


        $dbWords = array();
        foreach ($words as $word) {
            $dbWords[] = $word['word'];
        }


        $newWords = explode(',', $_POST['words']);
        $newWords = array_map(function ($word) {
            return trim($word);
        }, $newWords);


        $toDeleteWords = array_diff($dbWords, $newWords);
        $toInsertWords = array_diff($newWords, $dbWords);

        foreach ($toDeleteWords as $toDeleteWord) {
            $this->bad_words->delete_by_language_and_word($language, $toDeleteWord);
        }

        foreach ($toInsertWords as $toInsertWord) {
            $this->bad_words->insert(array(
                'language' => $language,
                'word' => $toInsertWord
            ));
        }

        jsonResponse(translate("systmess_success_bad_words_saved"), 'success');
    }


    function ajax_bad_words() {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse(translate("systmess_error_should_be_logged_in"));
        }

        checkAdmin('manage_content');

        $this->load->model('Bad_Words_Model', 'bad_words');

        $params = array_merge(
            array(
                'per_p' => intVal($_POST['iDisplayLength']),
                'start' => intVal($_POST['iDisplayStart']),
                'select' => "GROUP_CONCAT(word SEPARATOR ', ') as word, language, id, tl.lang_name",
                'group_by' => 'language',
                'sort_by' => flat_dt_ordering($_POST, array(
                    'dt_word' => 'word',
                    'dt_language' => 'language'
                ))
            ),
            dtConditions($_POST, array(
                array('as' => 'keywords', 'key' => 'sSearch', 'type' => 'cleanInput')
            ))
        );

        if(empty($params['sort_by'])) {
            $params['sort_by'] = ["language-asc"];
        }

        $sorting_split = [];
        foreach($params['sort_by'] as $sort_param) {
            $split = explode("-", $sort_param);
            $sorting_split[] = $split[0]. " " .$split[1];
        }

        $params['sort_by'] = $sorting_split;

        $words = $this->bad_words->get_bad_words($params);
        $words_count = $this->bad_words->get_bad_words_count($params);

        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $words_count,
            "iTotalDisplayRecords" => $words_count,
            'aaData' => array()
        );


        foreach ($words as $key => $word) {
            $out = array(
                'dt_language' => $word['lang_name'],
                'dt_word' => $word['word'],
                'dt_actions' => '<a href="' . __SITE_URL . 'bad_words/bad_words_popup?language=' . $word['language'] . '" data-title="Edit" class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModal"></a>'
            );

            $output['aaData'][] = $out;
        }

        jsonResponse('', 'success', $output);
    }
}
