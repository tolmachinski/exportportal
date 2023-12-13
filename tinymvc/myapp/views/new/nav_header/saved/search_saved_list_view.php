<div class="js-epuser-saved-content epuser-popup__overflow mt-30">
	<?php if(!empty($saved_search)){?>
		<ul class="saved-search">
			<?php foreach($saved_search as $saved){?>
				<li class="saved-search__item"><?php $type = array('category' => 'Items','search' => 'Items', 'directory' => 'Companies', 'shippers' => 'Freight Forwarders') ?>
                    <a class="saved-search__name" href="<?php echo $saved['link_search']?>">
                        <div class="saved-search__top">
                            <div class="saved-search__date"><?php echo getDateFormat($saved['date']);?></div>
                            <div class="saved-search__type"><?php echo $type[$saved['type_search']]?></div>
                        </div>
						<div class="saved-search__txt"><?php echo $saved['description_search']?></div>
					</a>

					<div class="dropdown">
						<a class="dropdown-toggle" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<i class="ep-icon ep-icon_menu-circles"></i>
						</a>

						<div class="dropdown-menu">
                            <a
                                class="dropdown-item call-function call-action"
                                data-callback="remove_header_search"
                                data-js-action="saved:remove-header-search"
                                data-search="<?php echo $saved['id_search'];?>"
                                href="#"
                            >
								<i class="ep-icon ep-icon_trash-stroke"></i>
								<span>Remove saved</span>
							</a>
						</div>
					</div>
				</li>
			<?php }?>
		</ul>
	<?php }else{ ?>
		<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> <span>No saved search info.</span></div>
	<?php }?>
</div>

<?php if(!empty($saved_search)){?>
	<div class="js-epuser-saved-page epuser-subline-additional2">
		<div></div>

		<div class="flex-display">
			<?php
				app()->view->display('new/nav_header/pagination_block_view', array(
					'count_total' => $counter,
					'per_page' => $per_page,
					'cur_page' => $curr_page,
					'type' => 'save_search'
				));
			?>
		</div>
	</div>
<?php }?>
