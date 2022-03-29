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
    /**
    $posts = get_posts([
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'post_type' => ['post'],
        'orderby' => 'id',
        'order' => 'desc',
        'date_query' => [
            [
                'before' => ['year'=>'2020', 'month'=>'3', 'day'=>'19'],
                'after' => ['year'=>'2020', 'month'=>'2', 'day'=>'16'],
                'inclusive' => true,
            ],
        ],
        'category_name' => 'longreads',
        'tag__not_in' => [198, 586],
        'fields' => 'ids'
    ]);

    foreach ($posts as $post) {
        if(get_post_meta($post, '_knife-promo')) {
            continue;
        }

        echo html_entity_decode(wp_strip_all_tags(get_the_title($post))) . "\t" . get_permalink($post) . "\n";
    }
     */

    $items = explode("\n", file_get_contents(__DIR__ . "/tags.txt"));

    foreach ($items as $item) {
        $arr = explode(";", $item);

        if (!isset($arr[0], $arr[1])) {
            continue;
        }

        if ($arr[1] !== 'бизнес' && $arr[1] !== 'образование') {
            continue;
        }

        $post = url_to_postid($arr[0]);

        if (!$post) {
            continue;
        }

        $term = get_term_by('name', $arr[1], 'post_tag');

        if (empty($term->term_id)) {
            continue;
        }

        $result = wp_remove_object_terms($post, $term->term_id, 'post_tag');

        if ($result) {
            echo "$post, ";
        }
    }
}
