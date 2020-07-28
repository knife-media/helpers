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
    $posts = get_posts([
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'post_type' => ['post'],
        'orderby' => 'id',
        'order' => 'asc',
        'date_query' => [
            [
                'year' => '2020',
                'month' => '07'
            ],
        ],
        'fields' => 'ids'
    ]);

    foreach ($posts as $post) {
        $meta = get_post_meta($post, '_knife-distribute-items', true);

        foreach ($meta as $uniquid => $task) {
            if (empty($task['complete'])) {
                echo "Пост: {$post} / " . get_permalink($post) . "\n";

                break;
            }
        }
    }
}
