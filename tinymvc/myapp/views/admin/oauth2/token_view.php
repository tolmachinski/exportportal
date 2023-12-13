<script>
    (function () {
        function close() {
            window.opener.dispatchEvent(new CustomEvent("auth:finished"));
            window.dispatchEvent(new CustomEvent("auth:finished"));
            window.close();
        }

        var provider = "<?php echo $type; ?>" || null;
        var redirectUrl = "<?php echo $redirectUri; ?>" || null;
        var verificationCode = "<?php echo $hmacKey; ?>" || null;
        var rawUrlHash = window.location.hash || null;
        if (redirectUrl === null || rawUrlHash === null) {
            close();
        }

        window.location.href = redirectUrl + "?" + rawUrlHash.slice(1) + "&type=" + provider + '&verification_code=' + verificationCode;
    })();
</script>
