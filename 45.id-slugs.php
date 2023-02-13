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
        'post_type' => ['quiz', 'post', 'club'],
        'orderby' => 'id',
        'order' => 'desc'
    ]);

    // Create custom db connection
    $db = new wpdb('root', '', 'id', 'localhost');

    foreach($posts as $post) {
        $post_id = $post->ID;
        $slug = wp_make_link_relative(get_permalink($post_id));
        $title = get_the_title($post_id);

        // Insert new data
        $db->replace('posts', compact('post_id', 'slug', 'title'));
    }
}
