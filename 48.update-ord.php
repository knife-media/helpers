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
    $posts = $wpdb->get_results(
        $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE post_content LIKE '%ads.adfox.ru/265942/%' AND post_status = 'publish'" )
    );

    foreach ($posts as $post) {
        $meta = get_post_meta($post->ID, '_knife-promo-options', true);

        if (empty($meta['link'])) {
            continue;
        }

        $content = preg_replace('#https?://ads.adfox.ru/265942/[^\"\'\s]+#is', $meta['link'], $post->post_content);
        $content = esc_sql($content);

        $wpdb->query("UPDATE {$wpdb->posts} SET post_content = '{$content}' WHERE ID = " . absint($post->ID));
        echo WP_SITEURL .  "/?p={$post->ID}\n";
    }
}
