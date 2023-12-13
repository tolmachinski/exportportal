<script type="application/javascript">
	var dtPages;
    var myFilters;

	$(document).ready(function(){
        var langs = JSON.parse('<?php echo !empty($langs) ? json_encode($langs, JSON_UNESCAPED_SLASHES | JSON_HEX_QUOT | JSON_HEX_APOS) : "[]"; ?>');
        var columns = [
            {mData: "dt_template", sClass: "vam tac w-300", aTargets: ['dt_template'], bSortable: false}
        ];

        langs.forEach(function(lang){
            var langKey = "dt_" + lang;

            this.push({
                mData: langKey,
                sClass: "vam tac",
                aTargets: [langKey],
                bSortable: false
            });
        }, columns);

		dtEmailTemplates = $('#dt-email-templates').dataTable({
			sDom: '<"top"lp>rt<"bottom"ip><"clear">',
			bProcessing: true,
			bServerSide: true,
			aoColumnDefs: columns,
			sAjaxSource: "<?php echo __SITE_URL;?>email_templates/administration_dt",
			fnServerData: function(sSource, aoData, fnCallback) {
				if(!myFilters){
                    myFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        container: '.wr-filter-list',
                        callBack: function(){
                            dtPages.fnDraw();
                        }
                    });
                }

                aoData = aoData.concat(myFilters.getDTFilter());
                $.ajax({
                    dataType: 'json',
                    type: "POST",
                    url: sSource,
                    data: aoData,
                    success: function(data, textStatus, jqXHR) {
                        if (data.mess_type == 'error') {
                            systemMessages(data.message, 'message-' + data.mess_type);
                        }

                        fnCallback(data, textStatus, jqXHR);
                    }
                });
			},
			sorting : [],
			sPaginationType: "full_numbers",
		});
	});
</script>
<div class="container-fluid content-dashboard">
	<div class="row">
		<div class="col-xs-12">
			<div class="titlehdr h-30">
				<span>Email Template Files</span>
			</div>
			<div class="wr-filter-list mt-10 clearfix"></div>

			<table id="dt-email-templates" class="data table-bordered table-striped w-100pr dataTable">
                <thead>
                    <tr>
                        <th class="dt_template">Template</th>
                        <?php foreach ($langs as $lang) { ?>
                            <th class="dt_<?php echo $lang; ?>"><?php echo strtoupper($lang) ?></th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody class="tabMessage">
                    <!-- <?php //foreach ($templates as $template => $files) { ?>
                        <tr>
                            <td class="dt_template"><?php //echo cleanOutput($template); ?></td>
                            <?php //foreach ($langs as $key) { ?>
                                <td class="dt_<?php //echo $key; ?>">
                                    <?php //if(isset($files[$key])) { ?>
                                        <i class="ep-icon ep-icon ep-icon_ok txt-green fs-24 lh-24"></i>
                                    <?php //} else { ?>
                                        <i class="ep-icon ep-icon ep-icon_remove txt-red fs-24 lh-24"></i>
                                    <?php //} ?>
                                </td>
                            <?php //} ?>
                        </tr>
                    <?php //} ?> -->
                </tbody>
            </table>
		</div>
	</div>
</div>
