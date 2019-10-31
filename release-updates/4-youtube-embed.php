<?php

$link = new mysqli("localhost", "root", "", "knife");
mysqli_set_charset($link, 'utf8');


if($result = mysqli_query($link, "SELECT post_content, ID from wp_posts where post_status = 'publish' and post_type = 'post' and post_content like '%<iframe%'")) {
    while($row = mysqli_fetch_assoc($result)) {
        $id = $row['ID'];
        $content = $row['post_content'];

        preg_match_all('~<iframe(.*?)>[^<]*</iframe>~is', $content, $captions, PREG_SET_ORDER);

        foreach($captions as $caption) {
            if(!preg_match('~src=".*?youtube.com/embed/([A-Za-z0-9-_]+?)"~is', $caption[1], $src))
                continue;

            $content = str_replace($caption[0], 'https://www.youtube.com/embed/' . $src[1], $content);

            echo "$id: {$src[1]}\n";

        }

        $content = mysqli_real_escape_string($link, $content);
        mysqli_query($link, "UPDATE wp_posts set post_content = '$content' where ID = {$id}");

    }

    mysqli_free_result($result);
}


