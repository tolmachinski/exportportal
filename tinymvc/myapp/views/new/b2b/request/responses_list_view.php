<?php $status_icons = array(
		'new' => array('ico' => 'new-stroke', 'title' => 'New'),
		'approved' => array('ico' => 'ok-circle', 'title' => 'approved'),
		'declined' => array('ico' => 'remove-circle', 'title' => 'Declined')
	);?>

<?php foreach($responses as $key => $response){?>
    <?php $companyDataUrl = [
        'index_name'    => $response['index_name'],
        'type_company'  => $response['type_company'],
        'name_company'  => $response['name_company'],
        'id_company'    => $response['id_partner'],
    ];?>
	<li class="order-users-list__item" data-response="<?php echo $response['id_response'];?>" data-status="<?php echo $response['status'];?>">
		<div class="flex-card">
			<div class="order-users-list__img image-card2 flex-card__fixed">
				<span class="link">
					<img
						class="image"
						src="<?php echo getDisplayImageLink(array('{ID}' => $response['id_partner'], '{FILE_NAME}' => $response['logo_company']), 'companies.main', array( 'thumb_size' => 1 ));?>"
						alt="<?php echo $response['name_company'];?>"/>
				</span>
			</div>
			<div class="order-users-list__detail flex-card__float">
				<div class="order-users-list__number">
					<span class="flex-display">
						<i class="lh-24 mr-5 ep-icon ep-icon_<?php echo $status_icons[strForURL($response['status'])]['ico'];?>" title="<?php echo $status_icons[strForURL($response['status'])]['title'];?>"></i>
						<a class="txt-black" href="<?php echo getCompanyURL($companyDataUrl);?>" target="_blank"><?php echo $response['name_company'];?></a>
					</span>
				</div>
				<div class="order-users-list__date">Response on: <?php echo formatDate($response['date_partner'], 'm/d/Y');?></div>
				<div class="order-users-list__company">
					<img
                        width="24"
                        height="24"
                        src="<?php echo getCountryFlag($response['country']);?>"
                        alt="<?php echo $response['country'];?>"
                    />
					<span class="link"><?php echo $response['country'];?></span>
				</div>
			</div>

			<div class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
					<i class="ep-icon ep-icon_menu-circles"></i>
				</a>

				<div class="dropdown-menu dropdown-menu-right">
					<?php if($response['status'] == 'new'){?>
						<a class="dropdown-item confirm-dialog btn-decline" data-message="Are you sure you want to decline this response?" data-callback="decline_partnership" data-response="<?php echo $response['id_response'];?>" data-company="<?php echo $response['id_company'];?>" data-partner="<?php echo $response['id_partner'];?>" href="#" title="Decline partnership">
							<i class="ep-icon ep-icon_remove-circle"></i>
							<span class="txt">Decline</span>
						</a>
						<a class="dropdown-item confirm-dialog btn-aprove" data-message="Are you sure you want to make this company your partner?" data-callback="aprove_partnership" data-response="<?php echo $response['id_response'];?>" data-company="<?php echo $response['id_company'];?>" data-partner="<?php echo $response['id_partner'];?>" href="#" title="Approve partnership">
							<i class="ep-icon ep-icon_ok-circle"></i>
							<span class="txt">Approve</span>
						</a>
					<?php } else{?>
						<a class="dropdown-item confirm-dialog" data-message="Are you sure you want to delete this response?" data-callback="remove_partnership" data-response="<?php echo $response['id_response'];?>" data-company="<?php echo $response['id_company'];?>" data-partner="<?php echo $response['id_partner'];?>" href="#" title="remove partnership">
							<i class="ep-icon ep-icon_trash-stroke"></i>
							<span class="txt">Remove</span>
						</a>
					<?php }?>

					<?php if(!empty($response['btnChat'])){ ?>
						<div class="dropdown-divider"></div>
						<?php echo $response['btnChat']; ?>
					<?php }?>
				</div>
			</div>
		</div>

		<div class="order-users-list__message">
			<strong>Message:</strong>
			<?php echo $response['message_partner'];?>
			<a class="ep-icon ep-icon_arrow-down pull-right call-function" data-callback="messageMore" href="#"></a>
		</div>
	</li>
<?php }?>
