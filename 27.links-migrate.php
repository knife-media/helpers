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
        'post_type' => 'any',
        'orderby' => 'id',
        'order' => 'asc'
    ]);

    // Mix with default values
    $conf = KNIFE_VIEWS;

    // Create custom db connection
    $db = new wpdb($conf['user'], $conf['password'], $conf['name'], $conf['host']);

    // Current site host name
    $host = wp_parse_url(get_site_url(), PHP_URL_HOST);

    foreach ($posts as $post) {
        $post_id = $post->ID;

        // Try to find all links
        preg_match_all('~(https?://.+?)[\'"\s]~', $post->post_content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $ext = null;

            // Parse url
            $url = wp_parse_url($match[1]);

            if (!empty($url['path'])) {
                $ext = pathinfo($url['path'], PATHINFO_EXTENSION);
            }

            // Skip images on current hostname
            if (!empty($ext) && $url['host'] === $host) {
                continue;
            }

            $db->insert('links', ['post_id' => $post->ID, 'link' => $match[1]]);
        }
    }
}
