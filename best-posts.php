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
        'tax_query' => [[
            'taxonomy' => 'label',
            'field' => 'slug',
            'terms' => 'best'
        ]]
    ]);

    foreach($posts as $post) {
        if(!update_post_meta($post->ID, '_knife-best', 1)) {
            echo "ERROR: ";
        }

        echo $post->ID . ', ';
    }
}
