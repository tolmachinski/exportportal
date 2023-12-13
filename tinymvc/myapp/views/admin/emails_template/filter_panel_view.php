<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel">
        <div class="title-b">Filter panel</div>
        <table>
            <tr>
                <td>Search by</td>
                <td>
                    <input class="dt_filter" type="text" data-title="Search for" name="keywords" maxlength="50" placeholder="Keywords">
                </td>
            </tr>
            <tr>
                <td>Email structure</td>
                <td>
                    <select class="dt_filter" name="email_structure" data-title="Email structure">
                        <option value="" data-default="true">Select email structure</option>
                        <?php foreach($emailsTemplateStructure as $emailsTemplateStructureItem){?>
                            <option
                                value="<?php echo $emailsTemplateStructureItem['id_emails_template_structure']?>"
                            >
                                <?php echo $emailsTemplateStructureItem['name']?>
                            </option>
                        <?php }?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Verified</td>
                <td>
                    <div class="input-group input-group--checks">
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="proofread" data-title="Status" data-value-text="All" value="" checked="checked">
							<span class="input-group__desc">All</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="proofread" data-title="Status" data-value-text="Yes" value="1">
							<span class="input-group__desc">Yes</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="proofread" data-title="Status" data-value-text="No" value="0">
							<span class="input-group__desc">No</span>
						</label>
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
    $(function(){
        $(".date_interval").datepicker();
    });
</script>
