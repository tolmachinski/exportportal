
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/freewall-1-0-6/freewall.js');?>"></script>

<script>
	var wall;
	var nameNavDraggable = '.js-dashboard-nav-customize';
	var nameNavSelected = '.js-dashboard-nav-selected';

$(document).ready(function(){
	wall = new Freewall(".js-dashboard-nav-customize");
	wall.reset({
		selector: '.dashboard-nav-customize__item',
		animate: true,
		cellW: 300,
		gutterX: 100,
		gutterY: 20,
		cellH: 'auto',
		onResize: function() {
			wall.fitWidth();
		}
	});
	wall.fitWidth();

	$('body').on('click', nameNavDraggable + ' .fancyboxValidateModal, ' + nameNavSelected + ' .fancyboxValidateModal', function(e){
		e.preventDefault();
		e.stopPropagation();
	});

	$('body').on('click', nameNavDraggable + ' a, ' + nameNavSelected + ' a', function(e){
		e.preventDefault();
	});

	$(nameNavDraggable).on('click', '.ui-draggable:not(.ui-draggable-disabled) .ico-move', function(e){
		var $thisItem = $(this).closest('li');
		var $dashboardItemEmpty = $(nameNavSelected).find('.item-empty').first();

		if($dashboardItemEmpty.length){
			var nameItem = $thisItem.data('name');
			//droppable item save draggable item and remove class item-empty and add class item-new
			//add remove button droppable item
			$dashboardItemEmpty.html( $thisItem.html() )
				.removeClass('item-empty').addClass('item-new')
				.find('.ico-draggable').before('<span class="ico-remove ep-icon ep-icon_remove-stroke confirm-dialog" data-callback="removeCustomizeItem" data-message="Are you sure you want to remove this item?" data-name="'+nameItem+'" title="Remove item from your customized menu"></span>').end()
				.find('.ico-move').remove();

			//if droppable item is draggable disabled
			if($dashboardItemEmpty.hasClass('ui-draggable-disabled')){
				//droppable item make enable
				$dashboardItemEmpty.draggable('enable');
			}

			//draggable item make disable
			$thisItem.draggable('disable');
			//init draggable customize all nav items
			draggCustomizeInit();
		}else{
			systemMessages( 'Customized menu is filled.', 'info' );
		}
	});

	//init draggable all nav items
	$(nameNavDraggable + ' li').draggable({
		helper: "clone",
		scroll: true,
		scrollSensitivity: 100,
		zIndex: 2,
		start: function( event, ui ) {
			//all nav clone item and add class change color, width
			var widthItem = $(this).css('width');
			$(this).siblings('.ui-draggable-dragging').css('width', widthItem).addClass('bg-gray-light');
		},
		stop: function( event, ui ) {	}
	});

	//init droppable customize all nav items
	$( nameNavSelected + " li.item-empty" ).droppable({
		drop: function( event, ui ) {
			//if draggable item is in customize nav
			if(ui.draggable.hasClass('item-new')){
				//if droppable item is empty
				if($(this).hasClass('item-empty')){
					//droppable item save draggable item and add classes item-new ui-draggable and add class item-empty
					$(this).html( ui.draggable.html() ).addClass('item-new ui-draggable').removeClass('item-empty');

					//if droppable item is draggable disabled
					if($(this).hasClass('ui-draggable-disabled')){
						//droppable item make enable
						$(this).draggable('enable');
					}

					//draggable item make draggable disable
					ui.draggable.draggable('disable');
					//draggable item clear and remove classes item-new ui-draggable and add class item-empty
					ui.draggable.html('').removeClass('item-new ui-draggable').addClass('item-empty');
					//init draggable customize all nav items
					draggCustomizeInit();

				}else{//item is not empty

					//save droppable item
					var itemReplase = $(this).html();
					//replace droppable item to draggable item
					$(this).html(ui.draggable.html());
					//replace draggable item to droppable item
					ui.draggable.html(itemReplase);
				}

			}else{//draggable item is not in customize nav

				//if droppable item is empty
				if($(this).hasClass('item-empty')){
					//save draggable item name
					var nameItem = ui.draggable.data('name');
					//droppable item save draggable item and remove class item-empty and add class item-new
					//add remove button droppable item
					$(this).html( ui.draggable.html() )
						.removeClass('item-empty').addClass('item-new')
						.find('.ico-draggable').before('<span class="ico-remove ep-icon ep-icon_remove-stroke confirm-dialog" data-callback="removeCustomizeItem" data-message="Are you sure you want to remove this item?" data-name="'+nameItem+'" title="Remove item from your customized menu"></span>').end()
						.find('.ico-move').remove();

					//if droppable item is draggable disabled
					if($(this).hasClass('ui-draggable-disabled')){
						//droppable item make enable
						$(this).draggable('enable');
					}

					//draggable item make disable
					ui.draggable.draggable('disable');
					//init draggable customize all nav items
					draggCustomizeInit();

				}else{//droppable item is not empty

					systemMessages( 'Item not empty.', 'info' );
				}
			}

			//droppable item remove class bg-blue2
			$(this).removeClass('bg-blue2');

		},
		over: function( event, ui ) {
			//droppable item add class bg-blue2
			$(this).addClass('bg-blue2');
		},
		out: function( event, ui ) {
			//droppable item remove class bg-blue2
			$(this).removeClass('bg-blue2');
		}
    });

	var navByGroup = JSON.parse('<?php echo $custom_menu;?>');

	$.each(navByGroup, function (index, item) {
		//disable selected draggable item
		$(nameNavDraggable + ' li[data-name='+item.name+']').draggable('disable');
        //create selected droppable item
        var link = item.external_link ? item.external_link : item.link;

		var labelNew = ``;
		if (item.new) {
			labelNew = `<span class="dashboard-nav__item-new">NEW</span>`;
		}

		$(nameNavSelected + ' .col'+item.col+'-cell'+item.cell)
			.addClass('item-new').removeClass('item-empty')
			.html('<a class="link" href="'+link+'" data-tab="'+item.tab+'" data-name="'+item.name+'">\
						<i class="ep-icon ep-icon_'+item.icon+'"></i>\
						<span class="txt-b">'+item.title+'</span>\
						' + labelNew + '\
					</a>\
					<div class="actions">\
						<span class="ico-remove ep-icon ep-icon_remove-stroke confirm-dialog" data-callback="removeCustomizeItem" data-message="Are you sure you want to remove this item?" data-name="'+item.name+'" title="Remove item from your customized menu"></span><span class="ico-draggable ep-icon ep-icon_move" title="Drag to your customized menu"></span>\
					</div>');

		//init draggable customize all nav items
		draggCustomizeInit();
	});
});


