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

require(__DIR__ . '/../wordpress/wp-load.php');

{
    $posts = get_posts([
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'post_type' => ['generator'],
        'orderby' => 'id',
        'fields' => 'ids'
    ]);

    foreach ($posts as $c => $post_id) {
        $result = [];

        $post = get_post($post_id);
        $name = $post->post_name;

        $options = get_post_meta($post_id, '_knife-generator-options', true);

        $content = '<figure class="figure figure--frame"><a class="button" href="https://knife.plus/frame/generator/' . $name . '/" rel="noopener" data-id="frame-' . $name . '" data-loading="Подождите…" target="_blank">'  . $options['button_text'] . '</a></figure>';

        $new_id = wp_insert_post([
            'post_title' => sanitize_text_field($post->post_title),
            'post_content' => $content,
            'post_status' => 'publish',
            'post_author' => $post->post_author,
            'post_date' => $post->post_date,
            'post_excerpt' => $post->post_excerpt,
            'post_name' => $post->post_name,
            'post_category' => [2053],
        ]);

        foreach (get_the_tags($post_id) as $tag) {
            wp_set_post_terms($new_id, [$tag->term_id], 'post_tag');
        }

        update_post_meta($new_id, '_wp_page_template', 'templates/single-wide.php');

        if (get_post_meta($post_id, '_social-image-options', true)) {
            update_post_meta($new_id, '_social-image-options', get_post_meta($post_id, '_social-image-options', true));
        }

        if (get_post_meta($post_id, '_knife-promo-options', true)) {
            update_post_meta($new_id, '_knife-promo-options', get_post_meta($post_id, '_knife-promo-options', true));
        }

        if (get_post_meta($post_id, '_knife-tagline', true)) {
            update_post_meta($new_id, '_knife-tagline', get_post_meta($post_id, '_knife-tagline', true));
        }

        if (get_post_meta($post_id, '_knife-lead', true)) {
            update_post_meta($new_id, '_knife-lead', get_post_meta($post_id, '_knife-lead', true));
        }

        if (get_post_meta($post_id, '_knife-promo', true)) {
            update_post_meta($new_id, '_knife-promo', get_post_meta($post_id, '_knife-promo', true));
        }

        if (get_post_meta($post_id, '_knife-primary-tag', true)) {
            update_post_meta($new_id, '_knife-primary-tag', get_post_meta($post_id, '_knife-primary-tag', true));
        }

        if (get_post_meta($post_id, '_knife_similar', true)) {
            update_post_meta($new_id, '_knife_similar', get_post_meta($post_id, '_knife_similar', true));
        }

        if (get_post_meta($post_id, '_knife-background', true)) {
            update_post_meta($new_id, '_knife-background', get_post_meta($post_id, '_knife-background', true));
        }

        if (get_post_meta($post_id, '_thumbnail_id', true)) {
            update_post_meta($new_id, '_thumbnail_id', get_post_meta($post_id, '_thumbnail_id', true));
        }

        if (get_post_meta($post_id, '_social-image', true)) {
            update_post_meta($new_id, '_social-image', get_post_meta($post_id, '_social-image', true));
        }

        if (get_post_meta($post_id, '_social_planner_tasks', true)) {
            update_post_meta($new_id, '_social_planner_tasks', get_post_meta($post_id, '_social_planner_tasks', true));
        }

        if (get_post_meta($post_id, '_social_planner_results', true)) {
            update_post_meta($new_id, '_social_planner_results', get_post_meta($post_id, '_social_planner_results', true));
        }

        if (get_post_meta($post_id, '_knife-authors')) {
            foreach (get_post_meta($post_id, '_knife-authors') as $author) {
                update_post_meta($new_id, '_knife-authors', $author);
            }
        }

        echo $new_id . ' ' . $name . "\n";
    }
}
