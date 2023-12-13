<div class="pages-status-code">
    <div class="pages-status-code__block">
        <h2 class="pages-status-code__ttl">Coming soon</h2>
        <h3 class="pages-status-code__txt pages-status-code__txt--soon">
            This page is currently under construction.<br/>
            Get ready – we are preparing something amazing and exciting for you!
        </h3>
        <a id="js-back-button" class="pages-status-code__btn btn btn-primary mnw-250" href="<?php echo __SITE_URL; ?>">Go back</a>
    </div>
</div>

<script>
    function extractHostname(url) {
        var hostname = '';

        if (url.indexOf("//") > -1) {
            hostname = url.split('//')[0] + '//' + url.split('/')[2] + '/';
        }

        return hostname;
    }

    document.addEventListener("DOMContentLoaded", function(){
        var backBtn = document.getElementById("js-back-button");
        var ref = document.referrer;
        var invalidRef = ('' === ref || extractHostname(ref) !== __site_url);

        backBtn.setAttribute('href', invalidRef ? __site_url : ref);

        if (invalidRef) {
            backBtn.textContent = 'Go home';
        } else {
            backBtn.addEventListener('click', function (e) {
                e.preventDefault();

                window.history.back();
            });
        }
    });
</script>
