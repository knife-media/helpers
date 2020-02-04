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


if(!defined('KNIFE_VIEWS')) {
    exit('Undefined db settings');
}

{
    $posts = get_posts([
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'post_type' => ['quiz', 'post', 'generator', 'club', 'select', 'story'],
        'orderby' => 'id',
        'order' => 'desc'
    ]);

    // Mix with default values
    $conf = KNIFE_VIEWS;

    // Create custom db connection
    $db = new wpdb($conf['user'], $conf['password'], $conf['name'], $conf['host']);

    foreach($posts as $post) {
        $post_id = $post->ID;
        $slug = wp_make_link_relative(get_permalink($post_id));
        $publish = $post->post_date;

        $x = compact('post_id', 'slug', 'publish');

        // Insert new data
        $db->replace('posts', compact('post_id', 'slug', 'publish'));

        echo $post->ID . ' ';
    }
}
