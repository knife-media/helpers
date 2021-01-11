<?php

$link = new mysqli("localhost", "root", "", "knife");
mysqli_set_charset($link, 'utf8');


if($result = mysqli_query($link, "SELECT post_content, ID from wp_posts where post_status = 'publish' and post_type = 'post' and post_content like '%[caption%'")) {
    while($row = mysqli_fetch_assoc($result)) {
        $id = $row['ID'];
        $content = $row['post_content'];

        preg_match_all('~\[caption(.*?)\](.*?)\[\/caption\]~is', $content, $captions, PREG_SET_ORDER);

        echo $id. ", ";

        foreach($captions as $caption) {
            preg_match('~width="([\d]+?)"~is', $caption[2], $width);
             preg_match('~height="([\d]+?)"~is', $caption[2], $height);

            if(!is_numeric($height[1]))
                echo "$id - fail height\n";

            if(!is_numeric($width[1]))
                echo "$id - fail width\n";

            $class = "figure";

            if($width[1] > 930 && ($width[1] * 2.5 >= $height[1] * 3))
                $class .= " figure--outer";
            elseif($width[1] >= 640)
                $class .= " figure--inner";
            else
                $class .= " figure--full";

            preg_match('~<img.*?src="(.*?)".*?>\s*(.*?)$~is', $caption[2], $atts);

            $figure = sprintf('<figure class="%1$s"><img src="%2$s" data-knife="image"/><figcaption class="figure__caption">%3$s</figcaption></figure>',
                $class, $atts[1], $atts[2]
            );

            $content = str_replace($caption[0], $figure, $content);
        }

        $content = mysqli_real_escape_string($link, $content);
        mysqli_query($link, "UPDATE wp_posts set post_content = '$content' where ID = {$id}");

    }

    mysqli_free_result($result);
}


