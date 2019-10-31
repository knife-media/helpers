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

require( __DIR__ . '/../../wordpress/wp-load.php');


{
    global $wbdb;

    $posts = $wpdb->get_results("SELECT post_content, id from {$wpdb->posts} where post_status = 'publish' and post_content like '%<span%'");

    foreach($posts as $post) {
        $content = $post->post_content;

        $content = preg_replace('~<span.*?>(.*?)</span>~is', '$1', $post->post_content);
        $content = esc_sql($content);

        $wpdb->query("UPDATE {$wpdb->posts} SET post_content = '{$content}' WHERE ID = " . absint($post->id));
        echo 'https://knife.media/?p=' . $post->id . "\n";
    }
}
