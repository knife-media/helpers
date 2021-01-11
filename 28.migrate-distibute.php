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

function convert_target($old) {
    if ($old === 'vk_knife') {
        return 'vk-main';
    }

    if ($old === 'fb_knife') {
        return 'facebook-main';
    }

    if ($old === 'tw_knife') {
        return 'twitter-main';
    }

    if ($old === 'tg_knife') {
        return 'telegram-main';
    }

    if ($old === 'vk_blunt') {
        return 'vk-blunt';
    }

    if ($old === 'tg_blunt') {
        return 'telegram-blunt';
    }
}

{
    global $wbdb;

    $results = $wpdb->get_results("SELECT * FROM {$wpdb->postmeta} WHERE meta_key = '_knife-distribute-items'");

    foreach ($results as $meta) {
        $value = unserialize($meta->meta_value);

        if (empty($value)) {
            continue;
        }

        $tasks = [];
        $results = [];

        foreach ($value as $key => $item) {
            if (!empty($item['excerpt']))  {
                $tasks[$key]['excerpt'] = $item['excerpt'];
            }

            if (!empty($item['attachment']))  {
                $tasks[$key]['attachment'] = $item['attachment'];

                $thumb = wp_get_attachment_image_url( $item['attachment'] );

                if ($thumb) {
                    $tasks[$key]['thumbnail'] = $thumb;
                }
            }

            if(!empty($item['collapse'])) {
                $tasks[$key]['preview'] = $item['collapse'];
            }

            if (!empty($item['targets'])) {
                foreach ($item['targets'] as $target) {
                    $tasks[$key]['targets'][] = convert_target($target);
                }
            }

            if (empty($item['sent'])) {
                continue 2;
            }

            $results[$key]['sent'] = $item['sent'];

            if (!empty($item['complete'])) {
                foreach ($item['complete'] as $i => $result) {
                    $target = convert_target($i);

                    $results[$key]['links'][$target] = $result;
                }
            }

            if (!empty($item['errors'])) {
                foreach ($item['errors'] as $i => $result) {
                    $target = convert_target($i);

                    $results[$key]['errors'][$target] = $result;
                }
            }
        }

        update_post_meta($meta->post_id, '_social_planner_tasks', $tasks);
        update_post_meta($meta->post_id, '_social_planner_results', $results);


        echo $meta->post_id . ", ";
    }
}
