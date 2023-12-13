<div class="js-modal-flex wr-modal-flex inputs-40">
    <form class="modal-flex__form">
	    <div class="modal-flex__content pr-15">
            <ul>
                <?php
                    tmvc::instance()->controller->view->display('new/items_questions/item_view', array(
                        'helpful'             => $helpful,
                        'questions'           => array($question),
                        'questions_user_info' => $questions_user_info
                    ));
                ?>
            <ul>
        </div>
    </form>
</div>
