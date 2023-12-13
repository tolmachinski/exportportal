<div class="row">
    <div class="js-gen-config-wr col-xs-4 col-xs-offset-4">
        <ul class="list-group">
            <li class="list-group-item">
                <label>
                    <input type="checkbox" name="translations" value="translations" checked>
                    Translations
                    <span class="loader-btn" style="display:none;">
                        <img class="image h-15" src="<?php echo __IMG_URL;?>public/img/loader.gif" alt="loader">
                    </span>
                </label>
            </li>
            <li class="list-group-item">
                <label>
                    <input type="checkbox" name="configs" value="configs" checked>
                    Configs
                    <span class="loader-btn" style="display:none;">
                        <img class="image h-15" src="<?php echo __IMG_URL;?>public/img/loader.gif" alt="loader">
                    </span>
                </label>
            </li>
            <li class="list-group-item">
                <label>
                    <input type="checkbox" name="routes" value="routes" checked>
                    Routes
                    <span class="loader-btn" style="display:none;">
                        <img class="image h-15" src="<?php echo __IMG_URL;?>public/img/loader.gif" alt="loader">
                    </span>
                </label>
            </li>
        </ul>

        <div class="mt-15">
            <button class="js-gen-config-btn btn btn-primary" type="button">Generate</button>
        </div>
    </div>
</div>

<script>
(function() {
	"use strict";

	window.configGen = ({
		init: function (params) {
			configGen.self = this;
			configGen.$btnSubmit = $('.js-gen-config-btn');
			configGen.$mainWr = $('.js-gen-config-wr');
			configGen.callArray = [];
			configGen.self.initListiners();
		},
		initListiners: function(){
			configGen.$btnSubmit.on('click', function(e){
                e.preventDefault();
                configGen.$btnSubmit.addClass('disabled');

                configGen.callArray = [];
                configGen.$mainWr.find('label input[type="checkbox"]').each(function(){
                    var $this = $(this);

                    if($this.prop('checked')){
                        configGen.callArray.push($this.val());
                        // configGen.self.call_func($this.val());
                    }
                });

                if(configGen.callArray.length > 0){
                    configGen.self.call_func();
                }
            });
		},
        call_func: function(){
            var loader = configGen.$mainWr.find('input[name="'+configGen.callArray[0]+'"]').siblings('.loader-btn');

            switch(configGen.callArray[0]){
                case 'translations':
                    configGen.self.translation_files_db_to_files(loader);
                break;
                case 'configs':
                    configGen.self.regenerate_configs(loader);
                break;
                case 'routes':
                    configGen.self.regenerate_route(loader);
                break;
            }
        },
        verify_array: function(loader){
            loader.hide();
            configGen.callArray.splice(0, 1);

            if(configGen.callArray.length > 0){
                configGen.self.call_func();
            }else{
                configGen.$btnSubmit.removeClass('disabled');
            }
        },
		translation_files_db_to_files: function(loader){

            $.ajax({
                type: 'POST',
                url: '<?php echo __SITE_URL ?>translations/ajax_operations/translation_files_db_to_files',
                dataType: 'JSON',
                beforeSend: function(){
                    loader.show();
                },
                success: function(resp){
                    systemMessages( resp.message, 'message-' + resp.mess_type );
                    configGen.self.verify_array(loader);
                },
                error: function(jqXHR, textStatus, errorThrown){
                    configGen.self.verify_array(loader);
                    systemMessages( 'Error. Please try again later.', 'message-error' );
                    jqXHR.abort();
                }
            });
		},
		regenerate_configs: function(loader){

            $.ajax({
                type: 'POST',
                url: '<?php echo __SITE_URL?>config/ajax_config_operation/regenerate_configs/',
                dataType: 'json',
                beforeSend: function(){
                    loader.show();
                },
                success: function(data){
                    systemMessages( data.message, 'message-' + data.mess_type );
                    configGen.self.verify_array(loader);
                },
                error: function(jqXHR, textStatus, errorThrown){
                    configGen.self.verify_array(loader);
                    systemMessages( 'Error. Please try again later.', 'message-error' );
                    jqXHR.abort();
                }
            });
        },
		regenerate_route: function(loader){

            $.ajax({
                url: '<?php echo __SITE_URL ?>translations/ajax_operations/regenerate_route',
                type: 'POST',
                dataType: 'json',
                data: {},
                beforeSend: function(){
                    loader.show();
                },
                success: function(resp){
                    systemMessages( resp.message, 'message-' + resp.mess_type );
                    configGen.self.verify_array(loader);
                },
                error: function(jqXHR, textStatus, errorThrown){
                    configGen.self.verify_array(loader);
                    systemMessages( 'Error. Please try again later.', 'message-error' );
                    jqXHR.abort();
                }
            });
        }

	});

}());

$(function() {
	configGen.init();
});

</script>
