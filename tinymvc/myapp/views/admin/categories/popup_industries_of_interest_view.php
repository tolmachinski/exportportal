<div class="wr-form-content w-700 mh-600">
    <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr mt-15 mb-15 vam-table">
        <thead>
            <tr role="row">
                <th class="tal">Industry</th>
                <th class="tac w-100"><?php echo empty($userId) ? 'Total users' : 'Total views';?></th>
            </tr>
        </thead>
        <tbody class="tabMessage">
            <?php foreach ($industriesStatistics as $industry){?>
                <tr>
                    <td class="tal"><?php echo $industry['name'];?></td>
                    <td class="tac">
                        <a href="#" class="call-function" data-callback="set_industry_filter" data-industry="<?php echo $industry['industry_id'];?>">
                            <span class="badge">
                                <?php echo $industry['users_interested_in'];?>
                            </span>
                        </a>
                    </td>
                </tr>
            <?php }?>
        </tbody>
    </table>
</div>
<script>
    var set_industry_filter = function(element){
        var $this = $(element);
        var industry = $this.data('industry');

        $('select[name="industry"]').val(industry).change();
    }
</script>
