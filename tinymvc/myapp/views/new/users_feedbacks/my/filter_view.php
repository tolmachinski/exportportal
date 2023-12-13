<div class="container-fluid-modal">
    <label class="input-label">Created date</label>
    <div class="row">
        <div class="col-12 col-lg-6 mb-15-sm-max">
            <input class="datepicker-init start_from dt_filter" id="start_from" type="text" placeholder="From" data-title="Created from" name="start_from" placeholder="From" readonly>
        </div>
        <div class="col-12 col-lg-6">
            <input class="datepicker-init start_to dt_filter" id="start_to" type="text" placeholder="To" data-title="Created to" name="start_to" readonly>
        </div>
    </div>

    <label class="input-label">Search by</label>
    <input class="dt_filter" type="text" data-title="Search by" name="keywords" maxlength="50" id="keywords" placeholder="Keywords">

    <label class="input-label">Order number</label>
    <input class="dt_filter" type="text" data-title="Order number" name="id_order" placeholder="Order number" value="<?php if(isset($id_order)) echo orderNumber($id_order);?>">

    <!-- HIDDEN FILTERS -->
    <div class="display-n">
        <input class="dt_filter" type="text" data-title="Feedback number" name="feedback_number" placeholder="Feedback number" value="<?php if(isset($id_feedback)) echo orderNumber($id_feedback);?>">
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
        if(callerObj.prop("name") == 'feedback_number'){
            var number = toOrderNumber(callerObj.val());
            if(number){
                callerObj.val(number);
            } else{
                systemMessages('Error: Incorrect feedback number.', 'error' );
                callerObj.val('');
                return false;
            }
        }
        if(callerObj.prop("name") == 'id_order'){
            var number = toOrderNumber(callerObj.val());
            if(number){
                callerObj.val(number);
            } else{
                systemMessages('Error: Incorrect order number.', 'error' );
                callerObj.val('');
                return false;
            }
        }
    }

    function onDeleteFilters(filter){
        <?php if(isset($id_feedback)){?>
            if(filter.name == 'feedback_number'){
                var url = window.location.href.replace("/feedback_number/<?php echo $id_feedback;?>", "");
                history.pushState({current_url:url}, "", url);
            }
        <?php }?>

        <?php if(isset($id_order)){?>
            if(filter.name == 'order_number'){
                var url = window.location.href.replace("/order_number/<?php echo $id_order;?>", "");
                history.pushState({current_url:url}, "", url);
            }
        <?php }?>
    }
</script>
