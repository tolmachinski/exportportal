<?php if(admin_logged_as_user()){?>
    <script>
        var exit_explore_user = function(){
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '<?php echo __SITE_URL;?>login/exit_explore_user',
                beforeSend: function(){},
                success: function(resp){
                    if(resp.mess_type == 'success'){
                        window.location.href = resp.redirect;
                    } else{
                        systemMessages(resp.message, 'message-' + resp.mess_type );
                    }
                }
            });
            return false;
        }
    </script>
    <div class="warning-alert-b wr-logged-as">
        <i class="ep-icon ep-icon_warning"></i>
        <?php echo admin_logged_as_name();?> logged in as: <?php echo user_name_session();?>
        <a href="#" class="pull-right confirm-dialog txt-orange-darker" data-callback="exit_explore_user" data-message="Are you sure you want to exit from this user session?">Exit access <i class="ep-icon ep-icon_logout"></i></a>
    </div>
<?php }?>