//init draggable customize all nav items
function draggCustomizeInit(){
	$( nameNavSelected + " li.item-new" ).draggable({
		helper: "clone",
		zIndex: 2,
		start: function( event, ui ) {
			var widthItem = $(this).css('width');
			$(this).siblings('.ui-draggable-dragging').css('width', widthItem).addClass('bg-gray-light');
		},
		stop: function( event, ui ) {	}
	});
}

//remove droppable item
var removeCustomizeItem = function(obj){
	var $this = $(obj);
	var name = $this.data('name');

	//clear droppable item
	$this.closest('li').html('').removeClass('ui-draggable item-new').addClass('item-empty').draggable('disable');
	//enable draggable item
	$(nameNavDraggable + ' li[data-name='+name+']').draggable('enable');
}

//save customize menu
var saveCustomizeMenu = function(obj){
	var $this = $(obj);
	var selectedNav = [];

	//collect all customize menu info
	$( nameNavSelected + " li.item-new").each(function(){
		var $thisLi = $(this);
		var $thisA = $thisLi.find('a');
		var attrColor = $thisA.attr('data-icon-color');

		selectedNav.push({
			col : $thisLi.data('col'),
			cell : $thisLi.data('cell'),
			tab : $thisA.data('tab'),
			name : $thisA.data('name'),
			new: $thisA.data('new'),
		});
	})

	$.ajax({
		type: 'POST',
		url: 'user/ajax_user_operation/save_menu',
		data: {menu: JSON.stringify(selectedNav)},
		dataType: 'JSON',
		beforeSend: function(){
			showLoader(nameNavSelected, 'Save menu...');
			$this.addClass('disabled');
		},
		success: function(resp){
			hideLoader(nameNavSelected);
			$this.removeClass('disabled');
			systemMessages( resp.message, resp.mess_type );
		}
	});
}

var clearCustomizeMenu = function(obj){
	//collect all customize menu info
	$( nameNavSelected + " li").each(function(){
		var $thisLi = $(this);
		var $thisA = $thisLi.find('a');

		if($thisA.length){
			var name = $thisA.data('name');
			$(nameNavDraggable + ' li[data-name='+name+']').draggable('enable');
			//clear droppable item
			$thisLi.html('').removeClass('ui-draggable item-new').addClass('item-empty').draggable('disable');
			//enable draggable item
		}
	});
}

var dashboardListHide = function(obj){
	var $this = $(obj);

	$this.toggleClass('ep-icon--rotate');
	$this.closest('.dashboard-nav-customize__item').find('.dashboard-nav-customize__links').slideToggle(function(){
		wall.fitWidth();
	});
}
</script>

