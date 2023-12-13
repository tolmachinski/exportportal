<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link href="<?php echo __FILES_URL;?>public/css/elements.css" rel="stylesheet" />

        <script src="<?php echo fileModificationTime('public/plug/jquery-1-12-0/jquery-1.12.0.min.js');?>"></script>
        <title>EP elements</title>
        <style>
            pre{
                margin-bottom: 0;
            }

            .use-elements{
                margin-bottom: 20px;
            }

            .elements{
                display: flex;
                flex-wrap: wrap;
            }

            .elements__item{
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                width: 80px;
                height: 80px;
                padding: 5px;
                border: 1px solid #ddd;
                margin: 5px;
            }

            .elements__name{
                font-size: 10px;
                line-height: 14px;
            }
        </style>
    </head>

    <body>
        <?php echo $svg;?>

        <a href="<?php echo __SITE_URL;?>epicons/saveFileSvgIcons" target="_blank">Generate file</a>
        <div class="use-elements">
            <pre><code class="html"><?php echo htmlspecialchars('<?php echo getEpIconSvg(\'favorite\', [17, 17]);?>');?></code></pre>
            <pre><code class="html"><?php echo htmlspecialchars('<i class="wr-ep-icon-svg wr-ep-icon-svg--h26"><?php echo getEpIconSvg(\'favorite\', [17, 17]);?></i>');?></code></pre>
        </div>

        <div class="elements">
            <?php foreach($icons as $iconsItem) {?>
                <div class="elements__item">
                    <?php echo getEpIconSvg($iconsItem[0]);?>
                    <span class="elements__name"><?php echo $iconsItem[0];?></span>
                </div>
            <?php }?>
        </div>

        <script>
        $(() => {
            console.log(123);
        });
        </script>
    </body>
</html>

