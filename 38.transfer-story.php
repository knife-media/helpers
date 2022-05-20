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


function create_image($id, $caption = '') {
    $html = get_image_tag($id, '', '', 'left', 'inner');

    if($caption) {
        $html = $html . '<figcaption class="figure__caption">' . $caption . '</figcaption>';
    }

    $html = '<figure class="figure figure--inner">' . $html . '</figure>';
    return $html;
}

{
    wp_insert_term('фотоистория', 'post_tag', [
        'parent'      => 0,
        'slug'        => 'photo-story',
    ]);

    $posts = get_posts([
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'post_type' => ['story'],
        'orderby' => 'id',
        'fields' => 'ids'
    ]);

    foreach ($posts as $c => $post_id) {
        $content = '';

        $items = get_post_meta($post_id, '_knife-story-items');
        $post = get_post($post_id);

        foreach ($items as $i => $item) {
            if (!empty($item['entry'])) {
                $item['entry'] = str_replace('<p>&nbsp;</p>', '', $item['entry']);
                $item['entry'] = str_replace('<p> </p>', '', $item['entry']);

                $item['entry'] = preg_replace('~<h4>(.+?)</h4>~s', '<p>$1</p>', $item['entry']);
            }

            if ($i < 1 && !empty($item['entry'])) {
                $content = $content . $item['entry'];
                continue;
            }

            if (!empty($item['media']) && !empty($item['entry'])) {
                $item['entry'] = preg_replace('~<strong>(.+?)</strong>~s', '$1', $item['entry']);
                $content = $content . create_image($item['media'], $item['entry']);
                continue;
            }

            if (!empty($item['media'])) {
                $content = $content . create_image($item['media']);
                continue;
            }

            if (!empty($item['entry'])) {
                $content = $content . $item['entry'];
            }
        }

        $thumbnail = get_post_thumbnail_id($post_id);

        $new_id = wp_insert_post([
            'post_title' => sanitize_text_field($post->post_title),
            'post_content' => $content,
            'post_status' => 'publish',
            'post_author' => $post->post_author,
            'post_date' => $post->post_date,
            'post_excerpt' => $post->post_excerpt,
            'post_name' => 'story-' . $post->post_name,
            'post_category' => [620],
        ]);

        update_post_meta($new_id, '_knife-authors', $post->post_author);
        update_post_meta($new_id, '_knife_similar_hide', 1);

        wp_set_post_tags($new_id, ['photo-story', 'stories']);
        set_post_thumbnail($new_id, $thumbnail);

        Knife_Similar_Posts::generate_similar($new_id);

        echo get_permalink($new_id) . "\n";
    }
}
