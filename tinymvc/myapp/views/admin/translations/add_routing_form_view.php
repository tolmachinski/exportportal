<div class="wr-modal-b">
   	<form class="modal-b__form validateModal">
		<div class="modal-b__content pb-0 w-700">
			<div class="row">
                <?php 
                    $tabs_list = '';
                    $tabs_content = '';
                    foreach ($tlanguages as $lkey => $tlanguage) {
                        $tabs_list .= '<li class="'.(($lkey == 0)?'active':'').'">
                                            <a href="#rlang_'.$tlanguage['lang_iso2'].'" role="tab" aria-controls="rlang_'.$tlanguage['lang_iso2'].'" data-toggle="tab">'.$tlanguage['lang_name'].'</a>
                                        </li>';
                        $tabs_content .= '<div role="tabpanel" class="tab-pane '.(($lkey == 0)?'active':'').'" id="rlang_'.$tlanguage['lang_iso2'].'">
                                                <div class="row">
                                                    <div class="col-xs-6">
                                                        <label class="modal-b__label">Route search</label>
                                                        <input type="text" class="form-control" name="routes[lang]['.$tlanguage['lang_iso2'].'][route_search]" placeholder="Route search">
                                                    </div>
                                                    <div class="col-xs-6">
                                                        <label class="modal-b__label">Replace URI string</label>
                                                        <input type="text" class="form-control" name="routes[lang]['.$tlanguage['lang_iso2'].'][replace_uri_string]" placeholder="e.g. item/URI_1/comments">
                                                    </div>
                                                    <div class="col-xs-12">
                                                        <label class="modal-b__label">URI components</label>
                                                        <input type="text" class="form-control" name="routes[lang]['.$tlanguage['lang_iso2'].'][uri_components]" value="" placeholder="e.g. category/author">
                                                    </div>
                                                </div>
                                            </div>';
                    }
                ?>
                <div class="col-xs-6 mt-15">
                    <label class="modal-b__label">Controller</label>
					<input type="text" class="form-control" name="routes[route_controller]" placeholder="e.g. items">
				</div>
                <div class="col-xs-6 mt-15">
                    <label class="modal-b__label">Action</label>
					<input type="text" class="form-control" name="routes[route_action]" placeholder="e.g. detail">
				</div>
                <div class="col-xs-12 mt-15">
                    <label class="modal-b__label">Route replace</label>
					<input type="text" class="form-control" name="routes[route_replace]" placeholder="e.g. /items/detail/${1}">
				</div>
				<div class="col-xs-12 mt-15">
                    <ul class="nav nav-tabs display-ib w-100pr" role="tablist">
                        <?php echo $tabs_list;?>
                    </ul>
				</div>
				<div class="col-xs-12 mt-15">
                    <div class="tab-content">
                        <?php echo $tabs_content;?>
                    </div>
				</div>
			</div>
		</div>
        <div class="modal-b__btns clearfix">
            <button class="btn btn-success pull-right call-function" data-callback="translation_add_route" type="button">Submit</button>
        </div>
   </form>
</div>
<script>
	var translation_add_route = function(btn){
        var $this = $(btn);
        var $form = $this.closest('form');
        $.ajax({
            url: '<?php echo __SITE_URL ?>translations/ajax_operations/add_route',
            type: 'POST',
            dataType: 'json',
            data: $form.serialize(),
            beforeSend: function () {
                showLoader($form);
            },
            success: function(data){
                systemMessages( data.message, 'message-' + data.mess_type );

                if(data.mess_type == 'success'){
                    translations_routes_callback();
                    closeFancyBox();                    
                } else{
                    hideLoader($form);
                }
            }
        });
    }
</script>
