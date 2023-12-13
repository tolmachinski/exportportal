<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel">
        <div class="title-b">Filter panel</div>
        <table class="w-100pr">
            <tr>
                <td>Title</td>
                <td>
                    <input type="text" class="form-control dt_filter" placeholder="Search by title" name="title">
                </td>
            </tr>
            <tr>
                <td>Type</td>
                <td>
                    <select class="form-control dt_filter" data-title="Type" name="type" data-type="select">
                        <option value="" data-default="true">All types</option>
                        <?php foreach ($eventTypes as $type) {?>
                            <option value="<?php echo $type['id'];?>"><?php echo $type['title'];?></option>
                        <?php }?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Category</td>
                <td>
                    <select id="js-filter-by-categories" class="form-control dt_filter" name="categories" data-title="Category" data-type="select" multiple>
                        <?php foreach ($eventCategories as $category) {?>
                            <option value="<?php echo $category['id'];?>"><?php echo $category['name'];?></option>
                        <?php }?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Recommended by EP</td>
                <td>
                    <select class="form-control dt_filter" data-title="Recommended by EP" name="is_recommended" data-type="select">
                        <option value="" data-default="true">All</option>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Upcoming by EP</td>
                <td>
                    <select class="form-control dt_filter" data-title="Upcoming by EP" name="is_upcoming" data-type="select">
                        <option value="" data-default="true">All</option>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Attended by EP</td>
                <td>
                    <select class="form-control dt_filter" data-title="Attended by EP" name="is_attended" data-type="select">
                        <option value="" data-default="true">All</option>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Promoted</td>
                <td>
                    <select class="form-control dt_filter" data-title="Promoted" name="promoted" data-type="select">
                        <option value="" data-default="true">All</option>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Partners</td>
                <td>
                    <select id="js-filter-by-partners" class="form-control dt_filter" name="partners" data-title="Partners" data-type="select" multiple>
                        <?php foreach ($eventPartners as $partner) {?>
                            <option value="<?php echo $partner['id'];?>"><?php echo $partner['name'];?></option>
                        <?php }?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Speakers</td>
                <td>
                    <select id="js-filter-by-speakers" class="form-control dt_filter" name="speakers" data-title="Speakers" data-type="select" multiple>
                        <?php foreach ($eventSpeakers as $speaker) {?>
                            <option value="<?php echo $speaker['id'];?>"><?php echo $speaker['name'];?></option>
                        <?php }?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Countries</td>
                <td>
                    <select id="js-filter-by-countries" class="form-control dt_filter" name="countries" data-title="Countries" data-type="select" multiple>
                        <?php foreach ($countries as $country) {?>
                            <option value="<?php echo $country['id'];?>"><?php echo $country['country'];?></option>
                        <?php }?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Start date</td>
                <td>
                    <div class="input-group">
						<input class="form-control dt_filter date-picker" type="text" name="start_date_from" data-title="Start from" placeholder="From">
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter date-picker" type="text" name="start_date_to" data-title="Start to" placeholder="To">
					</div>
                </td>
            </tr>
            <tr>
                <td>End date</td>
                <td>
                    <div class="input-group">
						<input class="form-control dt_filter date-picker" type="text" name="end_date_from" data-title="End from" placeholder="From">
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter date-picker" type="text" name="end_date_to" data-title="End to" placeholder="To">
					</div>
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
    $(function(){
        $('.date-picker').datepicker();

        $('#js-filter-by-partners').select2({
            width: '215px',
            multiple: true,
            placeholder: "Select partners"
        });

        $('#js-filter-by-speakers').select2({
            width: '215px',
            multiple: true,
            placeholder: "Select speakers"
        });

        $('#js-filter-by-categories').select2({
            width: '215px',
            multiple: true,
            placeholder: "Select categories"
        });

        $('#js-filter-by-countries').select2({
            width: '215px',
            multiple: true,
            placeholder: "Select countries"
        });
    });
</script>
