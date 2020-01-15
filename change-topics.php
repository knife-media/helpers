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

    $posts = $wpdb->get_results("SELECT id FROM {$wpdb->posts} WHERE post_type = 'post' OR post_type = 'quiz' OR post_type = 'generator' ORDER BY id DESC");

    $articles_id = get_category_by_slug('longreads')->cat_ID;
    $blunt_id = get_category_by_slug('blunt')->cat_ID;
    $games_id = get_category_by_slug('play')->cat_ID;
    $news_id = get_category_by_slug('news')->cat_ID;

    foreach($posts as $post) {
        if(in_array(get_post_type($post->id), ['quiz', 'generator'])) {
            wp_set_post_categories($post->id, [$games_id]);
            continue;
        }

        if(has_tag('bluntmedia', $post->id)) {
            wp_set_post_categories($post->id, [$blunt_id]);
            continue;
        }

        if(has_category('news', $post->id)) {
            wp_set_post_categories($post->id, [$news_id]);
            continue;
        }

        echo $post->id . ', ';

        wp_set_post_categories($post->id, [$articles_id]);
    }
}
