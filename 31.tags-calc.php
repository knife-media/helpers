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


if(!defined('KNIFE_VIEWS')) {
    exit('Undefined db settings');
}

{
    $posts = get_posts([
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'post_type' => ['post', 'quiz', 'generator', 'club'],
        'category' => [2053, 2054],
        'orderby' => 'id',
        'order' => 'desc',
        'fields' => 'ids'
    ]);

    $groups = [];

    // Mix with default values
    $conf = KNIFE_VIEWS;

    // Create custom db connection
    $db = new wpdb($conf['user'], $conf['password'], $conf['name'], $conf['host']);

    foreach ($posts as $post) {
        if (get_post_meta($post, '_knife-promo', true)) {
            continue;
        }

        $tags = get_the_tags($post);

        if(empty($tags)) {
            $groups[ 'Без тега' ][] = $post;

            continue;
        }

        foreach ($tags as $tag) {
            $groups[ $tag->name ][] = $post;
        }
    }

    echo'<style>table {border: solid 1px #ccc;} td{padding: 10px; border: solid 1px #ccc; } body{font: 400 14px/1.25 -apple-system, sans-serif; max-width: 800px; margin: 0 auto;} h1{font-size: 24px; margin-top: 40px;} h4{font-size: 16px; margin: 0;} em {font-style: normal;}</style>';

    // Sort by count of values
    array_multisort(array_map('count', $groups), SORT_DESC, $groups);

    foreach ($groups as $tag => $data) {
        echo "<h1>{$tag}</h1><h4>" . count($data). " записей</h4>";
        echo "<table>";

        $ids = implode(',', $data);

        $views = $db->get_results("SELECT post_id, pageviews FROM posts WHERE post_id IN ({$ids}) ORDER BY pageviews DESC");

        foreach ($views as $view) {
            echo '<tr><td><a href="' . get_permalink($view->post_id) . '">' . get_the_title($view->post_id) . '</a></td><td>' . $view->pageviews . '</td></tr>';
        }

        echo "</table>";
    }
}
