<script type="text/javascript" src="<?php echo __FILES_URL;?>public/plug_admin/jquery-nestable/jquery.nestable.js?<?php echo time();?>"></script>
<script type="text/javascript">
var filter_data = {};
var generate_pdf = function(obj){
	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>user_guide/doc2pdf',
		data: filter_data,
		dataType: 'json',
		beforeSend: function(){
            $('.full_block').addClass('h-100 hidden-b');
			showLoader('.full_block', 'Generating PDF file...');
		},
		success: function(resp){
            systemMessages(resp.message, 'message-' + resp.mess_type);
            $('.full_block').removeClass('h-100 hidden-b');
			hideLoader('.full_block');
		}
	});
}
var remove_menu = function(obj){
	var $this = $(obj);
	var menu = $this.data('menu');

	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL . 'user_guide/ajax_admin_operations/remove_menu';?>',
		data: {menu: menu},
		beforeSend: function(){ },
		dataType: 'json',
		success: function(resp){
			if(resp.mess_type == 'success'){
                get_menu();
			} else{
                systemMessages(resp.message, 'message-' + resp.mess_type);
            }
		}
	});
}

var menu_actualize = function(obj){
	$.ajax({
		type: 'POST',
		url: 'user_guide/menu_actualize',
		dataType: 'JSON',
		beforeSend: function(){
			showLoader('.full_block');
		},
		success: function(json){
			systemMessages(json.message, 'message-' + json.mess_type);
			hideLoader('.full_block');
		},
		error: function(){alert('ERROR')}
	});
}

var filter_by_user_group = function(obj){
	var $this = $(obj);
    $this.closest('li').addClass('active').siblings().removeClass('active');
    var user_type = $this.data('usertype');
    if(user_type != ''){
        filter_data.user_group = user_type;
    } else{
        delete filter_data.user_group;
    }

	get_menu();
}

function get_menu(){
    $.ajax({
        type: 'POST',
        url: '<?php echo __SITE_URL ?>user_guide/ajax_admin_operations/get_menu',
        dataType: 'json',
        data: filter_data,
        success: function(resp){
            if(resp.mess_type == 'success'){
                $('.dd').replaceWith('<div class="dd">'+resp.menu_html+'</div>').promise().done(function(){
                    $('.dd')
                    .nestable()
                    .on('change', function(e) {
                        var fdata = $('.dd').nestable('serialize');
                        $.ajax({
                            type: 'POST',
                            url: '<?php echo __SITE_URL ?>user_guide/ajax_admin_operations/update_menu',
                            dataType: 'json',
                            data: {menu_list : fdata},
                            success: function(resp){
                                if(resp.mess_type == 'error'){
                                    systemMessages( resp.message, 'message-' + resp.mess_type );
                                }
                            }
                        });
                    });
                });
            }
        }
    });
}

function _actions_document_callback(){
    get_menu();
}
$(document).ready(function(){
    get_menu();
});
</script>
<style>
.dd { position: relative; display: block; margin: 0; padding: 0; max-width: 100%; list-style: none; font-size: 13px; line-height: 20px; }

.dd-list { display: block; position: relative; margin: 0; padding: 0; list-style: none; }
.dd-list .dd-list { padding-left: 30px; }
.dd-collapsed .dd-list { display: none; }

.dd-item,
.dd-empty,
.dd-placeholder { display: block; position: relative; margin: 0; padding: 0; min-height: 20px; font-size: 13px; line-height: 20px; }

.dd-handle { display: block; height: 30px; margin: 5px 0; padding: 5px 10px; color: #333; text-decoration: none; font-weight: bold; border: 1px solid #ccc;
    background: #fafafa;
}
.dd-handle:hover { color: #2ea8e5; background: #fff; cursor: pointer;}

.dd-item > span { cursor: pointer; float: left; width: 25px; height: 20px; margin: 5px; padding: 0;line-height: 20px;text-align: center;}

.dd-placeholder,
.dd-empty { margin: 5px 0; padding: 0; min-height: 30px; background: #f2fbff; border: 1px dashed #b6bcbf; box-sizing: border-box; -moz-box-sizing: border-box; }
.dd-empty { border: 1px dashed #bbb; min-height: 100px; background-color: #e5e5e5;}

.dd-dragel { position: absolute; pointer-events: none; z-index: 9999; }
.dd-dragel > .dd-item .dd-handle { margin-top: 0; }
.dd-actions {position: absolute; right: 5px; top: 5px;}
</style>
<div class="row">
	<div class="col-xs-12 full_block">
		<div class="titlehdr h-30">
			<span>EP Documentation</span>
			<a class="fancyboxValidateModal fancybox.ajax pull-right ep-icon ep-icon_plus-circle txt-green" href="<?php echo __SITE_URL;?>user_guide/popup_forms/add_guide" data-title="Add user guide menu" title="Add user guide menu"></a>
			<a class="ep-icon ep-icon_branches txt-blue pull-right mr-5 confirm-dialog" data-callback="menu_actualize" title="Actualize" data-message="Are you sure you want to actualize documentation menu?" href="#"></a>
			<a class="ep-icon ep-icon_file txt-blue pull-right mr-5 confirm-dialog" data-callback="generate_pdf" title="Generate PDF" data-message="Are you sure you want to generate the documentation pdf file?" href="#"></a>
		</div>
        <ul class="menu-level3 mb-10 clearfix">
			<li><a class="call-function" data-callback="filter_by_user_group" data-usertype="" href="#">All</a></li>
			<li><a class="call-function" data-callback="filter_by_user_group" data-usertype="buyer" href="#">Buyer</a></li>
			<li><a class="call-function" data-callback="filter_by_user_group" data-usertype="seller" href="#">Seller</a></li>
			<li><a class="call-function" data-callback="filter_by_user_group" data-usertype="shipper" href="#">Freight Forwarder</a></li>
			<li><a class="call-function" data-callback="filter_by_user_group" data-usertype="admin" href="#">Admin</a></li>
		</ul>
        <div class="dd"></div>
	 </div>
</div>
