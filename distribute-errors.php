<?php

add_action('init', function() {
    if(!isset($_GET['distribute']) || !current_user_can('administrator')) {
        return;
    }

    global $wpdb;

    $tasks = [];
    $items = $wpdb->get_results("SELECT post_id, meta_value FROM wp_postmeta WHERE meta_key = '_knife-distribute-items'");

    foreach($items as $item) {
        $value = maybe_unserialize($item->meta_value);

        if(empty($value)) {
            continue;
        }

        foreach($value as $uniqid => $task) {
            $task['post_id'] = $item->post_id;
            $tasks[] = $task;
        }
    }

    echo '<style>body{font: 400 14px/1.5 sans-serif}</style>';

    echo '<h2>Distribute errors</h2>';

    foreach($tasks as $task) {
        if(empty($task['errors'])) {
            continue;
        }

        $post_id = $task['post_id'];

        foreach($task['errors'] as $network => $error) {
            printf(
                '<p><b>Post ID</b>: <a href="%s">%d</a><br><b>%s</b>: %s</p>',
                get_edit_post_link($post_id), absint($post_id),
                esc_attr($network), esc_html($error)
            );
        }
    }

    echo '<hr>';

    exit;
});
