<h3>Found users: <?php echo $found; ?></h3>
<h3>Filtered users: <?php echo $filtered; ?></h3>
<h3>Saved: <?php echo $saved; ?></h3>
<h3>Save failures: <?php echo $save_failures; ?></h3>

<?php if (!empty($failures)) { ?>
    <h3>Other failures: <?php echo $other_failures; ?></h3>

    <?php foreach ($failures as $type => list($title, $list)) { ?>
        <?php if (!empty($list)) { ?>
            <h3><?php echo sprintf($title, count($list)); ?></h3>
            <button id="show-<?php echo $type; ?>" title="Show/hide" type="button">Show/hide</button>
            <div id="spoiler-<?php echo $type; ?>" style="display:none">
                <?php foreach($list as $error) { ?>
                    <?php dump($error[0]); ?>
                    <?php if (isset($error[1])) { ?>
                        <?php dump($error); ?>
                        <?php dump($error[1]); ?>
                        <?php if ($error[1] instanceof \Exception) { ?>
                            <?php $previous = $error[1]->getPrevious(); ?>
                            <?php if (method_exists($error[1], 'getResponse')) { ?>
                                <div>
                                    <h4>Response</h4>
                                    <?php dump($error[1]->getResponse()); ?>
                                    <?php dump($error[1]->getResponse()->getBody()); ?>
                                    <?php dump($error[1]->getResponse()->getBody()->getContents()); ?>
                                </div>
                            <?php } ?>
                            <?php if (null !== $previous) { ?>
                                <?php if (method_exists($previous, 'getResponse')) { ?>
                                    <div>
                                        <h4>Response</h4>
                                        <?php dump($previous->getResponse()->getBody()->getContents()); ?>
                                    </div>
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                    <hr>
                <?php } ?>
            </div>
            <script>
                (function() {
                    var element = document.getElementById('spoiler-<?php echo $type; ?>');
                    var button = document.getElementById('show-<?php echo $type; ?>');

                    button.addEventListener('click', function() {
                        if (element)  {
                            if (element.style.display === 'none') {
                                element.style.display = 'block';
                            } else {
                                element.style.display = 'none';
                            }
                        }
                    });
                } ());
            </script>
        <?php } ?>
    <?php } ?>
<?php } ?>
