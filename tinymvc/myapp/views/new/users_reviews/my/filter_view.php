<div class="container-fluid-modal">
    <label class="input-label">Search by</label>
    <input class="dt_filter" type="text" data-title="Search by" name="keywords" maxlength="50" id="keywords" placeholder="Keywords">

    <label class="input-label">Created</label>
    <div class="row">
        <div class="col-12 col-lg-6 mb-15-sm-max">
            <input class="datepicker-init start_from dt_filter" id="start_from" type="text" placeholder="From" data-title="Created from" name="start_from" placeholder="From" readonly>
        </div>
        <div class="col-12 col-lg-6">
            <input class="datepicker-init start_to dt_filter" id="start_to" type="text" placeholder="To" data-title="Created to" name="start_to" readonly>
        </div>
    </div>

    <label class="input-label">Replied</label>
    <select name="replied" class="dt_filter" data-title="Replied">
        <option value="">All</option>
        <option value="yes">Yes</option>
        <option value="no">No</option>
    </select>

    <!-- HIDDEN FILTERS -->
    <div class="display-n">
        <input class="dt_filter" type="text" data-title="Review number" name="review_number" placeholder="Review number" value="<?php if(isset($id_review)) echo orderNumber($id_review);?>">
        <input class="dt_filter" type="text" data-title="Item number" name="id_item" placeholder="Item number" value="<?php if(isset($id_item)) echo orderNumber($id_item);?>">
        <input class="dt_filter" type="text" data-title="Order number" name="id_order" placeholder="Order number" value="<?php if(isset($id_order)) echo orderNumber($id_order);?>">
    </div>
</div>

<script>
	$(function(){
        $(".datepicker-init").datepicker({
            beforeShow: function (input, instance) {
                $('#ui-datepicker-div').addClass('dtfilter-ui-datepicker');
            },
        });

		window.onpopstate = function(event) {
			location.reload(true);
		};
	});

    function beforeSetFilters(callerObj){
        if(callerObj.prop("name") == 'review_number'){
            var number = toOrderNumber(callerObj.val());
            if(number){
                callerObj.val(number);
            } else{
                systemMessages('Incorrect review number.', 'error' );
                callerObj.val('');
                return false;
            }
        }
        if(callerObj.prop("name") == 'id_item'){
            var number = toOrderNumber(callerObj.val());
            if(number){
                callerObj.val(number);
            } else{
                systemMessages('Incorrect item number.', 'error' );
                callerObj.val('');
                return false;
            }
        }
        if(callerObj.prop("name") == 'id_order'){
            var number = toOrderNumber(callerObj.val());
            if(number){
                callerObj.val(number);
            } else{
                systemMessages('Incorrect order number.', 'error' );
                callerObj.val('');
                return false;
            }
        }
    }

    function onDeleteFilters(filter){
        <?php if(isset($id_review)){?>
            if(filter.name == 'review_number'){
                var url = window.location.href.replace("/review/<?php echo $id_review;?>", "");
                history.pushState({current_url:url}, "", url);
            }
        <?php }?>

        <?php if(isset($id_item)){?>
            if(filter.name == 'id_item'){
                var url = window.location.href.replace("/item/<?php echo $id_item;?>", "");
                history.pushState({current_url:url}, "", url);
            }
        <?php }?>

        <?php if(isset($id_order)){?>
            if(filter.name == 'id_order'){
                var url = window.location.href.replace("/order/<?php echo $id_order;?>", "");
                history.pushState({current_url:url}, "", url);
            }
        <?php }?>
    }
</script>
