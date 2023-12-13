<?php if(!empty($b2b_requests)){?>
	<?php foreach($b2b_requests as $key => $request){?>
		<li class="order-users-list__item call-function" data-callback="callGetResponse" data-request="<?php echo $request['id_request'];?>" data-link="<?php echo __SITE_URL;?>b2b/detail/<?php echo strForURL($request['b2b_title']);?>-<?php echo $request['id_request'];?>">
			<div class="order-users-list__number" title="<?php echo $request['b2b_title'];?>"><?php echo $request['b2b_title'];?></div>
			<!-- TODO delete on next stage because there is no id_country in request anymore
                <div class="order-users-list__company">
				<img src="<?php //echo getCountryFlag($countries[$request['id_country']]['country']);?>" alt="<?php //echo $countries[$request['id_country']]['country'];?>" />
				<span class="link"><?php //echo $countries[$request['id_country']]['country'];?></span>
			</div> -->
			<div class="order-users-list__date flex-display flex-jc--sb">
				<span><?php echo formatDate($request['b2b_date_register'], 'm/d/Y');?></span>

				<span><?php echo intval($count_b2b_response[$request['id_request']]['counters']);?></span>
			</div>
			<div class="order-users-list__actions display-n">
				<div class="dropdown">
					<a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
						<i class="ep-icon ep-icon_menu-circles"></i>
					</a>

					<div class="dropdown-menu dropdown-menu-right">
						<a class="dropdown-item" href="<?php echo __SITE_URL;?>b2b/edit/<?php echo $request['id_request'];?>" title="Edit this request" target="_blank">
							<i class="ep-icon ep-icon_pencil"></i>
							<span class="txt">Edit</span>
						</a>
						<a class="dropdown-item confirm-dialog" data-callback="activate_request" data-request="<?php echo $request['id_request'];?>" data-message="Are you sure you want to change status?" href="#"  title="Change request status to <?php echo equalsElse($request['status'], 'enabled', 'invisible', 'visible');?>">
							<i class="ep-icon ep-icon_<?php echo equalsElse($request['status'], 'enabled', 'invisible', 'visible');?>"></i>
							<span class="txt">Change to <span data-request-status="true"><?php echo equalsElse($request['status'], 'enabled', 'invisible', 'visible');?></span></span>
						</a>
						<a class="dropdown-item confirm-dialog" data-callback="remove_request" data-request="<?php echo $request['id_request'];?>" data-message="Are you sure you want to delete this request?" href="#" title="Remove this request">
							<i class="ep-icon ep-icon_trash-stroke"></i>
							<span class="txt">Remove</span>
						</a>
					</div>
				</div>
			</div>
		</li>
	<?php }?>
<?php }else{?>
	<li class="p-0"><div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> You do not have any requests.</div></li>
<?php }?>
