<div class="wr-modal-b">
    <form
        id="event-lifecycle--promote--form"
        class="modal-b__form validateModal"
        method="post"
        action="<?php echo $actionUrl; ?>"
        data-callback="promoteEvent"
    >
		<div class="modal-b__content relative-b pt-20 w-700">
            <label class="modal-b__label">Promotion date start</label>
            <div class="input-group">
                <input
                    id="event-lifecycle--promote--form-field--start-date"
                    type="text"
                    name="start_date"
                    class="form-control js-datepicker-start-date validate[required]"
                    placeholder="Start"
                    <?php echo addQaUniqueIdentifier("admin-users__event-lifecycle-promote-field-form__start-date-input")?>
                    readonly
                >
                <div class="input-group-addon">-</div>
                <input
                    id="event-lifecycle--promote--form-field--end-date"
                    type="text"
                    name="end_date"
                    class="form-control js-datepicker-end-date validate[required]"
                    value="<?php echo $maximumDate->format('m/d/Y H:i'); ?>"
                    placeholder="End"
                    <?php echo addQaUniqueIdentifier("admin-users__event-lifecycle-promote-field-form__end-date-input")?>
                    readonly
                >
            </div>
		</div>

        <div class="modal-b__btns clearfix">
            <button class="btn btn-success pull-right" type="submit">
                <span class="ep-icon ep-icon_ok"></span> Save
            </button>
        </div>
    </form>
</div>

<script>
    $(function () {
        var createDateInterval = function (startDateField, endDateField, minDate, maxDate) {
            var updateCounterpartOnChange = function (element, dateOption, formatedDate) {
                var selectedDate = new Date(formatedDate);

                element.datetimepicker("option", dateOption, selectedDate);
            };
            var defaultOptions = {
                showButtonPanel: false,
                minDate: minDate,
                maxDate: maxDate,
                timeFormat: "HH:mm",
                controlType: "select",
                stepMinute: 5,
                oneLine: true,
            };

            startDateField.datetimepicker(Object.assign({}, defaultOptions, { onSelect: updateCounterpartOnChange.bind(null, endDateField, 'minDate') }));
            endDateField.datetimepicker(Object.assign({}, defaultOptions, { onSelect: updateCounterpartOnChange.bind(null, startDateField, 'maxDate') }));
        };
        var promoteEvent = function (form) {
            var url = form.attr('action');
            var data = form.serializeArray();
            var onRequestSuccess = function (response) {
                systemMessages(response.message, response.mess_type);
                if(response.mess_type == 'success'){
                    closeFancyboxPopup()
                    dispatchCustomEvent('events:update', globalThis);
                    mix(globalThis, { promoteEvent: null }, false);
                }
            };

            if (null === url) {
                return Promise.resolve();
            }
            showLoader(form);
            form.find('button[type="submit"]').prop('disabled', false);

            return postRequest(url, data)
                .then(onRequestSuccess)
                .catch(onRequestError)
                .finally(function() {
                    hideLoader(form);
                    form.find('button[type="submit"]').prop('disabled', false);
                });
        };

        var form = $("#event-lifecycle--promote--form");
        var startDateField = $("#event-lifecycle--promote--form-field--start-date");
        var endDateField = $("#event-lifecycle--promote--form-field--end-date");
        var minDate = '<?php echo $minimumDate->format('m/d/Y H:i'); ?>';
        var maxDate = '<?php echo $maximumDate->format('m/d/Y H:i'); ?>';

        createDateInterval(startDateField, endDateField, minDate, maxDate);
        mix(globalThis, { promoteEvent: promoteEvent.bind(null, form) }, false);
    });
</script>
