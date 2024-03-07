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

function find_posts() {
    $posts = get_posts([
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'post_type' => ['post'],
        'orderby' => 'id',
        'order' => 'desc',
        'category_name' => 'longreads',
        'fields' => 'ids'
    ]);

    $groups = [];

    foreach ($posts as $post) {
        if (get_post_meta($post, '_knife-promo', true)) {
            continue;
        }

        $authors = Knife_Authors_Manager::get_post_authors($post);

        if (in_array('1304', $authors, false)) {
            continue;
        }

        $editor = get_post_meta( $post, Knife_Authors_Manager::$meta_editor, true );

        if ($editor !== 'seroe-fioletovoe') {
            continue;
        }

        $content = wp_strip_all_tags(get_the_content(null, false, $post));

        $create = get_the_date('Y-m', $post);

        if (empty($groups[$create])) {
            $groups[$create] = [
                'length' => 0,
                'amount' => 0,
            ];
        }

        $groups[$create]['length'] += strlen($content);
        $groups[$create]['amount'] += 1;
    }

    foreach ($groups as $key => $group) {
        echo $key . ': ' . $group['amount'] . ' - ' . round($group['length'] / $group['amount'], 2) . "\n";
    }
}


find_posts();
