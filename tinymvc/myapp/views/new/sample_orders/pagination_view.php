<div class="order-list-pag" id="order-samples--pagination">
        <div class="order-list-pag__total">
		<span class="order-list-pag__txt">Total</span>
		<span class="js-total-text"><?php echo cleanOutput($paginator['total'] ?? 0); ?></span>
	</div>
	<div class="order-list-pag__number">
		<button class="order-list-pag__number-prev btn btn-light btn-sm js-prev-button"><i class="ep-icon ep-icon_arrow-left"></i></button>
		<span class="order-list-pag__number-text">Page</span>
		<select class="order-list-pag__number-list js-pages-list" disabled>
			<option>1</option>
		</select>

		<span class="order-list-pag__number-text">
			of <span class="order-list-pag__number-total js-pages-total">1</span>
		</span>
		<button class="order-list-pag__number-next btn btn-light btn-sm js-next-button"><i class="ep-icon ep-icon_arrow-right"></i></button>
	</div>
</div>