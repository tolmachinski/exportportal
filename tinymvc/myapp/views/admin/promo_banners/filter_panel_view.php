<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel w-350">
        <div class="title-b">Filter panel</div>
        <table>
			<tr>
				<td>Search</td>
				<td>
					<input class="dt_filter" type="text" data-title="Search for" name="keywords" placeholder="Search for ...">
				</td>
			</tr>
			<tr>
				<td>Added date</td>
				<td>
					<div class="input-group">
						<input class="form-control js-date-filter dt_filter" type="text" data-title="Banner added from" name="added_from" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control js-date-filter dt_filter" type="text" data-title="Banner added to" name="added_to" placeholder="To" readonly>
					</div>
				</td>
			</tr>
            <tr>
				<td>Updated date</td>
				<td>
					<div class="input-group">
						<input class="form-control js-date-filter dt_filter" type="text" data-title="Banner update from" name="updated_from" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control js-date-filter dt_filter" type="text" data-title="Banner update to" name="updated_to" placeholder="To" readonly>
					</div>
				</td>
			</tr>
			<tr>
				<td>Position</td>
				<td>
                    <div class="mb-10">
                        <select
                            id="js-filter-select-id-page"
                            class="dt_filter"
                            data-title="Position page"
                            name="page_selection"
                        >
                            <option data-default="true" value="">All pages</option>
                            <?php foreach($byPages as $byPagesItem){?>
                                <option
                                    value="<?php echo $byPagesItem['id_page']; ?>"
                                ><?php echo $byPagesItem['page_name']; ?></option>
                            <?php }?>
                        </select>
                    </div>
                    <div>
                        <select
                            id="js-filter-select-id-page-position"
                            class="dt_filter"
                            data-title="Position page"
                            name="page_position"
                        >
                            <option data-default="true" value="" disabled selected>Select position</option>
                        </select>
                    </div>
				</td>
			</tr>
        </table>
        <div class="wr-filter-list clearfix mt-10"></div>
    </div>
    <div class="btn-display ">
        <div class="i-block"><i class="ep-icon ep-icon_filter"></i></div>
        <span>&laquo;</span>
    </div>

    <div class="wr-hidden"></div>
</div>

<?php foreach($byPages as $byPagesItem){ ?>
    <script type="text/template" id="js-filter-page-position-templates-<?php echo $byPagesItem['id_page']?>">
        <option data-default="true" value="" disabled selected>Select position</option>
        <?php foreach($byPagesItem['positions'] as $byPagesPositionItem){?>
            <option value="<?php echo $byPagesPositionItem['id_page_position']; ?>"><?php echo $byPagesPositionItem['position_name']; ?></option>
        <?php }?>
    </script>
<?php } ?>

<script>
    $(function(){
        var $position = $('#js-filter-select-id-page-position');

        $('body').on('change', '#js-filter-select-id-page', function(){
            var $this = $(this);
            var $template = $('#js-filter-page-position-templates-' + $this.val());

            $position.prop('disabled', false).html($template.html());
        });

        $(".filter-admin-panel .js-date-filter" ).datepicker();
    });
</script>
