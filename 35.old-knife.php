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
    $tags = get_tags();

    $groups = [];

    foreach ($tags as $one) {
        foreach ($tags as $two) {
            if ($one->name === $two->name) {
                continue;
            }

            if (array_key_exists("$two->name,$one->name", $groups)) {
                continue;
            }

            $groups["$one->name,$two->name"] = [];
        }
    }

    $posts = get_posts([
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'post_type' => ['post'],
        'orderby' => 'post_date',
        'date_query' => [
            'before' => [
                'year'  => 2017,
                'month' => 7,
                'day'   => 1,
            ],
        ],
        'order' => 'asc',
        'fields' => 'ids'
    ]);

    $result = [];

    // Mix with default values
    $conf = KNIFE_VIEWS;

    // Create custom db connection
    $db = new wpdb($conf['user'], $conf['password'], $conf['name'], $conf['host']);

    echo'<style>table {border: solid 1px #ccc;} td{padding: 10px; border: solid 1px #ccc; } body{font: 400 14px/1.25 -apple-system, sans-serif; max-width: 800px; margin: 0 auto;} h1{font-size: 24px; margin-top: 40px;} h4{font-size: 16px; margin: 0;} em {font-style: normal;}</style>';
    echo "<table>";

    foreach ($posts as $post) {
        if (get_post_meta($post, '_knife-promo', true)) {
            continue;
        }
        echo '<tr>';

        echo '<td>' . get_the_date('d.m.Y', $post) . '</td>';

        echo '<td><a href="' . get_permalink($post) . '">' . get_the_title($post) . '</a></td><td>';

        $tags = get_the_tags($post);

        if(!empty($tags)) {
            foreach ($tags as $tag) {
                echo $tag->name . ", ";
            }
        }

        echo '</td><td>';

        $authors = get_post_meta($post, '_knife-authors');

        if(!empty($authors)) {
            foreach($authors as $author) {
                echo get_userdata($author)->display_name . ', ';
            }
        }

        echo '</td><td>';

        $views = $db->get_var("SELECT pageviews FROM posts WHERE post_id = $post");

        if ($views) {
            echo $views;
        }

        echo '</td></tr>';
    }

    echo "</table>";
}
