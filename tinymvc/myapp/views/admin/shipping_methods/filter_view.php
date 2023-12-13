<div class="wr-filter-admin-panel">
	<div class="filter-admin-panel" >
		<div class="title-b">Filter panel</div>
		<table>
            <tr>
				<td>Search by Title :</td>
				<td>
                <input class="keywords dt_filter" type="text" data-title="Search for" name="keywords" maxlength="50" id="keywords" placeholder="Title or subtitle">
				</td>
			</tr>
            <tr>
				<td>Create date :</td>
				<td>
					<div class="input-group">
						<input class="form-control dt_filter datepicker" type="text" data-title="Create date from" name="create_date_from" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter datepicker" type="text" data-title="Create date to" name="create_date_to" placeholder="To" readonly>
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

<script>
    $(document).ready(
        function() {
		    $('.datepicker').datepicker();
        }
    );
</script>
