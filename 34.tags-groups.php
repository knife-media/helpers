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

$groups = [
    ['поколения', 'возраст', 'детство'],
    ['гендер', 'женщины', 'феминизм', 'мужчины'],
    ['наука', 'исследование', 'мозг', 'интеллект', 'биология', 'космос', 'экология'],
    ['психология', 'самозразвитие'],
    ['общение', 'отношения', 'любовь'],
    ['секс', 'порно'],
    ['экономика', 'деньги', 'бедность'],
    ['технологии'],
];

$result = [];

{
    $tags = get_tags();

    $posts = get_posts([
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'post_type' => ['post', 'quiz', 'generator', 'club'],
        'category' => [2053, 2054],
        'orderby' => 'id',
        'order' => 'desc',
        'fields' => 'ids'
    ]);

    $result = [];

    // Mix with default values
    $conf = KNIFE_VIEWS;

    // Create custom db connection
    $db = new wpdb($conf['user'], $conf['password'], $conf['name'], $conf['host']);

    foreach ($posts as $post) {
        $tags = get_the_tags($post);

        if(empty($tags)) {
            continue;
        }

        $post_tags = [];

        foreach ($tags as $tag) {
            $post_tags[] = $tag->name;
        }

        foreach ($groups as $group) {
            $intersect = array_intersect($post_tags, $group);

            if(count($intersect) > 1) {
                $views = $db->get_var("SELECT pageviews FROM posts WHERE post_id = $post");

                $key = implode(',', $intersect);

                $result[$key][$post] = $views;
            }
        }
    }

    $new = [];

    foreach ( $result as $key => $value ) {
        arsort($value);
        $new[$key] = $value;
    }

    echo'<style>table {border: solid 1px #ccc;} td{padding: 10px; border: solid 1px #ccc; } body{font: 400 14px/1.25 -apple-system, sans-serif; max-width: 800px; margin: 0 auto;} h1{font-size: 24px; margin-top: 40px;} h4{font-size: 16px; margin: 0;} em {font-style: normal;}</style>';

    foreach ($new as $key => $value) {
        if (get_post_meta($post, '_knife-promo', true)) {
            continue;
        }

        echo '<h1>' . $key . '</h1>';
        echo '<table>';

        foreach ($value as $post => $views) {
            echo '<tr>';
            echo '<td>' . get_the_date('d.m.Y', $post) . '</td>';

            echo '<td><a href="' . get_permalink($post) . '">' . get_the_title($post) . '</a></td><td>';

            $authors = get_post_meta($post, '_knife-authors');

            if(!empty($authors)) {
                foreach($authors as $author) {
                    echo get_userdata($author)->display_name . ', ';
                }
            }

            echo '</td><td>';
            echo $views;
            echo '</td></tr>';
        }

        echo "</table>";
    }
  }
