<?php foreach($feedbacks as $feedback) {?>
	<li
        class="product-comments__item"
        <?php echo addQaUniqueIdentifier('global__company-external-feedbacks__item'); ?>
    >
		<div class="product-comments__ttl flex-ai--c">
			<div class="">
				<div
                    class="product-comments__name"
                    <?php echo addQaUniqueIdentifier('global__company-external-feedbacks__item_user-name'); ?>
                >
					<?php echo $feedback['full_name'];?>
				</div>
				<div
                    class="product-comments__date tal"
                    <?php echo addQaUniqueIdentifier('global__company-external-feedbacks__item_date'); ?>
                ><?php echo getDateFormat($feedback['create_date'], null, 'M d, Y');?></div>
			</div>

			<div class="product-comments__rating">
				<div class="product-comments__rating-center bg-green"><?php echo $feedback['rating'];?></div>
				<input class="rating-bootstrap" data-filled="ep-icon ep-icon_diamond txt-green fs-12" data-empty="ep-icon ep-icon_diamond txt-gray-light fs-12" type="hidden" name="val" value="<?php echo $feedback['rating'];?>" data-readonly>
			</div>
		</div>

		<div
            class="product-comments__text"
            <?php echo addQaUniqueIdentifier('global__company-external-feedbacks__item_text'); ?>
        ><?php echo $feedback['description'];?></div>
	</li>
<?php }?>
