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
        $updated = wp_targeted_link_rel(links_add_target($content));

        $post_lead = get_post_meta($post->id, '_knife-lead', true);

        if ($post_lead) {
            $post_lead = wp_targeted_link_rel(links_add_target($post_lead));

            // Update post lead
            update_post_meta($post->id, '_knife-lead', $post_lead);
        }

        if($updated !== $content) {
            $updated = esc_sql($updated);

            $wpdb->query("UPDATE {$wpdb->posts} SET post_content = '{$updated}' WHERE ID = " . absint($post->id));
            echo WP_SITEURL .  "/?p={$post->id}\n";
        }
    }
}
