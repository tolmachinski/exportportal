<div class="container-center container-center--mw-1500 dashboard-container">
    <div id="result-container">
        <?php if ($initialized) { ?>
            <span class="badge badge-primary">Initialized</span>
        <?php } ?>
    </div>
    <div id="my-chats-app-wr"></div>
</div>

<?php if (!$initialized) { ?>
    <?php encoreScripts(); ?>
    <?php encoreEntryScriptTags('chat_app'); ?>
    <?php echo dispatchDynamicFragment(
        'chat-app:keygen',
        [
            '#my-chats-app-wr',
            '#result-container',
            $credentials['username'],
            $credentials['password'],
            $credentials['passphrase'],
            config('env.MATRIX_ADMIN_USER_ID'),
            $redirectUrl,
            $credentials['hasKeys'],
        ],
        true,
    ); ?>
<?php } ?>
