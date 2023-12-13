<script>
    (function() {
        window.opener.dispatchEvent(new CustomEvent('edit-tabs:finished'));
        window.dispatchEvent(new CustomEvent('edit-tabs:finished'));
        window.close();
    })();
</script>
