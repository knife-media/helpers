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

@mkdir(__DIR__ . '/data');
@mkdir(__DIR__ . '/data/posters');

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

        $result['customs'] = [
            'button' => [
                'color' => $options['button_color'],
                'background' => $options['button_background'],
            ],

            'body' => [
                'color' => $options['page_color'],
                'background' => $options['page_background']
            ]
        ];

        $result['share'] = [
            'title' => strip_tags(get_the_title($post_id)),
            'description' => strip_tags(get_the_excerpt($post_id))
        ];

        $items = get_post_meta($post_id, '_knife-generator-items');

        echo $name . "\n";

        foreach ($items as $i => $item) {
            if (empty($item['poster'])) {
                $data = [
                    'id' => $i + 1,
                    'text' => $item['description']
                ];

                $result['results'][] = $data;

                continue;
            }

            preg_match('~-([a-z0-9]+?)\.(jpg|png)$~', $item['poster'], $match);

            $poster = $name . '-' . $match[1] . '.' . $match[2];

            $image = file_get_contents($item['poster']);
            file_put_contents(__DIR__ . '/data/posters/' . $poster, $image);

            $data = [
                'id' => $i + 1,
                'poster' => 'https://knife.media/frame/generator/data/posters/' . $poster,
                'text' => wpautop($item['description'])
            ];

            $result['results'][] = $data;
        }

        $json = json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);

        file_put_contents(__DIR__ . '/data/' . $name . '.json', $json);
    }
}
