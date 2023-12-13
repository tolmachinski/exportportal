<div class="pages-status-code">
    <div class="pages-status-code__block">
        <div class="pages-status-code__nr">
            <img class="image" src="<?php echo __IMG_URL;?>public/img/error-pages/404-img.png" alt="404">
        </div>
        <h2 class="pages-status-code__ttl">OOPS, something went wrong</h2>
        <h3 class="pages-status-code__txt">Sorry, the page you requested could not be found.</h3>
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
        var refHost = extractHostname(ref);
        var invalidRef = ('' === ref || refHost !== __site_url);

        backBtn.setAttribute('href', invalidRef ? __site_url : ref);

        if (invalidRef) {
            backBtn.textContent = 'Go home';
        } else {
            backBtn.addEventListener('click', function (e) {
                e.preventDefault();

                if (window.history.length < 2) {
                    location.href = "<?php echo $referrer; ?>";

                    return;
                }

                window.history.back();
            });
        }
    });
</script>
