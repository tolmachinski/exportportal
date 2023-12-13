<?php
    use const App\Common\ROOT_PATH;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="<?php echo asset("public/img/favicon/favicon.ico", "legacy");?>" type="image/x-icon">
    <title>EP Backstop generator</title>
    <?php
        encoreEntryLinkTags("backstop");
        encoreLinks();
        views("new/js_global_vars_view");
    ?>
    <?php if (logged_in()) {?>
        <meta name="csrf-token" content="<?php echo session()->csrfToken;?>">
    <?php }?>
</head>
<body>
    <form
        id="formTest"
        class="container-900"
    >
        <div class="row">
            <div class="col">
                <h2>
                    Media:
                    <button class="checkboxers" data-target="#media-block" data-type="check-all" type="button">Check All</button>
                    <button class="checkboxers" data-target="#media-block" data-type="clear-all" type="button">Clear All</button>
                </h2>
                <div id="media-block" class="selected-block media-block">
                    <div><input id="media-1" type="checkbox" data-name="w1920" data-resolution="1560x2000" checked><label for="media-1">L-Desktop 1560</label></div>
                    <div><input id="media-2" type="checkbox" data-name="w768" data-resolution="768x2000" checked><label for="media-2">M-tablet 768</label></div>
                    <div><input id="media-3" type="checkbox" data-name="w360" data-resolution="360x2000" checked><label for="media-3">XS-mobile 360</label></div>
                </div>

                <h2>Config:</h2>
                <div class="form-element">
                    <label for="asyncCaptureLimit">Number of async tests:</label>
                    <input id="asyncCaptureLimit" type="number" value="10">
                </div>
                <div class="form-element">
                    <label for="asyncCompareLimit">Number of async image compares:</label>
                    <input id="asyncCompareLimit" type="number" value="15">
                </div>
                <div class="form-element form-element--row">
                    <label for="debug">Debug mode:</label>
                    <input id="debug" type="checkbox">
                </div>
            </div>
            <div class="col">
                <h2>
                    Pages:

                    <button class="checkboxers" data-target="#pages-block" data-type="check-all" type="button">Check All Searched</button>
                    <button class="checkboxers" data-target="#pages-block" data-type="clear-all" type="button">Clear All Searched</button>
                </h2>
                <input class="search-pages" data-target="#pages-block" type="text" placeholder="Search" autocomplete="off">
                <div class="selected-block pages-block" id="pages-block">
                    <?php
                        foreach(array_splice(scandir(ROOT_PATH . '/tests/front_end/backstop/pages'), 2) as $val){
                            $val = str_replace(".js", "", $val);
                            echo "<div><input id=\"id-page-{$val}\" type=\"checkbox\" value=\"{$val}\"><label for=\"id-page-{$val}\">{$val}</label></div>";
                        }
                    ?>
                </div>
            </div>
        </div>

        <div class="actions-block">
            <button class="btn-submit" type="submit">Generate</button>
        </div>
    </form>
    <div class="alert"></div>

    <img class="lemur" src="<?php echo asset('public/build/images/backstop/lemur.png');?>" alt="lemur">
</body>

<?php
    encoreEntryScriptTags("backstop");
    encoreScripts();
?>
</html>
