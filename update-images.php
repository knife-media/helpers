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

    $posts = $wpdb->get_results("SELECT post_content, id from {$wpdb->posts} where post_status = 'publish'");

    foreach($posts as $post) {
        $content = preg_replace('~<a[^>]+>\s*(<img[^>]+>)\s*</a>~is', '$1', $post->post_content);

        if($content === $post->post_content) {
            continue;
        }

        $content = esc_sql($content);
        $wpdb->query("UPDATE {$wpdb->posts} SET post_content = '{$content}' WHERE ID = " . absint($post->id));
    }
}
