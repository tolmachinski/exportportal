<div class="wr-modal-b w-900">
    <div class="modal-b__content mh-700">
        <ul class="nav-b nav nav-tabs clearfix mt-10" role="tablist">
            <li class="active" role="presentation"><a href="#new-users" aria-controls="title" role="tab" data-toggle="tab">New users</a></li>
            <li role="presentation"><a href="#registered-users" aria-controls="title" role="tab" data-toggle="tab">Registered users</a></li>
        </ul>

        <div class="tab-content nav-info clearfix">
            <div role="tabpanel" class="tab-pane active" id="new-users">
                <?php if (empty($new_users)) { ?>
                    <div class="info-alert-b"><i class="ep-icon ep-icon_info"></i> <span>No users found</span></div>
                <?php } else { ?>
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($new_users as $new_user) { ?>
                            <tr>
                                <td><?php echo $new_user['attend_fname']; ?> <?php echo $new_user['attend_lname']; ?></td>
                                <td><?php echo $new_user['attend_phone']; ?></td>
                                <td><?php echo $new_user['attend_email']; ?></td>
                                <td><?php echo ucfirst($new_user['attend_status']); ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>
            </div>
            <div role="tabpanel" class="tab-pane" id="registered-users">
                <?php if (empty($registered_users)) { ?>
                    <div class="info-alert-b"><i class="ep-icon ep-icon_info"></i> <span>No users found</span></div>
                <?php } else { ?>
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($registered_users as $registered_user) { ?>
                            <tr>
                                <td><?php echo $registered_user['attend_fname']; ?> <?php echo $registered_user['attend_lname']; ?></td>
                                <td><?php echo $registered_user['attend_phone']; ?></td>
                                <td><?php echo $registered_user['attend_email']; ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>
            </div>
        </div>
    </div>
</div>



<script>
</script>
