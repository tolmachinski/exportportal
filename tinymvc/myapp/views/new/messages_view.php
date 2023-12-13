<?php if (!empty($message)) { ?>
    <div style="overflow: auto; width: 100%; height: 100%">
        <ul class="modal-system-messages <?php echo $type; ?>">
            <li><?php echo $message; ?></li>
        </ul>
    </div>

	<?php if (isset($refrash)) { ?>
        <meta http-equiv="refresh" content="<?php echo $refrash; ?>"/>
    <?php } ?>
<?php } ?>
