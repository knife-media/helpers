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

define('WP_CACHE', false);
define('WP_DEBUG', true);
define('WP_USE_THEMES', false);

require( __DIR__ . '/../wordpress/wp-load.php');

{
    $posts = get_posts([
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'post_type' => 'any',
        'orderby' => 'id',
        'order' => 'asc',
    ]);

    foreach ($posts as $post) {
        $authors = get_post_meta($post->ID, '_knife-authors');

        if (count(array_intersect([132, 135, 461], $authors)) === 0) {
            continue;
        }

        update_post_meta($post->ID, '_knife-translations', 1);
        echo $post->ID . ", ";
    }
}
