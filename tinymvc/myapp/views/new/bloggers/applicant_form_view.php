<form id="applicant-form"
    action="<?php echo $action; ?>"
    class="content-form content-form--modal validate-modal mt-35 mb-35 js-ep-self-autotrack"
    data-tracking-events="submit"
    data-tracking-fields="<?php echo cleanOutput(json_encode(array('firstname', 'lastname', 'email'))); ?>"
    data-tracking-alias="form-bloggers-applicant-info">

    <div class="w-100pr">
        <label for="applicant-form--input--firstname">
            <i class="text-red">*</i> First name:
        </label>
        <div>
            <input type="text"
                name="firstname"
                id="applicant-form--input--firstname"
                class="input mt-5"
                placeholder="Enter the First name"
                data-validation-engine="validate[required,maxSize[200]]"
                data-prompt-position="topLeft:0,-5">
        </div>
    </div>

    <div class="w-100pr mt-20">
        <label for="applicant-form--input--lastname">
            <i class="text-red">*</i> Last name:
        </label>
        <div>
            <input type="text"
                name="lastname"
                id="applicant-form--input--lastname"
                class="input mt-5"
                placeholder="Enter the Last name"
                data-validation-engine="validate[required,maxSize[200]]"
                data-prompt-position="topLeft:0,-5">
        </div>
    </div>

    <div class="w-100pr mt-20">
        <label for="applicant-form--input--email">
            <i class="text-red">*</i> Email:
        </label>
        <div>
            <input type="email"
                name="email"
                id="applicant-form--input--email"
                class="input mt-5"
                placeholder="Enter the email"
                data-validation-engine="validate[required,custom[noWhitespaces],custom[emailWithWhitespaces],maxSize[255]]"
                data-prompt-position="topLeft:0,-5">
        </div>
    </div>

    <div class="w-100pr mt-20">
        <label for="applicant-form--input--country">
            <i class="text-red">*</i> Country:
        </label>
        <div class="mt-5">
            <select name="country"
                id="applicant-form--input--country"
                data-validation-engine="validate[required]"
                data-prompt-position="topLeft:0,-10">
                <option></option>
                <?php foreach($countries as $country){ ?>
                    <option value="<?php echo $country['id']; ?>">
                        <?php echo $country['country']; ?>
                    </option>
                <?php } ?>
            </select>
        </div>
    </div>

    <div class="w-100pr mt-20">
        <label for="applicant-form--input--about">
            <i class="text-red">*</i> Tell us few words about yourself. What makes you different?
        </label>
        <!-- <div class="file-upload__message mt-10 mb-10">
            <div class="alert alert-info">
                <i class="ep-icon ep-icon_info-stroke"></i>
                <div> Tell us few words about yourself. What makes you different?</div>
            </div>
        </div> -->
        <div style="position: relative">
            <textarea id="applicant-form--input--about"
                class="mt-5"
                name="about"
                placeholder="Enter the few words about yourself"
                data-validation-engine="validate[required,maxSize[500]]"
                data-prompt-position="topLeft:0,-5"></textarea>
        </div>
    </div>

    <div class="w-100pr mt-20">
        <label for="applicant-form--input--example-link">
            <i class="text-red">*</i> Give us an example of the article or video you edited before.
        </label>
        <!-- <div class="file-upload__message mt-10 mb-10">
            <div class="alert alert-info">
                <i class="ep-icon ep-icon_info-stroke"></i>
                <div> Give us an example of the article or video you edited before.</div>
            </div>
        </div> -->
        <div>
            <input type="text"
                name="example"
                class="input mt-5"
                id="applicant-form--input--example-link"
                placeholder="Enter the link"
                data-validation-engine="validate[required,maxSize[2000],custom[url]]"
                data-prompt-position="topLeft:0,-5">
        </div>
    </div>

    <div class="w-100pr mt-20">
        <label class="display-b">
            <i class="text-red">*</i> Do you have an ability to go and take an interview, if needed?
        </label>
        <!-- <div class="file-upload__message mt-10 mb-10">
            <div class="alert alert-info">
                <i class="ep-icon ep-icon_info-stroke"></i>
                <div> Do you have an ability to go and take an interview, if needed?</div>
            </div>
        </div> -->
        <label class="clearfix display-ib b-form-radio mr-5">
            <input type="radio"
                value="1"
                name="interview_opportunity"
                class="label-input mr-5 form-field--radio"
                id="applicant-form--input--interview-flag-yes"
                data-validation-engine="validate[required]"
                data-prompt-position="topLeft:0,-10">
            <span class="w-75">Yes</span>
        </label>
        <label class="clearfix display-ib b-form-radio mr-5">
            <input type="radio"
                value="0"
                name="interview_opportunity"
                class="label-input mr-5 form-field--radio"
                id="applicant-form--input--interview-flag-no"
                data-validation-engine="validate[required]"
                data-prompt-position="topLeft:0,-10">
            <span class="w-75">No</span>
        </label>
    </div>

    <div class="w-100pr mt-20">
        <label class="display-b">
            <i class="text-red">*</i> Do you have any previous experience in taking interviews?
        </label>
        <!-- <div class="file-upload__message mt-10 mb-10">
            <div class="alert alert-info">
                <i class="ep-icon ep-icon_info-stroke"></i>
                <div> Do you have any previous experience in taking interviews?</div>
            </div>
        </div> -->

        <label class="clearfix display-ib b-form-radio mr-5">
            <input type="radio"
                value="1"
                name="interview_experience"
                class="label-input mr-5 form-field--radio"
                id="applicant-form--input--interview-experince-flag-yes"
                data-validation-engine="validate[required]"
                data-prompt-position="topLeft:0,-10">
            <span class="w-75">Yes</span>
        </label>
        <label class="clearfix display-ib b-form-radio mr-5">
            <input type="radio"
                value="0"
                name="interview_experience"
                class="label-input mr-5 form-field--radio"
                id="applicant-form--input--interview-experince-flag-no"
                data-validation-engine="validate[required]"
                data-prompt-position="topLeft:0,-10">
            <span class="w-75">No</span>
        </label>
    </div>

    <div class="w-100pr mt-20">
        <label for="applicant-form--input--strengths">
            <i class="text-red">*</i> Tell us about your strengths.
        </label>
        <!-- <div class="file-upload__message mt-10 mb-10">
            <div class="alert alert-info">
                <i class="ep-icon ep-icon_info-stroke"></i>
                <div> Tell us about your strengths.</div>
            </div>
        </div> -->
        <div style="position: relative">
            <textarea class="mt-5"
                name="strengths"
                id="applicant-form--input--strengths"
                placeholder="Enter the few words about your strengths"
                data-validation-engine="validate[required,maxSize[500]]"
                data-prompt-position="topLeft:0,-5"></textarea>
        </div>
    </div>

    <div class="w-100pr mt-20">
        <label for="applicant-form--input--hobbies">
            Do you have any hobbies?
        </label>
        <!-- <div class="file-upload__message mt-10 mb-10">
            <div class="alert alert-info">
                <i class="ep-icon ep-icon_info-stroke"></i>
                <div> Do you have any hobbies?</div>
            </div>
        </div> -->
        <div style="position: relative">
            <textarea class="mt-5"
                name="hobbies"
                id="applicant-form--input--hobbies"
                placeholder="Enter the few words about your hobbies"
                data-validation-engine="validate[maxSize[500]]"
                data-prompt-position="topLeft:0,-5"></textarea>
        </div>
    </div>

    <div class="w-100pr mt-20">
        <label>
            Please give us links to your social media pages:
        </label>
        <!-- <div class="file-upload__message mt-10 mb-10">
            <div class="alert alert-info">
                <i class="ep-icon ep-icon_info-stroke"></i>
                <div> Do you have a Portfolio page? If yes, please provide us with a link.</div>
            </div>
        </div> -->
    </div>

    <div class="w-100pr mt-5">
        <label for="applicant-form--input--facbook-link">Facebook profile:</label>
        <div>
            <input type="text"
                name="socials[facebook]"
                class="input mt-5"
                id="applicant-form--input--facbook-link"
                placeholder="Enter the profile link"
                data-validation-engine="validate[maxSize[2000],custom[url]]"
                data-prompt-position="topLeft:0,-5">
        </div>
    </div>

    <div class="w-100pr mt-5">
        <label for="applicant-form--input--instagram-link">Instagram profile:</label>
        <div>
            <input type="text"
                name="socials[instagram]"
                class="input mt-5"
                id="applicant-form--input--instagram-link"
                placeholder="Enter the profile link"
                data-validation-engine="validate[maxSize[2000],custom[url]]"
                data-prompt-position="topLeft:0,-5">
        </div>
    </div>

    <div class="w-100pr mt-5">
        <label for="applicant-form--input--twitter-link">Twitter profile:</label>
        <div>
            <input type="text"
                name="socials[twitter]"
                class="input mt-5"
                id="applicant-form--input--twitter-link"
                placeholder="Enter the profile link"
                data-validation-engine="validate[maxSize[2000],custom[url]]"
                data-prompt-position="topLeft:0,-5">
        </div>
    </div>

    <div class="w-100pr mt-20">
        <label for="applicant-form--input--portfolio-link">
            Do you have a Portfolio page? If yes, please provide us with a link.
        </label>
        <!-- <div class="file-upload__message mt-10 mb-10">
            <div class="alert alert-info">
                <i class="ep-icon ep-icon_info-stroke"></i>
                <div> Do you have a Portfolio page? If yes, please provide us with a link.</div>
            </div>
        </div> -->
        <div>
            <input type="text"
                name="portfolio"
                id="applicant-form--input--portfolio-link"
                class="input mt-5"
                placeholder="Enter the portfolio link"
                data-validation-engine="validate[maxSize[2000],custom[url]]"
                data-prompt-position="topLeft:0,-5">
        </div>
    </div>

    <button type="submit"
        id="applicant-form--button--submit"
        class="button mt-30">
        Next
    </button>
