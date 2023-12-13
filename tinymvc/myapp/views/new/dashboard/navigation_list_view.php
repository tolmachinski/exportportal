<?php
	$session = tmvc::instance()->controller->session;
	$nav_tabs = $session->menu_full;
?>

<?php foreach($nav_tabs as $key_tab => $nav_tab_item){?>
	<?php
		if(!empty($nav_tab_item['params']['right']) && !have_right_or($nav_tab_item['params']['right'])){
			continue;
		}
	?>
	<div class="dashboard-nav-customize__item">
		<div class="dashboard-nav-customize__ttl">
			<div class="name"><?php echo $nav_tab_item['params']['title'];?></div>
			<div class="ep-icon ep-icon_remove-stroke call-function" data-callback="dashboardListHide" title="Hide/show items"></div>
		</div>

		<ul class="dashboard-nav-customize__links dashboard-nav">
		<?php foreach($nav_tab_item['items'] as $nav_item_key => $nav_item){?>
			<?php if(!empty($nav_item['right']) && !have_right_or($nav_item['right'])){
					continue;
				} ?>
			<li class="dashboard-nav-customize__links-item dashboard-nav__item" data-name="<?php echo $nav_item_key;?>">
				<a
					class="link <?php echo (isset($nav_item['popup']))?'fancybox.ajax fancyboxValidateModal':''; ?>"
					<?php echo (isset($nav_item['popup']))?'data-title="'.$nav_item['popup'].'"':''; ?>
					<?php echo (isset($nav_item['popup_width']))?'data-mw="'.$nav_item['popup_width'].'"':''; ?>
					<?php echo (!empty($nav_item['target']))?'target="'.$nav_item['target'].'"':''; ?>
					href="<?php echo $nav_item['link'];?>"
					data-name="<?php echo $nav_item_key;?>"
					data-tab="<?php echo $key_tab;?>"
					data-new="<?php echo $nav_item['new'];?>">
					<i class="ep-icon ep-icon_<?php echo $nav_item['icon'];?>"></i>
					<span class="txt-b"><?php echo $nav_item['title'];?></span>
					<?php if ($nav_item['new']) { ?>
						<span class="dashboard-nav__item-new">NEW</span>
					<?php } ?>
				</a>

				<div class="actions">
 					<span class="ico-move ep-icon ep-icon_arrow-line-up" title="Move to customized menu"></span><span class="ico-draggable ep-icon ep-icon_move" title="Drag to your customized menu"></span>
 				</div>
			</li>
		<?php }?>
		</ul>
	</div>
<?php }?>
