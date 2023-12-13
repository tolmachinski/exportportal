<?php
/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Info_block_Controller extends TinyMVC_Controller {
	function popup_forms(){
        if (!isAjaxRequest())
            headerRedirect();

        $op = $this->uri->segment(3);
        switch($op){
            case 'view':
				$this->load->model('Text_block_Model', 'text_block');
        		$text_block = cleanInput($this->uri->segment(4));
                $data['block_info'] = $this->text_block->get_text_block_by_shortname($text_block);

                $this->view->display('new/text_blocks/text_block_view', $data);
            break;
        }
    }

	function ajax_operation(){
        if (!isAjaxRequest()){
            headerRedirect();
        }

        $op = $this->uri->segment(3);
        switch($op){
            case 'view':
        		$text_block = cleanInput($this->uri->segment(4));
                $block_info = model('Text_block')->get_text_block_by_shortname($text_block);
                if(empty($block_info)){
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                jsonResponse('', 'success', array('content' => '<div class="ep-tinymce-text">'.$block_info['text_block'].'</div>'));
            break;
        }
    }

    function show_block(){
        $this->load->model('Text_block_Model', 'text_block');
        $text_block = cleanInput($this->uri->segment(3));
        $data['block_info'] = $this->text_block->get_text_block_by_shortname($text_block);

        $this->view->display('new/header_view');
        $this->view->display('new/text_blocks/text_block_page_view', $data);
        $this->view->display('new/footer_view');
    }

    function featured_show_block(){
        $this->view->display('new/header_view');
        $this->view->display('new/text_blocks/featured_block_page_view');
        $this->view->display('new/footer_view');
    }

    function highlighted_show_block(){
        $this->view->display('new/header_view');
        $this->view->display('new/text_blocks/highlighted_block_page_view');
        $this->view->display('new/footer_view');
    }
}
