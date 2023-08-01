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

$words = ['лгбт', 'гомосексуальн', 'лесбиян', 'квир', 'секс с несовершеннолет', 'педофил', 'эфебофил', 'гебофил'];

function find_word($data, $word) {
    global $wpdb;

    $posts = $wpdb->get_results(
        $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE post_content LIKE '%{$word}%' AND post_status = 'publish' ORDER BY ID ASC" )
    );

    foreach ($posts as $post) {
        if (!has_category('longreads', $post)) {
            continue;
        }

        $key = $post->ID;

        if (empty($data[$key])) {
            $data[$key] = array();
        }

        $data[$key][] = $word;
    }

    return $data;
}


$result = array();

foreach ($words as $word) {
    $result = find_word($result, $word);
}

echo '<meta charset="utf-8">';

foreach ($result as $id => $bad) {
    $link = str_replace('knife.plus', 'knife.media', get_permalink($id));
    echo html_entity_decode(strip_tags(get_the_title($id))) . "<br><a href='{$link}'>$link</a><br>" . implode("; ", $bad) . "<br><br>";
}
