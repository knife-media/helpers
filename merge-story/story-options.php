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

    $posts = get_posts([
        'post_type' => 'story',
        'posts_per_page' => -1
    ]);

    foreach($posts as $post) {
        $options = [];

        $options['blur'] = get_post_meta($post->ID, '_knife-story-blur', true);
        $options['background'] = get_post_meta($post->ID, '_knife-story-background', true);
        $options['shadow'] = get_post_meta($post->ID, '_knife-story-shadow', true);

        $stories = get_post_meta($post->ID, '_knife-story-stories');

        foreach($stories as $story) {
            add_post_meta($post->ID, '_knife-story-items', $story);
        }

        // Create new options
        add_post_meta($post->ID, '_knife-story-options', $options);

        // Delete old options
        delete_post_meta($post->ID, '_knife-story-blur');
        delete_post_meta($post->ID, '_knife-story-shadow');
        delete_post_meta($post->ID, '_knife-story-background');

        delete_post_meta($post->ID, '_knife-story-stories');

        echo $post->ID . "\n";
    }
}
