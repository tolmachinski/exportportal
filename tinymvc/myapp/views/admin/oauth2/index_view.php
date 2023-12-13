<script>
    (function() {
        window.opener.dispatchEvent(new CustomEvent('auth:finished'));
        window.dispatchEvent(new CustomEvent('auth:finished'));
        window.close();
    })();
</script>
