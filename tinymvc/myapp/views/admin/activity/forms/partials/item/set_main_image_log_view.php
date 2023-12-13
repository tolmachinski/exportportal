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
                    <tr>
                        <td class="w-20pr">Name</td>
                        <?php if(!empty($changes['old']['photo'])) { ?>
                            <td class="w-40pr"><?php echo $changes['old']['photo']['photo_name']; ?></td>
                        <?php } ?>
                        <?php if(!empty($changes['current']['photo'])) { ?>
                            <td class="w-40pr"><?php echo $changes['current']['photo']['photo_name']; ?></td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <td class="w-20pr">Url</td>
                        <?php if(!empty($changes['old']['photo'])) { ?>
                            <td class="w-40pr">
                                <a href="<?php echo ($url = getDisplayImageLink(array('{ID}' => $resource['id'], '{FILE_NAME}' => $changes['old']['photo']['photo_name']), 'items.main')); ?>" target="_blank">
                                    <?php echo $url; ?>
                                </a>
                            </td>
                        <?php } ?>
                        <?php if(!empty($changes['current']['photo'])) { ?>
                            <td class="w-40pr">
                                <a
                                    href="<?php echo ($url = getDisplayImageLink(array('{ID}' => $resource['id'], '{FILE_NAME}' => $changes['current']['photo']['photo_name']), 'items.main')); ?>"
                                    target="_blank"
                                >
                                    <?php echo $url; ?>
                                </a>
                            </td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <td class="w-20pr">Image</td>
                        <?php if(!empty($changes['old']['photo'])) { ?>
                            <td class="w-40pr">
                                <a
                                    href="<?php echo ($url = getDisplayImageLink(array('{ID}' => $resource['id'], '{FILE_NAME}' => $changes['old']['photo']['photo_name']), 'items.main')); ?>"
                                    target="_blank"
                                >
                                    <img  class="image mw-100" src="<?php echo $url; ?>" alt="<?php echo $changes['old']['photo']['photo_name']; ?>">
                                </a>
                            </td>
                        <?php } ?>
                        <?php if(!empty($changes['current']['photo'])) { ?>
                            <td class="w-40pr">
                                <a
                                    href="<?php echo ($url = getDisplayImageLink(array('{ID}' => $resource['id'], '{FILE_NAME}' => $changes['current']['photo']['photo_name']), 'items.main')); ?>"
                                    target="_blank"
                                >
                                    <img  class="image mw-100" src="<?php echo $url; ?>" alt="<?php echo $changes['current']['photo']['photo_name']; ?>">
                                </a>
                            </td>
                        <?php } ?>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
<?php } ?>
