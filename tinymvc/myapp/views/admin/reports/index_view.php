<div class="row">
    <div class="col-xs-4 col-xs-offset-2">
        <form action="" id="js-form-reports">
            <ul class="list-group">
                <li class="list-group-item">
                    <label class="txt-blue">
                        Report
                    </label>
                    <select class="w-100pr mt-5 validate[required]" name="report">
                        <option value="">Select report</option>
                        <?php foreach ($reports as $report) {?>
                            <option value="<?php echo $report['id_report']?>"><?php echo cleanOutput($report['title']);?></option>
                        <?php }?>
                    </select>
                </li>
                <li class="list-group-item">
                    <label class="txt-blue">
                        User registration date
                    </label>
                    <div class="w-100pr mt-5 input-group">
                        <span class="input-clean-btn" style="display: flex; position: relative; width:100%">
                            <i class="ep-icon ep-icon_remove-stroke" id="js-reset_reg_date_from" style="position: absolute;right:0;top: 3px;z-index: 1;padding: 5px 8px 5px 5px;cursor: pointer;color: #2181F8;"></i>
                            <input class="form-control date-picker" type="text" name="reg_date_from" placeholder="From">
                        </span>
                        <div class="input-group-addon">-</div>
                        <span class="input-clean-btn" style="display: flex; position: relative; width:100%">
                            <i class="ep-icon ep-icon_remove-stroke" id="js-reset_reg_date_to"  style="position: absolute;right:0;top: 3px;z-index: 1;padding: 5px 8px 5px 5px;cursor: pointer;color: #2181F8;"></i>
                            <input class="form-control date-picker" type="text" name="reg_date_to" placeholder="To">
                        </span>
                    </div>
                </li>
                <li class="list-group-item">
                    <label class="txt-blue">
                        Focus Countries
                    </label>
                    <select class="w-100pr mt-5 validate[required]" name="focus_countries">
                        <option value="0">All countries</option>
                        <option value="1">Only focus countries</option>
                    </select>
                </li>
            </ul>

            <div class="mt-15">
                <button class="btn btn-primary" id="js-download-report-btn" type="button">Download report</button>
            </div>
        </form>
    </div>
    <div class="col-xs-4 bd-1-gray">
        <table class="table-bordered w-100pr mt-15 mb-15 vam-table">
            <tbody class="tabMessage">
                <tr class="tr-doc">
                    <td class="vam">
                        List of the subscribers
                    </td>
                    <td class="vam tar td-actions">
                        <button class="btn btn-primary call-function" data-callback="export_subscribers" type="button">Download</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="class-xs-2">
        <iframe src="" id="download_report_xls_file"></iframe>
    </div>
</div>
<script>
    $(function() {
        $('#js-download-report-btn').on('click', function(){
            var $id_report = $('select[name="report"]').val();
            if ('' == $id_report) {
                systemMessages('Please select the report type first.', 'message-warning');
                return;
            }

            var url = '<?php echo __SITE_URL . "reports/download_report?";?>' + $('#js-form-reports').serialize();

            $('#download_report_xls_file').attr('src', url);
        });

        $('.date-picker').datepicker();

        $('input[name="reg_date_to"').on('change', function(){
            $('input[name="reg_date_from"]').datepicker("option", "maxDate", $('input[name="reg_date_to"]').datepicker("getDate"));
        })

        $('input[name="reg_date_from"').on('change', function(){
            $('input[name="reg_date_to"]').datepicker("option", "minDate", $('input[name="reg_date_from"]').datepicker("getDate"));
        })

        $('#js-reset_reg_date_from').on('click', function(){
            $('input[name="reg_date_to"]').datepicker( "option" , {
                minDate: null
            });

            $('input[name="reg_date_from"]').datepicker('setDate', null);
        });

        $('#js-reset_reg_date_to').on('click', function(){
            $('input[name="reg_date_from"]').datepicker( "option" , {
                maxDate: null,
            });

            $('input[name="reg_date_to"]').datepicker('setDate', null);
        });
    });

    var export_subscribers = function(){
        var url = '<?php echo __SITE_URL . "reports/export_subscribers";?>';

        $('#download_report_xls_file').attr('src', url);
    }
</script>
