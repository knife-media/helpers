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
    $results = $wpdb->get_results(
        $wpdb->prepare( "SELECT * FROM {$wpdb->postmeta} WHERE meta_key = '_knife-authors'" )
    );

    foreach ($results as $result) {
        $wpdb->query("INSERT INTO {$wpdb->prefix}authors (post_id, user_id) VALUES ({$result->post_id}, {$result->meta_value})");
        echo $result->post_id . ", ";
    }
}
