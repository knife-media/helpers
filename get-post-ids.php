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
    $list = file_get_contents(__DIR__ . '/data/list.txt');

    // Get xids
    $xids = explode("\n", $list);

    // Updated list
    $data = [];

    foreach ($xids as $xid) {
        $uid = str_replace('https://knife.media', 'https://knife.plus', $xid);

        // Get pist id
        $post_id = url_to_postid($uid);

        if (empty($post_id)) {
            $uid = str_replace('https://knife.plus', 'https://knife.plus/quiz', $uid);

            // Let's do it once again
            $post_id = url_to_postid($uid);

            if (empty($post_id)) {
                continue;
            }
        }

        $xid = str_replace('https://', '', $xid);

        $data[] = "$post_id:$xid";
    }

    file_put_contents(__DIR__ . '/data/result.txt', implode("\n", $data));
}
