<?php tmvc::instance()->controller->view->display('new/two_mobile_buttons_view'); ?>

<div class="row">
	<div class="col-12 col-md-6">
		<div class="ei-statistic-item">
			<h2 class="ei-statistic-item__ttl">
				Exports
			</h2>
			<div class="ei-statistic-item__desc">
				<?php echo $info['export_text']; ?>
			</div>

			<div class="ei-statistic-chart">
				<?php foreach ($plotData['topExportProducts'] as $item) { ?>
					<div class="ei-statistic-chart__item">
						<div class="ei-statistic-chart__percent"><?php echo number_format($item['percent'], 1); ?>%</div>
						<div class="ei-statistic-chart__line">
							<div class="ei-statistic-chart__name"><?php echo $item['name']; ?></div>
							<span class="ei-statistic-chart__bg" data-width="<?php echo number_format($item['percent'], 1); ?>"></span>
						</div>
					</div>
				<?php } ?>
				<div class="ei-statistic-chart__grid">
					<div class="ei-statistic-chart__grid-white">
						<span class="nr">0%</span>
					</div>
					<div class="ei-statistic-chart__grid-points">
						<div class="ei-statistic-chart__grid-line">
							<span class="nr">50%</span>
						</div>
						<div class="ei-statistic-chart__grid-line">
							<span class="nr">100%</span>
						</div>
						<div class="ei-statistic-chart__grid-percent"></div>
					</div>
				</div>
			</div>

			<div class="ei-statistic-subchart">
				Top 15 export products
			</div>
		</div>
	</div>
	<div class="col-12 col-md-6">
		<div class="ei-statistic-item">
			<h2 class="ei-statistic-item__ttl">
				Imports
			</h2>
			<div class="ei-statistic-item__desc">
				<?php echo $info['import_text']; ?>
			</div>

			<div class="ei-statistic-chart">
				<?php foreach ($plotData['topImportProducts'] as $item) { ?>
					<div class="ei-statistic-chart__item">
						<div class="ei-statistic-chart__percent"><?php echo number_format($item['percent'], 1); ?>%</div>
						<div class="ei-statistic-chart__line">
							<div class="ei-statistic-chart__name"><?php echo $item['name']; ?></div>
							<span class="ei-statistic-chart__bg" data-width="<?php echo number_format($item['percent'], 1); ?>"></span>
						</div>
					</div>
				<?php } ?>
				<div class="ei-statistic-chart__grid">
					<div class="ei-statistic-chart__grid-white">
						<span class="nr">0%</span>
					</div>
					<div class="ei-statistic-chart__grid-points">
						<div class="ei-statistic-chart__grid-line">
							<span class="nr">50%</span>
						</div>
						<div class="ei-statistic-chart__grid-line">
							<span class="nr">100%</span>
						</div>
						<div class="ei-statistic-chart__grid-percent"></div>
					</div>
				</div>
			</div>

			<div class="ei-statistic-subchart">
				Top 15 import products
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-12 col-md-6">
		<div class="ei-statistic-item">
			<h2 class="ei-statistic-item__ttl">
				Destinations
			</h2>
			<div class="ei-statistic-item__desc">
				<?php echo $info['destination_text']; ?>
			</div>

			<div class="ei-statistic-chart">
				<?php foreach ($plotData['topExportCountries'] as $item) { ?>
					<div class="ei-statistic-chart__item">
						<div class="ei-statistic-chart__percent"><?php echo number_format($item['percent'], 1); ?>%</div>
						<div class="ei-statistic-chart__line">
							<div class="ei-statistic-chart__name"><?php echo $item['country']; ?></div>
							<span class="ei-statistic-chart__bg" data-width="<?php echo number_format($item['percent'], 1); ?>"></span>
						</div>
					</div>
				<?php } ?>
				<div class="ei-statistic-chart__grid">
					<div class="ei-statistic-chart__grid-white">
						<span class="nr">0%</span>
					</div>
					<div class="ei-statistic-chart__grid-points">
						<div class="ei-statistic-chart__grid-line">
							<span class="nr">50%</span>
						</div>
						<div class="ei-statistic-chart__grid-line">
							<span class="nr">100%</span>
						</div>
						<div class="ei-statistic-chart__grid-percent"></div>
					</div>
				</div>
			</div>

			<div class="ei-statistic-subchart">
				Top 15 destination countries
			</div>
		</div>
	</div>
	<div class="col-12 col-md-6">
		<div class="ei-statistic-item">
			<h2 class="ei-statistic-item__ttl">
				Origins
			</h2>
			<div class="ei-statistic-item__desc">
				<?php echo $info['origin_text']; ?>
			</div>

			<div class="ei-statistic-chart">
				<?php foreach ($plotData['topImportCountries'] as $item) { ?>
					<div class="ei-statistic-chart__item">
						<div class="ei-statistic-chart__percent"><?php echo number_format($item['percent'], 1); ?>%</div>
						<div class="ei-statistic-chart__line">
							<div class="ei-statistic-chart__name"><?php echo $item['country']; ?></div>
							<span class="ei-statistic-chart__bg" data-width="<?php echo number_format($item['percent'], 1); ?>"></span>
						</div>
					</div>
				<?php } ?>
				<div class="ei-statistic-chart__grid">
					<div class="ei-statistic-chart__grid-white">
						<span class="nr">0%</span>
					</div>
					<div class="ei-statistic-chart__grid-points">
						<div class="ei-statistic-chart__grid-line">
							<span class="nr">50%</span>
						</div>
						<div class="ei-statistic-chart__grid-line">
							<span class="nr">100%</span>
						</div>
					</div>
				</div>
			</div>

			<div class="ei-statistic-subchart">
				Top 15 origin countries
			</div>
		</div>
	</div>
</div>

<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/paroller/jquery.paroller.js'); ?>"></script>

<script>
$(function () {
	$(window).paroller({factor: '0.5', type: 'foreground'});

    $(window).on("scroll load", function(){
        $.each($(".ei-statistic-chart__bg:not(.js-animate)"), function(){
            var that = $(this);
            if(that.offset().top - $(window).scrollTop() - $(window).height() + 25 <= 0){
                that.addClass('js-animate');
                that.css("width", that.data("width") + "%");
            }
        });
    });
});
</script>
