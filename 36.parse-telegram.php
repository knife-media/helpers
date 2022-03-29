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
    global $wpdb;

    $leads = $wpdb->get_results("SELECT post_id, meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key = '_knife-lead'");

    $results = [];

    foreach ($leads as $lead) {
        $value = $lead->meta_value;

        $authors = get_post_meta($lead->post_id, '_knife-authors');
        foreach($authors as &$author) {
            $user = get_userdata($author);
            $author = $user->display_name;
        }

        if (preg_match('~(https://tgram.link/.+?)"~', $value, $match)) {
            $link = str_replace('https://tgram.link', 'https://t.me', $match[1]);

            if (!array_key_exists($link, $results)) {
                $results[$link] = $authors;
            }
        }

        if (preg_match('~(https://t.me/.+?)"~', $value, $match)) {
            if (!array_key_exists($link, $results)) {
                $results[$link] = $authors;
            }
        }

    }

    foreach ($results as $link => $names) {
        echo $link . "\t" . implode(", ", $names) . "\n";
    }
}
