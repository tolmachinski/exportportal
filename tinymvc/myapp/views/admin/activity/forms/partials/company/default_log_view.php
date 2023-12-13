<?php if(!empty($changes) && !empty($changes['keys'])) { ?>
    <div class="row">
        <div class="col-xs-12 mb-15">
            <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered table-fixed w-100pr mt-15 mb-15 vam-table" style="table-layout:fixed;">
                <thead>
                    <tr role="row">
                        <th class="w-20pr" rowspan="2">Field</th>
                        <th colspan="2">Changes</th>
                    </tr>
                    <tr role="row">
                        <th class="w-50pr">Old</th>
                        <th class="w-50pr">Current</th>
                    </tr>
                </thead>
                <tbody class="tabMessage">
                    <?php foreach ($changes['keys'] as $key => $key_name) { ?>
                        <tr>
                            <td class="w-20pr"><?php echo $key_name; ?></td>
                            <td class="w-40pr">
                                <?php if(isset($changes['old']) && isset($changes['old'][$key])) { ?>
                                    <?php if('logo_company' === $key && !empty($changes['old'][$key])) { ?>
                                    <?php
                                        $logo_company = getDisplayImageLink(array('{ID}' => $resource['id'], '{FILE_NAME}' => $changes['old'][$key]), 'companies.main');
                                        $logo_company_thumb = getDisplayImageLink(array('{ID}' => $resource['id'], '{FILE_NAME}' => $changes['old'][$key]), 'companies.main', array( 'thumb_size' => 1 ));
                                    ?>
                                        <div class="tac">
                                            <a href="<?php echo $logo_company; ?>" target="_blank">
                                                <img class="image mw-100" src="<?php echo $logo_company_thumb; ?>" alt="<?php echo $changes['old'][$key]; ?>"/>
                                            </a>
                                        </div>
                                    <?php } else { ?>
                                        <?php echo $changes['old'][$key]; ?>
                                    <?php } ?>
                                <?php } ?>
                            </td>
                            <td class="w-40pr">
                                <?php if(isset($changes['current']) && isset($changes['current'][$key])) { ?>
                                    <?php if('logo_company' === $key && !empty($changes['current'][$key])) { ?>
                                    <?php
                                        $logo_company = getDisplayImageLink(array('{ID}' => $resource['id'], '{FILE_NAME}' => $changes['current'][$key]), 'companies.main');
                                        $logo_company_thumb = getDisplayImageLink(array('{ID}' => $resource['id'], '{FILE_NAME}' => $changes['current'][$key]), 'companies.main', array( 'thumb_size' => 1 ));
                                    ?>
                                        <div class="tac">
                                            <a href="<?php echo $logo_company; ?>" target="_blank">
                                                <img class="image mw-100" src="<?php echo $logo_company_thumb; ?>" alt="<?php echo $changes['current'][$key]; ?>"/>
                                            </a>
                                        </div>
                                    <?php } else { ?>
                                        <?php echo $changes['current'][$key]; ?>
                                    <?php } ?>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
<?php } ?>
