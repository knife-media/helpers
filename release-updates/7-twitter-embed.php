<?php

$link = new mysqli("localhost", "root", "", "knife");
mysqli_set_charset($link, 'utf8');


if($result = mysqli_query($link, "SELECT post_content, ID from wp_posts where post_status = 'publish' and post_type = 'post' and post_content like '%<blockquote%'")) {
    while($row = mysqli_fetch_assoc($result)) {
        $id = $row['ID'];
        $content = $row['post_content'];

        preg_match_all('~<blockquote\s+class="twitter-tweet"(.+?)</blockquote>~is', $content, $captions, PREG_SET_ORDER);

        foreach($captions as $caption) {
            preg_match('#https?://twitter\.com/(?:\#!/)?(\w+)/status(es)?/(\d+)#i', $caption[1], $href);
            echo "$id: $href[0]\n";

            $content = str_replace($caption[0], $href[0], $content);
        }

        $content = str_replace('<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>', '', $content);
        $content = str_replace('<script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>', '', $content);

        $content = mysqli_real_escape_string($link, $content);
        mysqli_query($link, "UPDATE wp_posts set post_content = '$content' where ID = {$id}");

    }

    mysqli_free_result($result);
}


