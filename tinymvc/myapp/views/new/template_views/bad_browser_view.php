<div id="js-bad-browser-wr">
    <script type="text/template" id="js-bad-browser">
        <?php
            views()->display('new/template_views/bad_browser_template_view', [
                "title"     => translate("browser_out_of_date_title"),
                "paragraph" => translate("browser_out_of_date_paragrapth"),
            ]);
        ?>
    </script>
    <script>
        (function() {
            var d = document;
            if (!("noModule" in d.createElement("script"))) {
                d.querySelector("body").outerHTML = d.getElementById("js-bad-browser-wr").outerHTML;
                var c = d.createElement("link"), b = d.querySelector("body");
                c.rel = "stylesheet";
                c.href = "<?php echo asset("public/build/badbrowser.css"); ?>";
                c.onload = function () {
                    b.insertAdjacentHTML("beforeend", d.getElementById("js-bad-browser").textContent.trim());
                };
                b.appendChild(c);
            }
        })();
    </script>
    <noscript>
        <link rel="preload stylesheet" as="style" href="<?php echo asset("public/build/badbrowser.css"); ?>">
        <?php
            views()->display('new/template_views/bad_browser_template_view', [
                "title"     => translate("browser_no_script_title"),
                "paragraph" => translate("browser_no_script_paragrapth"),
            ]);
        ?>
    </noscript>
</div>