</form>

<script type="application/javascript">
    var modalFormCallBack = function(form, caller){
        var url = form.attr('action');
        var data = form.serializeArray();
        var clearEditors = function() {
            for (var i = 0; i < tinyMCE.editors.length; i++) {
                var editor = tinyMCE.editors[i];

                tinyMCE.execCommand("mceRemoveControl", true, editor.id);
                tinyMCE.execCommand("mceRemoveEditor", true, editor.id);
                tinyMCE.remove(editor.id);
            }
        }


        addLoader('body');
        $.post(url, data, null, 'json').done(function(response){
            runFormTracking(form, response.mess_type === 'success');
            if(response.mess_type && response.mess_type !== 'success') {
                Messenger.notification(response.mess_type, response.message || 'Service is temporary unavailable');
                removeLoader('body');

                return;
            }

            if($.fancybox) {
                var fancyboxOptions = $.extend(true, {}, $.fancybox.opts, {
                    beforeClose: function() {
                        clearEditors.apply(this, arguments);
                    },
                    afterClose: function() {
                        if(timeoutAnchor) {
                            clearInterval(timeoutAnchor);
                        }
                    },
                    ajax: {
                        method: 'post',
                        data: response.applicant || {}
                    }
                });

                $.fancybox && $.fancybox.open({
                    type: 'ajax',
                    href: response.location,
                    element: $('<a>').data({title: "Fill the form", w: 1024}),
                }, fancyboxOptions);
            } else {
                removeLoader('body');
            }
        }).fail(function(error) {
            Messenger.error('Service is temporary unavailable');
            runFormTracking(form, false);
            removeLoader('body');
        });
    };

    $(function() {
        var countryList = $('#applicant-form--input--country');
        var radioFields = $('.form-field--radio');
        var countryListOptions = { width: "100%", placeholder: "Select country", minimumResultsForSearch: 1 };
        var radioFieldsOptions = { cursor: true };

        countryList.select2(countryListOptions);
        radioFields.iCheck(radioFieldsOptions);
    });
</script>
