<div class="wr-modal-flex inputs-40">
	<div class="modal-flex__form">
		<div class="modal-flex__content ep-tinymce-text">
			<div><?php echo $mess;?></div>

			<?php if(!empty($docs)){?>
				<ul class="pl-10 pr-10 pb-0">
					<?php foreach($docs as $docs_item){?>
						<li><?php echo $docs_item['title'];?></li>
					<?php }?>
				</ul>
			<?php }?>

			<?php if(!in_array($status, array('init_decline'))){?>
				<div class="pt-15">
					<?php echo translate('system_message_accreditation_links');?>
				</div>
			<?php }?>
		</div>
		<div class="modal-flex__btns">
			<div class="modal-flex__btns-right">
				<?php if(in_array($status,array('init_decline'))){?>
					<a class="btn btn-primary call-function" data-callback="closeFancyBox" href="#">Ok</a>
				<?php }else{?>
					<a class="btn btn-primary" href="<?php echo __BLOG_URL; ?>">Done</a>
				<?php }?>
			</div>
		</div>
	</div>
</div>