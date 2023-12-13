<?php $session = tmvc::instance()->controller->session;?>

<script>
$(document).ready(function(){
	navHeaderMenu(JSON.parse('<?php echo $custom_header_menu;?>'));
});
</script>

<div class="header-nav-widget__top clearfix">
	<div class="pull-left lh-37 fs-16 txt-blue txt-bold">
		<span ><?php echo user_name_session();?></span>
		(<?php echo group_name_session();?>)
	</div>
</div> <!-- header-nav-widget__top -->

<div class="row dashboard-nav-b">
	<div class="col-xs-3">
		<div class="personal-account-item-nav">
			<ul>
				<li class="col1-cell1"></li>
				<li class="col1-cell2"></li>
				<li class="col1-cell3"></li>
				<li class="col1-cell4"></li>
				<li class="col1-cell5"></li>
				<li class="col1-cell6"></li>
				<li class="col1-cell7"></li>
				<li class="col1-cell8"></li>
			</ul>
		</div>
	</div>
	<div class="col-xs-3">
		<div class="personal-account-item-nav">
			<ul>
				<li class="col2-cell1"></li>
				<li class="col2-cell2"></li>
				<li class="col2-cell3"></li>
				<li class="col2-cell4"></li>
				<li class="col2-cell5"></li>
				<li class="col2-cell6"></li>
				<li class="col2-cell7"></li>
				<li class="col2-cell8"></li>
			</ul>
		</div>
	</div>
	<div class="col-xs-3">
		<div class="personal-account-item-nav">
			<ul>
				<li class="col3-cell1"></li>
				<li class="col3-cell2"></li>
				<li class="col3-cell3"></li>
				<li class="col3-cell4"></li>
				<li class="col3-cell5"></li>
				<li class="col3-cell6"></li>
				<li class="col3-cell7"></li>
				<li class="col3-cell8"></li>
			</ul>
		</div>
	</div>
	<div class="col-xs-3">
		<div class="personal-account-item-nav">
			<ul>
				<li class="col4-cell1"></li>
				<li class="col4-cell2"></li>
				<li class="col4-cell3"></li>
				<li class="col4-cell4"></li>
				<li class="col4-cell5"></li>
				<li class="col4-cell6"></li>
				<li class="col4-cell7"></li>
				<li class="col4-cell8"></li>
			</ul>
		</div>
	</div>
</div>
