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


if(!defined('KNIFE_ANALYTICS')) {
    exit('Undefined db settings');
}

function median($arr) {
 sort($arr);

 $count = count($arr);
 $middle = floor($count / 2);

 if($count % 2) {
     return $arr[$middle];
 }

 return ($arr[$middle-1] + $arr[$middle]) / 2;
}

{
    $posts = get_posts([
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'post_type' => ['post'],
        'orderby' => 'id',
        'order' => 'desc',
        'date_query' => [
            [
                'month' => '4',
                'year' => '2023'
            ],
        ],
        'fields' => 'ids'
    ]);

    // Mix with default values
    $conf = KNIFE_ANALYTICS;

    // Create custom db connection
    $db = new wpdb($conf['user'], $conf['password'], $conf['name'], $conf['host']);

    $ids = implode(',', $posts);

    // Get data
    $data = $db->get_results("SELECT * FROM views WHERE post_id IN ({$ids}) ORDER BY pageviews DESC");

    // Slice data
#   $data = array_slice($data, 100, -500);

    // Compiled data
    $views = [];

    foreach($data as $item) {
        if(empty($item->pageviews)) {
            continue;
        }

        $post_id = $item->post_id;

        // Get format
        $group = get_post_type($post_id);

        // Get category
        $author = Knife_Authors_Manager::get_post_authors($post_id)[0];

        if(empty($author)) {
            continue;
        }

        $group = get_userdata($author)->user_login;

        // Get category
        $category = get_the_category($post_id);

        if(empty($category[0]) || !in_array($category[0]->slug, ['news'])) {
            continue;
        }

        $views[$group][] = $item->pageviews;
    }

    $groups = array_keys($views);

    foreach($groups as $g) {
        $mid = round(array_sum($views[$g]) / count($views[$g]), 2);
        $med = median($views[$g]);

        //echo "$g: $mid / $med \n";
        echo $g . " - сумма просмотров: " . array_sum($views[$g]) . ', количество статей: ' . count($views[$g]) . "\n";
    }
}
