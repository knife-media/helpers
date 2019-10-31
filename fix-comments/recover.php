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

require(__DIR__ . '/wordpress/wp-load.php');


{
    global $wbdb;

    $posts = $wpdb->get_results("SELECT ID, post_type FROM {$wpdb->posts} WHERE post_status = 'publish' ORDER BY post_date DESC");

    $types = [];

    foreach($posts as $post) {
        if(!array_key_exists($post->post_type, $types)) {
            $types[$post->post_type] = get_default_comment_status($post->post_type);
        }

        $default = $types[$post->post_type];

        wp_update_post([
            'ID' => $post->ID,
            'comment_status' => $default
        ]);

        echo "{$post->ID}: {$default}, ";
    }
}
