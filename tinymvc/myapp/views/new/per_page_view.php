<?php $pages_count = ceil($count/$per_p); ?>

<?php if($pages_count){?>
<div class="minfo-perpage">
	<span class="minfo-perpage__ttl"><?php echo translate('general_pagination_show_word'); ?> </span>
	<select class="minfo-select2 minfo-perpage__select" onchange="dropdown(this,'<?php echo $page_link;?>','<?php echo $get_per_p;?>')" name="per_p">
		<option <?php echo selected($per_p, 10 ) ?> value="10">10 / <?php echo translate('general_pagination_page_word'); ?></option>
		<option <?php echo selected($per_p, 20 ) ?> value="20">20 / <?php echo translate('general_pagination_page_word'); ?></option>
		<option <?php echo selected($per_p, 50 ) ?> value="50">50 / <?php echo translate('general_pagination_page_word'); ?></option>
		<option <?php echo selected($per_p, 100 ) ?> value="100">100 / <?php echo translate('general_pagination_page_word'); ?></option>
	</select>
</div>
<?php } ?>
