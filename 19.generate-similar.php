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
        'post_type' => Knife_Similar_Posts::$post_type,
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids'
    ]);

    foreach($posts as $post) {
        Knife_Similar_Posts::generate_similar($post);
    }
}
