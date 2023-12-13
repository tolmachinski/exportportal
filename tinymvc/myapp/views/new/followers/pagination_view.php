<div class="order-list-pag">
	<div class="order-list-pag__total">
		<span class="order-list-pag__txt">Total</span>
		<span id="total_orders_count_by_status" <?php echo addQaUniqueIdentifier('followers-my__item_total-counter') ?>><?php echo $status_select_count;?></span>
	</div>
	<div class="order-list-pag__number">
		<button class="order-list-pag__number-prev btn btn-light btn-sm"><i class="ep-icon ep-icon_arrow-left"></i></button>
		<span class="order-list-pag__number-text">Page</span>
		<select class="order-list-pag__number-list">
			<option>1</option>
		</select>

		<span class="order-list-pag__number-text">
			of <span class="order-list-pag__number-total" <?php echo addQaUniqueIdentifier('followers-my__item_total-counter') ?>>1</span>
		</span>
		<button class="order-list-pag__number-next btn btn-light btn-sm"><i class="ep-icon ep-icon_arrow-right"></i></button>
	</div>
</div>
