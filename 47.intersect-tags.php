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
        'posts_per_page' => 20,
        'post_status' => 'publish',
        'post_type' => ['post'],
        'tax_query' => array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'category',
                'field'    => 'slug',
                'terms'    => 'longreads',
                'operator' => 'IN',
            ),

            array(
                'taxonomy' => 'post_tag',
                'field'    => 'slug',
                'terms'    => 'science',
                'operator' => 'IN',
            ),
            array(
                'taxonomy' => 'post_tag',
                'field'    => 'slug',
                'terms'    => 'russia',
                'operator' => 'IN',
            ),
        ),
        'orderby' => 'id',
        'order' => 'desc',
    ]);

    foreach ($posts as $post) {
        echo strip_tags(get_the_title($post->ID)) . "\n" . get_permalink($post->ID) . "\n\n";
    }
}
