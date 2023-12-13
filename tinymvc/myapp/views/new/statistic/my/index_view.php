<div class="container-center dashboard-container inputs-40">
	<div class="row">
		<div class="col-12">
			<div class="dashboard-line">
				<h1 class="dashboard-line__ttl">
					Statistics
				</h1>
			</div>

			<ul class="list-statistic-dasboard">
			<?php foreach($statistic as $item){?>
				<li class="list-statistic-dasboard__item">
					<div class="list-statistic-dasboard__inner">
						<p class="list-statistic-dasboard__text" title="<?php echo $item['description'];?>"><?php echo $item['description'];?></p>
						<span class="list-statistic-dasboard__counter"><?php echo $item['value'];?></span>
					</div>
				</li>
			<?php }?>
			</ul>
		</div>
	</div>
</div>
