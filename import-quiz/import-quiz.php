<?php

if(php_sapi_name() !== 'cli')
    exit;

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

require( __DIR__ . '/../../wordpress/wp-load.php');


$quiz_id = 13;

function get_attachment_id_by_url( $url ) {
    $post_id = attachment_url_to_postid( $url );

    if ( ! $post_id ){
        $dir = wp_upload_dir();
        $path = $url;
        if ( 0 === strpos( $path, $dir['baseurl'] . '/' ) ) {
            $path = substr( $path, strlen( $dir['baseurl'] . '/' ) );
        }

        if ( preg_match( '/^(.*)(\-\d*x\d*)(\.\w{1,})/i', $path, $matches ) ){
            $url = $matches[1] . $matches[3];
            $post_id = attachment_url_to_postid( $url );
        }
    }

    return (int) $post_id;
}

{
    global $wbdb;

    $row = $wpdb->get_row("SELECT id FROM {$wpdb->posts} WHERE post_content LIKE '%[WpProQuiz {$quiz_id}]%' AND post_type = 'post' AND post_status = 'publish' LIMIT 1");

    $post_id = $row->id;

    // get old post
    $post = get_post($post_id);

    $args = array(
        'comment_status' => $post->comment_status,
        'post_date'      => $post->post_date,
        'ping_status'    => $post->ping_status,
        'post_author'    => $post->post_author,
        'post_content'   => $post->post_content,
        'post_excerpt'   => $post->post_excerpt,
        'post_name'      => $post->post_name,
        'post_parent'    => $post->post_parent,
        'post_password'  => $post->post_password,
        'post_type'      => 'quiz',
        'post_status'    => 'private',
        'post_title'     => $post->post_title,
        'to_ping'        => $post->to_ping,
        'menu_order'     => $post->menu_order
    );

    $new_post_id = wp_insert_post( $args );

    $taxonomies = get_object_taxonomies($post->post_type);

    foreach ($taxonomies as $taxonomy) {
        $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
        wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
    }

    $post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id={$post_id}");

    if(count($post_meta_infos) > 0) {
        $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";

        foreach ($post_meta_infos as $meta_info) {
            $meta_key = $meta_info->meta_key;

            if( $meta_key == '_wp_old_slug' ) {
                continue;
            }

            $meta_value = addslashes($meta_info->meta_value);

            $sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
        }

        $sql_query .= implode(" UNION ALL ", $sql_query_sel);

        $wpdb->query($sql_query);
    }

    echo $new_post_id . "\n";

    $options = [
        'details' => 'result',
        'shuffle' => 1
    ];


    $questions = $wpdb->get_results("SELECT * FROM wp_wp_pro_quiz_question WHERE quiz_id = '{$quiz_id}'");

    $scores = 0;

    foreach($questions as $i => $question) {
        if($question->points != 1) {
            $options['format'] = 'points';
        } else {
            $options['format'] = 'binary';
        }

        $new_question = [
            'question' => $question->question
        ];

        $answers = unserialize($question->answer_data);

        $max_score = 0;

        foreach($answers as $i => $a) {
            $reflection = new ReflectionClass('WpProQuiz_Model_AnswerTypes');
            $answer = $reflection->getProperty('_answer');
            $answer->setAccessible(true);

            $new_answer = [
                'choice' => $answer->getValue($a),
            ];

            if($question->points == 1) {
                $correct = $reflection->getProperty('_correct');
                $correct->setAccessible(true);

                $points = $reflection->getProperty('_points');
                $points->setAccessible(true);

                $after = $reflection->getProperty('_message');
                $after->setAccessible(true);

                $max_score  = 1;

                if($after->getValue($a)) {
                    $new_answer['message'] = $after->getValue($a);
                } else {
                    $correct = $reflection->getProperty('_correct');
                    $correct->setAccessible(true);

                    if($correct->getValue($a)) {
                        $new_answer['message'] = $question->correct_msg;
                    } elseif (!empty($question->incorrect_msg)) {
                        $new_answer['message'] = $question->incorrect_msg;
                    } else {
                        $new_answer['message'] = $question->correct_msg;
                    }
                }

                if($correct->getValue($a) || $points->getValue($a)) {
                    $new_answer['binary'] = 1;
                }
            } else {
                $after = $reflection->getProperty('_message');
                $after->setAccessible(true);

                if($after->getValue($a)) {
                    $new_answer['message'] = $after->getValue($a);
                } else {
                    $new_answer['message'] = $question->correct_msg;
                }

                $points = $reflection->getProperty('_points');
                $points->setAccessible(true);

                $max_score = max($max_score, $points->getValue($a));

                $new_answer['points'] = $points->getValue($a);
            }

            $new_answer['choice'] = mb_strtoupper(mb_substr($new_answer['choice'], 0, 1)) . mb_substr($new_answer['choice'], 1);

            if(!empty($new_answer['message'])) {
                $options['message'] = 1;
            }

            $new_question['answers'][] = $new_answer;
        }

        $scores = $scores + $max_score;

        if(!add_post_meta($new_post_id, '_knife-quiz-items', $new_question)) {
            echo "Question not added\n";
        }
    }


    $results = $wpdb->get_row("SELECT `text`, `result_text` FROM wp_wp_pro_quiz_master WHERE id = '{$quiz_id}'");

    if(!empty($results->text)) {
        $add_lead = update_post_meta($new_post_id, '_knife-lead', $results->text, true);

        if($add_lead === false) {
            echo "Lead not added: $new_post_id\n";
        }
    }

    $results = unserialize($results->result_text);

    $points = [];
    foreach($results['prozent'] as $i => $prozent) {
        $points[$i] = floor(($prozent/100) * $scores);
    }

    $texts = $results['text'];

    if($points[0] > $points[1]) {
        $texts = array_reverse($texts);

        sort($points);
    }

    $points[] = $scores;

    foreach($texts as $i => $text) {
        $result = [
            'details' => $text,
            'achievment' => '',
            'heading' => '',
            'description' => '',
            'attachment' => '',
            'from' => $points[$i],
            'to' => $points[$i + 1] - 1
        ];

        if($i + 1 === count($texts)) {
            $result['to'] = $points[$i + 1];
        }

        preg_match('~src="([^"]+)~is', $text, $matches);
        if(!empty($matches[1])) {
#           $matches[1] = str_replace('//knife.media', '//knife.plus', $matches[1]);
            $attachment = get_attachment_id_by_url($matches[1]);

            if($attachment > 0) {
                $result['attachment'] = $attachment;
            }
        }

        preg_match('~<h4>(.+?)</h4>~is', $text, $matches);
        if(!empty($matches[1])) {
            $result['heading'] = strip_tags($matches[1]);
        }

        preg_match('~<p>(.+?)</p>~is', wpautop($text), $matches);
        if(!empty($matches[1])) {
            $result['description'] = strip_tags($matches[1]);
        }

        if(!add_post_meta($new_post_id, '_knife-quiz-results', $result)) {
            echo "Result not added: {$result['from']}\n";
        }
    }

    if(!add_post_meta($new_post_id, '_knife-quiz-options', $options, true)) {
        echo "Options not added\n";
    }
}
