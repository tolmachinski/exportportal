<div class="dt-details w-1000 p-15">
    <table class="dt-details__table m-20">
        <tbody>
            <tr>
                <td class="w-100 p-5">ID event:</td>
                <td class="p-5">
                    <strong><?php echo $event['id'];?></strong>
                </td>
            </tr>
            <tr>
                <td class="w-100 p-5">Labels:</td>
                <td class="p-5">
                    <?php if ($event['is_recommended_by_ep']) {?>
                        <span class="label label-success">Recommended by EP</span>
                    <?php }?>

                    <?php if ($event['is_upcoming_by_ep']) {?>
                        <span class="label label-primary">Upcoming by EP</span>
                    <?php }?>

                    <?php if ($event['is_attended_by_ep']) {?>
                        <span class="label label-info">Attended by EP</span>
                    <?php }?>

                    <?php if ($event['is_published']) {?>
                        <span class="label label-default">Published</span>
                    <?php } else {?>
                        <span class="label label-danger">Not published</span>
                    <?php }?>
                </td>
            </tr>
            <tr>
                <td class="w-100 p-5">Title:</td>
                <td class="p-5"><?php echo cleanOutput($event['title']);?></td>
            </tr>
            <tr>
                <td class="w-100 p-5">Main image:</td>
                <td class="p-5">
                    <img
                        class="mw-150 mh-150 js-fs-image"
                        src="<?php echo cleanOutput($event['imageUrl']); ?>"
                        alt="<?php echo cleanOutput($event['speaker']['name']); ?>"
                        data-fsw="150"
                        data-fsh="150"
                    >
                </td>
            </tr>
            <tr>
                <td class="w-100 p-5">Type:</td>
                <td class="p-5"><?php echo cleanOutput($event['type']['title']);?></td>
            </tr>
            <tr>
                <td class="w-100 p-5">Ticket price:</td>
                <td class="p-5"><?php echo get_price($event['ticket_price']);?></td>
            </tr>
            <tr>
                <td class="w-100 p-5">Category:</td>
                <td class="p-5"><?php echo cleanOutput($event['category']['name']);?></td>
            </tr>
            <?php if (!empty($event['speaker'])) {?>
                <tr>
                    <td class="w-100 p-5">Speaker:</td>
                    <td class="p-5">
                        <p class="mb-5"><?php echo cleanOutput($event['speaker']['name']);?></p>
                        <img
                            class="mw-150 mh-150 js-fs-image"
                            src="<?php echo cleanOutput($event['speaker']['imageUrl']); ?>"
                            alt="<?php echo cleanOutput($event['speaker']['name']); ?>"
                            data-fsw="150"
                            data-fsh="150"
                        >
                    </td>
                </tr>
            <?php }?>
            <tr>
                <td class="w-100 p-5">Start date:</td>
                <td class="p-5"><?php echo getDateFormatIfNotEmpty($event['start_date']);?></td>
            </tr>
            <tr>
                <td class="w-100 p-5">End date:</td>
                <td class="p-5"><?php echo getDateFormatIfNotEmpty($event['end_date']);?></td>
            </tr>
            <tr>
                <td class="w-100 p-5">Description:</td>
                <td class="p-5"><?php echo $event['description'];?></td>
            </tr>
            <tr>
                <td class="w-100 p-5">Why attend:</td>
                <td class="p-5"><?php echo $event['why_attend'];?></td>
            </tr>
            <tr>
                <td class="w-100 p-5">Agenda:</td>
                <td class="p-5">
                    <ul>
                        <?php foreach ($event['agenda'] as $agendaItem) {?>
                            <li class="mb-5"><?php echo $agendaItem['startDate'] . ' : ' . $agendaItem['description'];?></li>
                        <?php }?>
                    </ul>
                </td>
            </tr>
            <tr>
                <td class="w-100 p-5">Tags:</td>
                <td class="p-5 txt-red">
                    <?php if (!empty($event['tags'])) {?>
                        <?php foreach ($event['tags'] as $tag) {?>
                            <span class="label label-default"><?php echo cleanOutput($tag);?></span>
                        <?php }?>
                    <?php }?>
                </td>
            </tr>
            <tr>
                <td class="w-100 p-5">Location:</td>
                <td class="p-5">
                    <div>
                        <div>
                            <strong>Country: </strong> <?php echo cleanOutput($event['country']['name'] ?? 'Not indicated');?>
                        </div>
                        <div class="pt-5">
                            <strong>State: </strong> <?php echo cleanOutput($event['state']['name'] ?? 'Not indicated');?>
                        </div>
                        <div class="pt-5">
                            <strong>City: </strong> <?php echo cleanOutput($event['city']['name'] ?? 'Not indicated');?>
                        </div>
                    </div>
                </td>
            </tr>
            <?php if (!empty($event['partners'])) { ?>
                <tr>
                    <td class="w-100 p-5">Partners:</td>
                    <td class="p-5">
                        <?php foreach ($event['partners'] as $partner) {?>
                            <img
                                class="mw-100 mh-150 js-fs-image"
                                src="<?php echo cleanOutput($partner['imageUrl']); ?>"
                                alt="<?php echo cleanOutput($partner['name']); ?>"
                                data-fsw="100"
                                data-fsh="100"
                            >
                        <?php }?>
                    </td>
                </tr>
            <?php }?>
            <tr>
                <td class="w-100 p-5">Created:</td>
                <td class="p-5"><?php echo getDateFormat($event['create_date']);?></td>
            </tr>
            <tr>
                <td class="w-100 p-5">Updated:</td>
                <td class="p-5"><?php echo getDateFormat($event['update_date']);?></td>
            </tr>
        </tbody>
    </table>
</div>
