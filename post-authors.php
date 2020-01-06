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
    if(get_role('club_user')) {
        remove_role('club_user');
    }

    $posts = get_posts([
        'posts_per_page' => -1,
        'post_type' => 'any',
        'fields' => ['ids', 'post_author']
    ]);

    foreach($posts as $post) {
        $coauthors = get_coauthors($post->ID);
        $authors = get_post_meta($post->ID, '_knife-authors');

        echo $post->ID . ", ";

        if(count($coauthors) === 0 && empty($authors)) {
            add_post_meta($post->ID, '_knife-authors', $post->post_author);
            continue;
        }

        foreach($coauthors as $coauthor) {
            $login = $coauthor->user_login;

            if ($login === 'agata_k') {
                $get_user = get_user_by('login', 'agata');
            } elseif ($login === 'anna') {
                $get_user = get_user_by('login', 'news');
            } else {
                $get_user = get_user_by('login', $login);
            }


            if($get_user === false) {
                $first = get_post_meta($coauthor->ID, 'cap-first_name', true);
                $last = get_post_meta($coauthor->ID, 'cap-last_name', true);
                $login = get_post_meta($coauthor->ID, 'cap-user_login', true);

                $ff = explode(' ', $first);
                $first = $ff[0];

                $ll = explode(' ', $last);
                $last = $ll[0];

                $args = [
                    'user_login' => $login,
                    'user_nicename' => $login,
                    'display_name' => $first . ' ' . $last,
                    'first_name' => $first,
                    'last_name' => $last,
                    'role' => 'subscriber',
                    'user_pass' => wp_generate_password()
                ];

                $user_id = wp_insert_user($args);

                if(is_wp_error($user_id)) {
                    echo $user_id->get_error_message();
                    exit;
                }
            } else {
                $user_id = $get_user->ID;
            }

            $curr = get_post_meta($post->ID, '_knife-authors');

            if(!in_array($user_id, $curr)) {
                add_post_meta($post->ID, '_knife-authors', $user_id);
            }
        }
    }


    $users = get_posts([
        'posts_per_page' => -1,
        'post_type' => 'guest-author',
    ]);

    foreach($users as $user) {
        echo "Remove user: $user->ID, ";

        $term = get_term_by('slug', 'cap-' . $user->post_name, 'author');
        if ( ! $term ) {
            $term = get_term_by( 'slug', $user->post_name, 'author');
        }

        if($term) {
            wp_delete_term( $term->term_id, 'author' );
        }

        wp_delete_post( $user->ID, true );
    }

    $terms = get_terms([
        'taxonomy' => 'author',
        'hide_empty' => false,
    ]);

    foreach($terms as $term) {
        echo "Remove term: $term->term_id, ";
        wp_delete_term( $term->term_id, 'author' );
    }

}
