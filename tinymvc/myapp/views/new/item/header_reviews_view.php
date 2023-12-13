<div class="average-rating-wr">
	<div class="average-rating">
		<div class="average-rating__detail">
			<h4 class="average-rating__ttl">Average Rating</h4>

			<div class="average-rating__circle" <?php echo addQaUniqueIdentifier('global__rating-circle'); ?>>
				<?php echo $item['rating']?>
			</div>

			<?php if($item['rev_numb'] < 1) { $item['rating'] = 0; } ?>
			<input class="rating-bootstrap" data-filled="ep-icon ep-icon_star txt-orange fs-12" data-empty="ep-icon ep-icon_star txt-gray fs-12" type="hidden" name="val" value="<?php echo $item['rating']?>" data-readonly>

			<div class="average-rating__txt">based on <?php echo $item['rev_numb']?> reviews</div>
		</div>
		<?php $one_mark = 0 === (int) $item['rev_numb'] ? 0 : round(150 / $item['rev_numb']); ?>

		<div class="average-rating__statistic">
		<?php foreach($rank_counters as $mark => $rank_item){ ?>
			<div class="average-rating__statistic-item" data-toggle="popover" data-content="<?php echo $rank_item['count']; ?> rating">
				<div class="average-rating__statistic-name"><?php echo $rank_item['name']; ?></div>
				<div class="average-rating__statistic-line">
					<div class="average-rating__statistic-line-bg" style="width:<?php echo ($one_mark * $rank_item['count']); ?>px"></div>
				</div>
			</div>
		<?php } ?>
		</div>
	</div>
</div>
