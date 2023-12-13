<div class="col-12 col-lg-6">
    <div class="form-group" id="<?php echo $id; ?>-date-container">
        <label class="input-label input-label--required"><?php echo $title; ?></label>
        <input type="text"
            id="<?php echo $id; ?>"
            name="<?php echo $name; ?>"
            class="form-control datetimepicker-input validate[required]"
            value="<?php echo !empty($date) ? $date->format($format) : ''; ?>"
            placeholder="<?php echo $placeholder; ?>"
            data-input
            readonly>
    </div>
</div>

<script>
    $(function(){
        var onDatepickerShowing = function(container, input, instance) {
            var calendar = instance.dpDiv;

            container.append(calendar);
            calendar.addClass('dtfilter-ui-datepicker');
        };
        var onDateChange = function(binding, type, e) {
            var self = $(this);
            if(dateInput.hasClass('validengine-border')){
                dateInput.removeClass('validengine-border');
            }

            if (null !== type && ['min', 'max'].indexOf(type) !== -1) {
                binding.datepicker('option', type + 'Date', self.datepicker('getDate'));
            } else {
                binding.datepicker('setDate', self.datepicker('getDate'));
            }
        };

        var id = "#<?php echo $id; ?>";
        var hasMinimalDate = Boolean(~~'<?php echo (int) !empty($min_date); ?>');
        var hasMaximalDate = Boolean(~~'<?php echo (int) !empty($max_date); ?>');
        var minDate = hasMinimalDate ? new Date('<?php echo !empty($min_date) ? $min_date->format(DATE_ATOM) : null; ?>') : new Date('1970-01-01 00:00:00');
        var maxDate = hasMaximalDate ? new Date('<?php echo !empty($max_date) ? $max_date->format(DATE_ATOM) : null; ?>') : new Date('9999-01-01 00:00:00');
        var containerId = "#<?php echo $id; ?>-date-container";
        var containerBlock = $(containerId);
        var hasBinding = Boolean(~~parseInt('<?php echo (int) (!empty($bind) && arrayHas($bind, 'id')); ?>'));
        var hasBindingType = hasBinding && Boolean(~~parseInt('<?php echo (int) arrayHas($bind, 'type'); ?>'));
        var bindType = hasBindingType ? '<?php echo arrayGet($bind, 'type'); ?>' : null;
        var bindId = hasBinding ? '#<?php echo arrayGet($bind, 'id'); ?>' : null;
        var bindedInput = null !== bindId ? $(bindId) : null;
        var dateInput = $(id);
        var datepickerOptions = {
            constrainInput: true,
            beforeShow: onDatepickerShowing.bind(null, containerBlock),
            dateFormat: 'mm/dd/yy',
            minDate: minDate,
            maxDate: maxDate,
        };

        if (dateInput.length !== 0) {
            dateInput.datepicker(datepickerOptions)
            if (hasBinding) {
                dateInput.on('change', onDateChange.bind(dateInput, bindedInput, bindType));
            }
        }
    });
</script>
