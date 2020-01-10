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

    $adult_words = ['Пидор', 'Педераст', 'Гей', 'Гомосексуал', 'Гомосексуализм', 'Гомосексуалист', 'Гомосексуальность', 'Квир', 'Queer', 'ЛГБТ', 'ЛГБТК', 'Шлюх', 'Слат', 'Slut', 'Транслюди', 'Транссексуал', 'Трансмужчин', 'Трансженщин', 'Психоделик', 'Психоактивный', 'ЛСД', 'Лизергинов', 'Ибогаин', 'Галлюциноген', 'Псилоцибин', 'Гриб', 'Прекурсор', 'Наркосодержащ', 'Наркотическ', 'Опиат', 'Опиоид', 'Героин', 'Кокаин', 'Мефедрон', 'Марихуан', 'Травк', 'Каннабис', 'Кетамин', 'Хуй', 'Ебать', 'Пизд', 'Блядь'];

    $common = [];

    file_put_contents(__DIR__ . "/adult.html", '<meta charset="utf-8">');

    foreach($adult_words as $word) {
        $word = ' ' . strtolower(trim($word));
        $posts = $wpdb->get_results("SELECT id from wp_posts WHERE post_status = 'publish' AND (post_content LIKE '%{$word}%' OR post_title LIKE '%{$word}%')");

        $data = "<h3>{$word}</h3>";

        foreach($posts as $post) {
            if(in_array($post, $common)) {
                continue;
            }

            if(in_category('news', $post->id)) {
                continue;
            }

            $data = $data . sprintf('<a href="%2$s" target="_blank">%1$s</a>',
                get_the_title($post->id),
                get_permalink($post->id)
            );

            $data = $data . "<br>";

            $common[] = $post;
        }


        file_put_contents(__DIR__ . "/adult.html", $data, FILE_APPEND);
    }
}
