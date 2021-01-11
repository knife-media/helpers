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
    global $wbdb;

    $posts = $wpdb->get_results("SELECT id FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish' ORDER BY id DESC");

    foreach($posts as $post) {
        if(get_post_format($post->id) == 'aside') {
            update_post_meta($post->id, '_wp_page_template', 'templates/single-aside.php');

            // Clear post format
            set_post_format($post->id, '');

            echo "aside: " . $post->id . ', ';
            continue;
        }

        if(get_post_format($post->id) == 'chat') {
            update_post_meta($post->id, '_wp_page_template', 'templates/single-cards.php');

            // Clear post format
            set_post_format($post->id, '');

            echo "cards: " . $post->id . ', ';
            continue;
        }
    }
}
