<?php if(!empty($changes) && !empty($changes['old']['photo'])) { ?>
    <div class="row">
        <div class="col-xs-12 mb-15">
            <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered table-fixed w-100pr mt-15 mb-15 vam-table" style="table-layout:fixed;">
                <thead>
                    <tr role="row">
                        <th class="w-20pr">Field</th>
                        <th class="w-80pr">Value</th>
                    </tr>
                </thead>
                <tbody class="tabMessage">
                    <tr>
                        <td class="w-20pr">Name</td>
                        <td class="w-80pr"><?php echo $changes['old']['photo']['photo_name']; ?></td>
                    </tr>
                    <tr>
                        <td class="w-20pr">Url</td>
                        <td class="w-80pr">
                            <a
                                href="<?php echo ($url = getDisplayImageLink(array('{ID}' => $resource['id'], '{FILE_NAME}' => $changes['old']['photo']['photo_name']), 'items.main')); ?>"
                                target="_blank"
                            >
                                <?php echo $url; ?>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
<?php } ?>