<div class="container-center-sm dashboard-container">
	<div class="row">
		<div class="col-12">
			<div class="minfo-title">
				<h3 class="minfo-title__name">Menu configurations</h3>
			</div>

			<div class="info-alert-b">
				<i class="ep-icon ep-icon_info-stroke"></i>
				<span><?php echo translate('dashboard_customize_menu_description'); ?></span>
			</div>

			<div class="js-dashboard-nav-customize dashboard-nav-customize dashboard-nav-customize--draggable mb-20">
				<?php tmvc::instance()->controller->view->display('new/dashboard/navigation_list_view');?>
			</div> <!-- --draggable -->

			<div class="row js-dashboard-nav-selected">
				<div class="col-12">
					<div class="minfo-title">
						<h3 class="minfo-title__name">Customize menu</h3>
					</div>
				</div>

				<div class="col-12 col-md-6 col-lg-4">
					<ul class="dashboard-nav-selected__links dashboard-nav">
						<li class="dashboard-nav-selected__links-item dashboard-nav__item item-empty col1-cell1" data-col="1" data-cell="1"></li>
						<li class="dashboard-nav-selected__links-item dashboard-nav__item item-empty col1-cell2" data-col="1" data-cell="2"></li>
						<li class="dashboard-nav-selected__links-item dashboard-nav__item item-empty col1-cell3" data-col="1" data-cell="3"></li>
						<li class="dashboard-nav-selected__links-item dashboard-nav__item item-empty col1-cell4" data-col="1" data-cell="4"></li>
						<li class="dashboard-nav-selected__links-item dashboard-nav__item item-empty col1-cell5" data-col="1" data-cell="5"></li>
						<li class="dashboard-nav-selected__links-item dashboard-nav__item item-empty col1-cell6" data-col="1" data-cell="6"></li>
						<li class="dashboard-nav-selected__links-item dashboard-nav__item item-empty col1-cell7" data-col="1" data-cell="7"></li>
					</ul>
				</div>

				<div class="col-12 col-md-6 col-lg-4">
					<ul class="dashboard-nav-selected__links">
						<li class="dashboard-nav-selected__links-item dashboard-nav__item item-empty col2-cell1" data-col="2" data-cell="1"></li>
						<li class="dashboard-nav-selected__links-item dashboard-nav__item item-empty col2-cell2" data-col="2" data-cell="2"></li>
						<li class="dashboard-nav-selected__links-item dashboard-nav__item item-empty col2-cell3" data-col="2" data-cell="3"></li>
						<li class="dashboard-nav-selected__links-item dashboard-nav__item item-empty col2-cell4" data-col="2" data-cell="4"></li>
						<li class="dashboard-nav-selected__links-item dashboard-nav__item item-empty col2-cell5" data-col="2" data-cell="5"></li>
						<li class="dashboard-nav-selected__links-item dashboard-nav__item item-empty col2-cell6" data-col="2" data-cell="6"></li>
						<li class="dashboard-nav-selected__links-item dashboard-nav__item item-empty col2-cell7" data-col="2" data-cell="7"></li>
					</ul>
				</div>

				<div class="col-12 col-md-6 col-lg-4">
					<ul class="dashboard-nav-selected__links">
						<li class="dashboard-nav-selected__links-item dashboard-nav__item item-empty col3-cell1" data-col="3" data-cell="1"></li>
						<li class="dashboard-nav-selected__links-item dashboard-nav__item item-empty col3-cell2" data-col="3" data-cell="2"></li>
						<li class="dashboard-nav-selected__links-item dashboard-nav__item item-empty col3-cell3" data-col="3" data-cell="3"></li>
						<li class="dashboard-nav-selected__links-item dashboard-nav__item item-empty col3-cell4" data-col="3" data-cell="4"></li>
						<li class="dashboard-nav-selected__links-item dashboard-nav__item item-empty col3-cell5" data-col="3" data-cell="5"></li>
						<li class="dashboard-nav-selected__links-item dashboard-nav__item item-empty col3-cell6" data-col="3" data-cell="6"></li>
						<li class="dashboard-nav-selected__links-item dashboard-nav__item item-empty col3-cell7" data-col="3" data-cell="7"></li>
					</ul>
				</div>

				<div class="col-12 dashboard-nav-selected__actions">
					<a class="btn btn-primary call-function" data-message="Are you sure you want to save customize menu?" data-callback="saveCustomizeMenu">Save menu</a>
					<a class="btn btn-light call-function" data-callback="clearCustomizeMenu">Clear menu</a>
				</div>
			</div>
		</div>
	</div>
</div>
