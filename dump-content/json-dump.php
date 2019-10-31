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

require_once(__DIR__ . '/../../wordpress/wp-load.php');

$posts = get_posts([
    'post_type' => ['post', 'story', 'club', 'select'],
    'posts_per_page' => 2,
]);

$array = [];

echo '[';

foreach($posts as $counter => $post) {
    $data = [
        'id' => $post->ID,
        'title' => get_the_title($post->ID),
        'link' => get_permalink($post->ID),
        'date' => $post->post_date,
        'description' => strip_tags($post->post_excerpt)
    ];

    $data['authors'] = coauthors(null, null, null, null, false);

    $text = strip_tags($post->post_content);
    $text = preg_replace('/\s+/', ' ', $text);
    $text = str_replace(array("[card]", "[/card]"), '', $text);
    $text = html_entity_decode($text);

    $text = preg_replace('/&([a-zA-Z0-9]{2,6}|#[0-9]{2,4});/', '', $text);
    $text = str_replace('|+|amp|+|', '&', $text);

    if($categories = wp_get_post_categories($post->ID, ['fields' => 'names'])) {
        foreach($categories as $category) {
            $data['category'][] = $category;
        }
    }

    if($tags = get_the_tags()) {
        foreach($tags as $tag) {
            $data['tag'][] = $tag->name;
        }
    }

    $data['type'] = get_post_type() ?? 'post';

    if(has_post_format()) {
        $data['format'] = get_post_format();
    }

    if(has_post_thumbnail()) {
        $data['thumbnail'] = get_the_post_thumbnail_url($post->ID, 'full');
    }

    if($lead = get_post_meta($post->ID, 'lead-text', true)) {
        $data['lead'] = strip_tags($lead);
    }

    $data['content'] = $text;

    if($counter > 0) {
        echo ',';
    }

    echo json_encode($data);
}

echo ']';
