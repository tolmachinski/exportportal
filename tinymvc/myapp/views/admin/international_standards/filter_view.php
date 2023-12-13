<div class="wr-filter-admin-panel">
	<div class="filter-admin-panel" >
		<div class="title-b">Filter panel</div>
		<table class="w-100pr">
			<tr>
				<td>Search</td>
				<td>
					<div class="form-group mb-0">
						<div class="input-group">
							<input type="text" name="search" class="dt_filter form-control" value=""  data-title="Search" placeholder="Keywords">
							<span class="input-group-btn">
								<a class="dt-filter-apply dt-filter-apply-buttons btn btn-primary mr-0 mb-0"><i class="ep-icon ep-icon_magnifier "></i></a>
							</span>
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<td>Country</td>
				<td>
					<select class="form-control w-100pr dt_filter" data-title="Country" name="country" data-type="select">
						<option value="">All countries</option>
						<?php foreach($countries as $country){?>
							<option value="<?php echo $country['id'];?>"><?php echo $country['country'];?></option>
						<?php }?>
					</select>
				</td>
			</tr>
		</table>
		<div class="wr-filter-list clearfix mt-10 "></div>
	</div>
	<div class="btn-display">
		<div class="i-block"><i class="ep-icon ep-icon_filter"></i></div>
		<span>&laquo;</span>
	</div>
	<div class="wr-hidden" style="display: none;"></div>
</div>
