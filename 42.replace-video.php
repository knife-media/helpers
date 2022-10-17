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

    $posts = $wpdb->get_results("SELECT id, post_content FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_content LIKE '%[video%' ORDER BY id ASC");

    foreach($posts as $post) {
        $content = $post->post_content;

        if(preg_match_all('~\[video .*?mp4="(.+?)".*?\].*?\[\/video\]~is', $content, $matches, PREG_SET_ORDER)) {
           foreach($matches as $match) {
               $content = str_replace($match[0], '<figure class="figure figure--embed"><video src="' . $match[1]  .  '" controls></video></figure>', $content);
           }

           $content = esc_sql($content);

           $wpdb->query("UPDATE {$wpdb->posts} SET post_content = '{$content}' WHERE ID = " . absint($post->id));
           echo WP_SITEURL .  "/?p={$post->id}\n";
        }
    }
}
