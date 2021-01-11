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

    $posts = $wpdb->get_results("SELECT id, post_content FROM {$wpdb->posts} WHERE post_status = 'publish' ORDER BY id ASC");

    foreach($posts as $post) {
        $content = $post->post_content;
        $updated = preg_replace('~(<a[^>]+>)(\s+)(.*?</a>)~is', '$2$1$3', $content);

        if($updated !== $content) {
            $updated = esc_sql($updated);

            $wpdb->query("UPDATE {$wpdb->posts} SET post_content = '{$updated}' WHERE ID = " . absint($post->id));
            echo WP_SITEURL .  "/?p={$post->id}\n";
        }
    }
}
