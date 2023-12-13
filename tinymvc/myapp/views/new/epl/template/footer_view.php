<?php views()->display('new/template/components/footer_global_view', ['isEPL' => 1]); ?>

<?php
    if (isset($webpackData['pageConnect'])) {
        encoreEntryLinkTags($webpackData['pageConnect']);
    }
?>
