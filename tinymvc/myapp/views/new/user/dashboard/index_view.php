<?php $session = tmvc::instance()->controller->session; ?>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/freewall-1-0-6/freewall.js');?>"></script>

<script type="text/javascript">
var wall;
var wallStatistic;

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

	wallStatistic = new Freewall(".dashboard-statistic");
	wallStatistic.reset({
		selector: '.dashboard-statistic__item',
		animate: true,
		cellW: 300,
		gutterX: 100,
		gutterY: 20,
		cellH: 'auto',
		onResize: function() {
			wallStatistic.fitWidth();
		}
	});
	wallStatistic.fitWidth();
});

var dashboardListHide = function(obj){
	var $this = $(obj);
	var $wr = $this.closest('.js-dashboard-nav-customize');

	showLoader($wr, 'Updating...');

	$this.toggleClass('ep-icon--rotate');
	$this.closest('.dashboard-nav-customize__item').find('.dashboard-nav-customize__links').slideToggle(function(){
		wall.fitWidth();
		hideLoader($wr);
	});
}
</script>

<div class="container-center-sm dashboard-container">
	<div class="row">
		<div class="col-12">
			<div  class="title-public pt-0">
				<h1 class="title-public__txt">Manage My Account</h1>
			</div>

			<div class="info-alert-b">
				<i class="ep-icon ep-icon_info-stroke"></i>
				<span><?php echo translate('dashboard_description'); ?></span>
			</div>

			<h3 class="dashboard-statistic-ttl pt-10">Statistics</h3>

			<div class="dashboard-statistic pb-50">
			<?php if(have_right('sell_item')){?>
				<div class="dashboard-statistic__item"><span>Active items</span><i>(<?php echo intval($statistic['items_active'])?>)</i></div>
				<div class="dashboard-statistic__item"><span>Products sold</span><i>(<?php echo intval($statistic['items_sold'])?>)</i></div>
				<div class="dashboard-statistic__item"><span>B2B partners</span><i>(<?php echo intval($statistic['b2b_partners'])?>)</i></div>
			<?php }?>
			<?php if(have_right('buy_item')){?>
				<div class="dashboard-statistic__item"><span>Total orders</span><i>(<?php echo intval($statistic['orders_total'])?>)</i></div>
				<div class="dashboard-statistic__item"><span>Items bought</span><i>(<?php echo intval($statistic['items_bought'])?>)</i></div>
				<div class="dashboard-statistic__item"><span>Item reviews written</span><i>(<?php echo intval($statistic['item_reviews_wrote'])?>)</i></div>
				<div class="dashboard-statistic__item"><span>Item questions written</span><i>(<?php echo intval($statistic['item_questions_wrote'])?>)</i></div>
			<?php }?>
				<div class="dashboard-statistic__item"><span>Item comments written</span><i>(<?php echo intval($statistic['item_comments_wrote'])?>)</i></div>
				<div class="dashboard-statistic__item"><span>Feedback written</span><i>(<?php echo intval($statistic['feedbacks_wrote'])?>)</i></div>

			<?php if(have_right('buy_item')){?>
				<div class="dashboard-statistic__item"><span>Offers sent</span><i>(<?php echo intval($statistic['offers_sent'])?>)</i></div>
				<div class="dashboard-statistic__item"><span>Inquiries sent</span><i>(<?php echo intval($statistic['inquiries_sent'])?>)</i></div>
				<div class="dashboard-statistic__item"><span>Producing Requests sent</span><i>(<?php echo intval($statistic['po_sent'])?>)</i></div>
				<div class="dashboard-statistic__item"><span>Estimates sent</span><i>(<?php echo intval($statistic['estimates_sent'])?>)</i></div>
			<?php }?>
				<div class="dashboard-statistic__item"><span>Users followed</span><i>(<?php echo intval($statistic['follow_users'])?>)</i></div>
				<div class="dashboard-statistic__item"><span>My followers</span><i>(<?php echo intval($statistic['followers_user'])?>)</i></div>
				<div class="dashboard-statistic__item"><span>Questions to Export Portal written</span><i>(<?php echo intval($statistic['ep_questions_wrote'])?>)</i></div>
				<div class="dashboard-statistic__item"><span>Items saved</span><i>(<?php echo intval($statistic['items_saved'])?>)</i></div>
				<div class="dashboard-statistic__item"><span>Blog articles written</span><i>(<?php echo intval($statistic['blogs_wrote'])?>)</i></div>
			</div>

			<div class="js-dashboard-nav-customize dashboard-nav-customize dashboard-nav-customize--nohover">
				<?php tmvc::instance()->controller->view->display('new/dashboard/navigation_list_view'); ?>
			</div>
		</div>
	</div>
</div>
