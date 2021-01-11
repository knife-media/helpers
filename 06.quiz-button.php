<?php

$link = new mysqli("localhost", "root", "", "knife");
mysqli_set_charset($link, 'utf8');


if($result = mysqli_query($link, "SELECT post_content, ID from wp_posts where post_status = 'publish' and post_type = 'post' and post_content like '%<h3%'")) {
    while($row = mysqli_fetch_assoc($result)) {
        $id = $row['ID'];
        $content = $row['post_content'];

        $test = preg_match_all('~\<h3>\s*<a.*?href="([^"]+)">(.+?)</a>\s*</h3>~is', $content, $titles, PREG_SET_ORDER);
        if(!$test)
            continue;

        echo $id. ", ";

        foreach($titles as $title) {
            print_r($title);

            $href = $title[1];
            $text = strip_tags($title[2]);

            $button = sprintf('<a class="button" href="%1$s" target="_blank">%2$s</a>', $href, $text);

            $content = str_replace($title[0], "\n" . $button, $content);
        }

        $content = mysqli_real_escape_string($link, $content);
        mysqli_query($link, "UPDATE wp_posts set post_content = '$content' where ID = {$id}");
    }

    mysqli_free_result($result);
}


