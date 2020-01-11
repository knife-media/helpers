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

    $ids = '18494, 16842, 29970, 33664, 39754, 44196, 58345, 13800, 33264, 63444, 70183, 71022, 85636, 5178, 21919, 24628, 24863, 26410, 31372, 41928, 54855, 75164, 80967, 930, 14196, 16066, 18159, 18478, 86319, 91259, 1930, 2515, 3088, 31736, 54450, 72182, 77345, 9000, 13282, 15121, 25488, 4341, 13398, 25435, 39402, 69677, 4919, 20899, 44291, 42042, 29021, 45668, 89601, 87045, 76169, 57651, 61381, 62631, 69431, 62163, 89979, 36681, 57170, 29939, 3624, 16973, 53615, 56748, 56932, 82331, 92435, 85530, 21528, 43162, 5866, 91837, 39968, 41586, 36714, 86698, 34467';
    $ids = explode(", ", $ids);

    foreach($ids as $id) {
        if(!update_post_meta($id, '_knife-adult-content', 1)) {
            echo $id . ', ';
        }
    }
}
