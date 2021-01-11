<?php

$link = new mysqli("localhost", "root", "", "knife");
mysqli_set_charset($link, 'utf8');


if($result = mysqli_query($link, "SELECT post_content, ID from wp_posts where post_status = 'publish' and post_type = 'post' and post_content like '%<img%'")) {
    while($row = mysqli_fetch_assoc($result)) {
        $id = $row['ID'];
        $content = $row['post_content'];

        $content = preg_replace('~<a[^>]+>(<img[^>]+>)</a>~is', '$1', $content);
        $content = preg_replace('~<h2>(<img[^>]+>)</h2>~is', '$1', $content);
        $content = preg_replace('~<h3>(<img[^>]+>)</h3>~is', '$1', $content);
        $content = preg_replace('~<h4>(<img[^>]+>)</h4>~is', '$1', $content);

        preg_match_all('~<img(.*?)>~is', $content, $captions, PREG_SET_ORDER);

        echo $id. ", ";

        foreach($captions as $caption) {
            if(strpos($caption[1], 'data-knife="image"') !== false) {
                $content = str_replace('data-knife="image"', '', $content);

                continue;
            }

            preg_match('~width="([\d]+?)"~is', $caption[1], $width);
            preg_match('~height="([\d]+?)"~is', $caption[1], $height);

            if(!isset($width[1]) || !is_numeric($width[1])) {
                echo "\n$id - fail width\n";

                continue;
            }

            $class = "figure";

            if(!isset($height[1]) || !is_numeric($height[1]))
                $class .= " figure--full";
            elseif($width[1] > 930 && ($width[1] * 2.5 >= $height[1] * 3))
                $class .= " figure--outer";
            elseif($width[1] >= 640)
                $class .= " figure--inner";
            else
                $class .= " figure--full";

            preg_match('~src="(.*?)"~is', $caption[1], $atts);

            $figure = sprintf('<figure class="%1$s"><img src="%2$s" /></figure>',
                $class, $atts[1]
            );

            $content = str_replace($caption[0], $figure, $content);
        }

        $content = mysqli_real_escape_string($link, $content);
        mysqli_query($link, "UPDATE wp_posts set post_content = '$content' where ID = {$id}");

    }

    mysqli_free_result($result);
}


