<script src="<?php echo fileModificationTime('public/plug/hidemaxlistitem-1-3-4/hideMaxListItem-min.js'); ?>"></script>
<script src="<?php echo fileModificationTime('public/plug/zabuto-calendar/js/zabuto_calendar.min.js'); ?>"></script>
<script>
$(function(){
	$('.hideMaxList').hideMaxListItems({
		'max': 6,
	});

	$("#my-calendar").zabuto_calendar({
		month : <?php echo formatDate($event['event_date_start'],'n');?>,
		data: <?php echo json_encode($events_date); ?>,
		weekstartson: 0,
		nav_icon: {
			prev: '<i class="ep-icon ep-icon_arrow-left"></i>',
			next: '<i class="ep-icon ep-icon_arrow-right"></i>'
		},
		action: function () {
			var date = $('#' + this.id).data('date');
			var template = '<?php echo __CURRENT_SUB_DOMAIN_URL . 'events/date/._-RPC._-'; ?>';
			document.location.href = template.replace('._-RPC._-', date);
		}
	});
});
</script>

<div id="my-calendar"></div>

<?php if(!empty($events_list)){?>
<h3 class="minfo-sidebar-ttl">
	<span class="minfo-sidebar-ttl__txt">Other events</span>
</h3>

<div class="minfo-sidebar-box">
	<div class="minfo-sidebar-box__desc">
		<ul class="minfo-sidebar-box__list hideMaxList">
			<?php foreach($events_list as $events_list_one){?>
			<li class="minfo-sidebar-box__list-item">
				<a class="minfo-sidebar-box__list-link" href="<?php echo get_dynamic_url($events_list_one['event_url'], __CURRENT_SUB_DOMAIN_URL.'event/'); ?>">
					<?php echo $events_list_one['event_name'];?>
				</a>
			</li>
			<?php }?>
		</ul>
	</div>
</div>
<?php }?>

<?php
	tmvc::instance()->controller->view->display('new/who_we_are_view');
?>

<?php views()->display('new/subscribe/subscribe_view'); ?>
