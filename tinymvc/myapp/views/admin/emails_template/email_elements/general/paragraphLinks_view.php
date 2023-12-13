
<?php
    $links = [];
    $linkTemplate = "<a
                        style='max-width: 520px; word-wrap: break-word; word-break: break-word; font-family: Arial, Helvetica, sans-serif; font-size: 16px; line-height: 24px; color: #1155cc !important; color: #1155cc'
                        href='[LINK]'
                        target='_blank'
                    ><span style='color: #1155cc; color: #1155cc !important; text-decoration: underline !important; text-decoration: underline'>[TEXT]</span></a>";

    if(
        !empty($params['paragraphLinksTextLinks'])
        && !empty($params['paragraphLinksTextLinksValues'])
        && count($paragraphLinksTextLinks = explode(PHP_EOL, $params['paragraphLinksTextLinks'])) === count($paragraphLinksTextLinksValues = explode(PHP_EOL, $params['paragraphLinksTextLinksValues']))
    ){

        foreach ($paragraphLinksTextLinks as $paragraphLinksTextLinksKey => $paragraphLinksTextLinksItem) {
            $links[$paragraphLinksTextLinksItem] = str_replace(['[LINK]', '[TEXT]'], [$paragraphLinksTextLinksItem, $paragraphLinksTextLinksValues[$paragraphLinksTextLinksKey]], $linkTemplate);
        }

    }
?>

<tr>
    <td
        class="color-dark"
        style="padding: 0px 0px 16px 0px; font-family: Arial, Helvetica, sans-serif; font-size: 16px; line-height: 24px; color: #000000"
    ><?php
        if (!empty($links)) {
            echo str_replace(array_keys($links), array_values($links), $params['paragraphLinksText']);
        } else {
            echo $params['paragraphLinksText'];
        }
    ?></td>
</tr>
