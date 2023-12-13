<?php
    $fonts_path = get_dynamic_url("public/css/fonts", __SITE_URL, true);
    $ajax_path = get_dynamic_url("newsletter/ajax_load_archive", __SITE_URL, true);
?>
<!DOCTYPE html>
<html lang="<?php echo __SITE_LANG; ?>">
    <head>
        <base href="<?php echo __SITE_URL; ?>">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta name="format-detection" content="telephone=no">
        <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=no;">
        <meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=7; IE=EDGE">

        <?php if (logged_in()) { ?>
            <meta name="csrf-token" content="<?php echo session()->csrfToken; ?>">
        <?php } ?>

        <title>Export Portal &raquo; Newsletter archive</title>

        <?php tmvc::instance()->controller->view->display('new/favicon_view'); ?>

        <style>
            @font-face {
                font-family: "Roboto";
                font-weight: normal;
                font-style: normal;
                src: url("<?php echo $fonts_path ?>/Roboto-Regular.eot");
                src: url("<?php echo $fonts_path ?>/Roboto-Regular.eot?#iefix") format("embedded-opentype"),
                    url("<?php echo $fonts_path ?>/Roboto-Regular.woff") format("woff"),
                    url("<?php echo $fonts_path ?>/Roboto-Regular.ttf") format("truetype"),
                    url("<?php echo $fonts_path ?>/Roboto-Regular.svg#Roboto") format("svg")
            }

            body {
                margin: 0;
                overflow: hidden;
            }

            iframe {
                opacity: 0;
                width: 100%;
                border: none;
                transition: opacity 0.35s;
                min-height: 100vh;
            }

            main {
                display: flex;
                justify-content: center;
            }

            .container {
                width: 100%;
                position: relative;
                background: #FAFAFA 0% 0% no-repeat padding-box;
            }

            .content {
                overflow: hidden;
            }

            .row {
                width: 100%;
                padding-top: 50px;
                display: flex;
                justify-content: space-between;
                position: absolute;
                z-index: 1;
            }

            .column {
                display: flex;
                justify-content: center;
                width: calc(50% - 300px);
            }

            .btn {
                color: #2F2F2F;
                border: none;
                width: 150px;
                height: 50px;
                padding: 0 15px;
                font-size: 16px;
                outline: none;
                display: none;
                text-align: center;
                align-items: center;
                justify-content: center;
                text-decoration: none;
                font-family: "Roboto";
                box-sizing: border-box;
                cursor: pointer;
                background-color: red;
            }

            .btn.btn-outline-dark {
                border: 1px solid #2F2F2F;
                background: #FFFFFF 0% 0% no-repeat padding-box;
            }

            .btn.btn-outline-dark:active {
                color: #FFFFFF;
                background: #2F2F2F 0% 0% no-repeat padding-box;
            }

            @media only screen and (max-width: 1000px) {

                iframe {
                    min-height: calc(100vh - 50px);
                }

                .row {
                    left: auto;
                    right: auto;
                    margin: 0 auto;
                    padding-top: 0;
                    position: static;
                }
                .column {
                    width: 50%;
                }
                .btn {
                    width: 100%;
                }

                .btn.previous {
                    border-right: none;
                }

                .hidden-previous .column:first-of-type {
                    display: none;
                }

                .hidden-next .column:first-of-type,
                .hidden-previous .column:last-of-type {
                    width: 100%;
                }

                .hidden-next .column:last-of-type {
                    display: none;
                }

                .hidden-next .previous {
                    border-right: 1px solid #2F2F2F;
                }
            }
        </style>

    </head>
    <body>
        <main>
            <div class="container">
                <div id="row" class="row">
                    <div class="column">
                        <button id="previous" class="btn btn-outline-dark previous">Previous</button>
                    </div>
                    <div class="column">
                        <button id="next" class="btn btn-outline-dark next">Next</a>
                    </div>
                </div>
                <div class="content">
                    <iframe id="template" src="<?php echo $archive_path ?>" title="Preview Iframe"></iframe>
                </div>
            </div>
        </main>

        <script>

            var template   = document.getElementById('template'),
                prev       = document.getElementById('previous'),
                next       = document.getElementById('next'),
                row        = document.getElementById('row'),
                identifier = "<?php echo $archive_id ?>",
                hostname   = "<?php echo __SITE_URL ?>",
                fadeIn    = function() {
                    template.style.transition="opacity 1s";
                    template.style.opacity = "1";
                },
                fadeOut   = function() {
                    template.style.transition="opacity 0s";
                    template.style.opacity = "0";
                }

             /**
             * Common action for post requests
             *
             * @param {String} path
             * @param {Object} data
             * @param {Function} callback
             */
            function postRequest(path, data, callback) {
                var request_data = "";
                var xhr = new XMLHttpRequest();
                xhr.open("POST", path, true);

                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhr.setRequestHeader("X-fancyBox", "true");

                if (document.querySelector('meta[name="csrf-token"]')) {
                    xhr.setRequestHeader("X-CSRF-TOKEN", document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                }

                Object.keys(data).forEach(function (k, index) {
                    request_data += !index ? k + "=" + data[k] : "&" + k + "=" + data[k];
                });

                xhr.onload = function (d) {
                    callback(JSON.parse(d.currentTarget.response));
                };

                xhr.send(request_data);
            }

            /**
             * Action used to scale the template inside of an Iframe at resolutions less than 600px.
             *
             */
            function updateTemplateScaleSize() {

                setTimeout(function() {
                    var frame      = template.contentWindow.document.getElementsByTagName("html")[0];
                    var frame_body = template.contentWindow.document.getElementsByTagName("body")[0];

                    if (window.matchMedia("(max-width: 600px)").matches) {
                        frame.style.width = "0";
                        frame.style.height = "0";
                        frame.style.display = "flex";
                        frame.style.justifyContent = "flex-start";
                        frame.style.transform = "scale(" + (window.outerWidth/6) * 0.01 + ")";

                        frame_body.style.padding = "0";
                        frame_body.style.margin = "0";
                    } else {
                        frame.style.width = "auto";
                        frame.style.justifyContent = "center";
                    }

                    fadeIn();

                }, 500);
            }

            /**
             * Action used to update Iframe path and buttons visibility.
             *
             */
            function updateCommonData(response, button) {

                fadeOut();

                if (response.mess_type == "success") {
                    identifier         = response.id;
                    template.src       = response.message;
                    prev.style.display = "block";
                    next.style.display = "block";
                    row.classList.remove("hidden-next");
                    row.classList.remove("hidden-previous");
                    history.pushState({}, 'Export Portal', hostname + "newsletter/archive/" + identifier);

                    updateTemplateScaleSize();
                }
                if (response.last) {
                    button.style.display = "none";
                    row.classList.add("hidden-" + button.id);
                }
            }

            document.addEventListener('DOMContentLoaded', function(event) {

                postRequest("<?php echo $ajax_path ?>", {type: "0", id: identifier}, function (response) {
                    console.log(response);
                    if (response.id) {
                        prev.style.display = "block";
                    }
                    if (response.last) {
                        row.classList.remove("hidden-next");
                        row.classList.add("hidden-prev");
                    }
                });

                postRequest("<?php echo $ajax_path ?>", {type: "1", id: identifier}, function (response) {
                    console.log(response);
                    if (response.id) {
                        next.style.display = "block";
                    }
                    if (response.last) {
                        row.classList.remove("hidden-prev");
                        row.classList.add("hidden-next");
                    }
                });

                prev.addEventListener("click", function() {
                    postRequest("<?php echo $ajax_path ?>", {type: "0", id: identifier}, function (response) {
                        updateCommonData(response, prev);
                    });
                });

                next.addEventListener("click", function() {
                    postRequest("<?php echo $ajax_path ?>", {type: "1", id: identifier}, function (response) {
                        updateCommonData(response, next);
                    });
                });
            });

            window.addEventListener('load', updateTemplateScaleSize, true);
            window.addEventListener('resize', updateTemplateScaleSize, true);

        </script>
    </body>
</html>
