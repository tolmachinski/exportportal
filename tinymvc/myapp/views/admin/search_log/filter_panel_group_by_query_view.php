<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel w-350">
        <div class="title-b">Filter panel</div>
        <table>
			<tr>
				<td>Pages</td>
				<td>
					<select class="dt_filter" data-title="Page" name="page">
						<option data-default="true" value="">All pages</option>
						<?php foreach($pages as $page){ ?>
							<option value="<?php echo $page['id_page'];?>"><?php echo $page['page_name'];?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
        </table>
        <div class="wr-filter-list clearfix mt-10 "></div>
    </div>
    <div class="btn-display ">
        <div class="i-block"><i class="ep-icon ep-icon_filter"></i></div>
        <span>&laquo;</span>
    </div>

    <div class="wr-hidden"></div>
</div>

<script>
$(document).ready(function() {
	$(".filter-admin-panel").find("input[name^=date_]" ).datepicker();
});
</script>
