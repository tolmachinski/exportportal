<?php if(!empty($downgrade_packages)){?>
    <script>
        $(function() {
            var downgradeAccount = function(){
                $.ajax({
                    type: 'POST',
                    url: __site_url + 'upgrade/ajax_operations/downgrade',
                    dataType: 'json',
                    success: function(resp){
                        if(resp.mess_type == 'success'){
                            window.scroll(0, 0);
                            location.reload(true);
                        } else {
                            systemMessages( resp.message, resp.mess_type );
                        }
                    }
                });
            }

            mix(window, {
                callDowngradeAccount: downgradeAccount
            }, false);
        });
    </script>
<?php }?>

<div class="container-center2">
    <h2
        id="js-upgrade-list"
        class="upgrade-large-title"
    ><?php echo translate('upgrade_packages_title_1'); ?> <strong class="upgrade-large-title__subtitle"><?php echo translate('upgrade_packages_title_2'); ?></strong></h2>

    <div class="upgrade-packages">
        <?php
            if(!empty($downgrade_packages)){

                foreach($downgrade_packages as $downgradePackagesItem){
                    views()->display(
                        'new/upgrade/package_item_view',
                        array(
                            'itemType'          => 'downgrade',
                            'packagesItem'      => $downgradePackagesItem,
                            'upgradeBenefits'   => $upgrade_benefits
                        )
                    );
                }

                foreach ($upgrade_packages as $upgradePackagesItem){
                    views()->display(
                        'new/upgrade/package_item_view',
                        [
                            'idGroup'           => $id_group,
                            'itemType'          => 'extend',
                            'packagesItem'      => $upgradePackagesItem,
                            'upgradeBenefits'   => $upgrade_benefits,
                            'upgradeRequest'    => $upgrade_request,
                            'currentPackage'    => $current_package
                        ]
                    );
                }

            }else{
                $current_package['short_description'] = 'All users start out on Export Portal as verified and have access to a variety of free benefits to make international trade easier and safer.';
                views()->display(
                    'new/upgrade/package_item_view',
                    [
                        'idGroup'           => $id_group,
                        'itemType'          => 'current',
                        'packagesItem'      => $current_package,
                        'upgradeBenefits'   => $upgrade_benefits,
                    ]
                );

                foreach ($upgrade_packages as $upgradePackagesItem){
                    views()->display(
                        'new/upgrade/package_item_view',
                        array(
                            'itemType'          => 'upgrade',
                            'packagesItem'      => $upgradePackagesItem,
                            'upgradeBenefits'   => $upgrade_benefits,
                            'upgradeRequest'    => $upgrade_request
                        )
                    );
                }

            }
        ?>
    </div>
</div>
