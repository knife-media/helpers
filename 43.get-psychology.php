<?php

if(php_sapi_name() !== 'cli') {
    exit;
}

$_SERVER = [
    "SERVER_PROTOCOL" => "HTTP/1.1",
    "HTTP_HOST"       => "knife.media",
    "SERVER_NAME"     => "knife.media",
    "REQUEST_URI"     => "/",
    "REQUEST_METHOD"  => "GET"
];

#define('WP_CACHE', false);
#define('WP_DEBUG', true);
define('WP_USE_THEMES', false);

require( __DIR__ . '/../wordpress/wp-load.php');

{
    $posts = get_posts([
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'post_type' => ['post', 'club'],
        'tag' => 'psychology',
        'orderby' => 'post_date',
        'order' => 'desc',
        'fields' => 'ids'
    ]);
    $result = [];

    echo'<style>table {border: solid 1px #ccc;} td{padding: 10px; border: solid 1px #ccc; } body{font: 400 14px/1.25 -apple-system, sans-serif; max-width: 800px; margin: 0 auto;} h1{font-size: 24px; margin-top: 40px;} h4{font-size: 16px; margin: 0;} em {font-style: normal;}</style>';
    echo "<table>";

    $i = 1;

    foreach ($posts as $post) {
        $cats = get_the_category($post);

        foreach ($cats as $cat) {
            if ($cat->slug === 'longreads') {
                break;
            }

            continue 2;
        }

        if (get_post_meta($post, '_knife-promo', true)) {
            continue;
        }

        echo '<tr>';

        echo '<td>' . $i++ . '</td>';
        echo '<td>' . get_the_date('d.m.Y', $post) . '</td>';
        echo '<td><a href="' . get_permalink($post) . '">' . get_the_title($post) . '</a></td><td>';

        $authors = get_post_meta($post, '_knife-authors');

        if(!empty($authors)) {
            foreach($authors as $author) {
                echo get_userdata($author)->display_name . ', ';
            }
        }

        echo '</td></tr>';
    }

    echo "</table>";
}
