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

require_once(__DIR__ . '/../wordpress/wp-load.php');

$query = new WP_Query([
    'post_type' => ['post', 'story', 'club', 'select'],
    'posts_per_page' => -1
]);

echo '<?xml version="1.0" encoding="' . get_option('blog_charset') . '"?' . '>';
?>

<rss version="2.0">
    <channel>
        <title><?php bloginfo_rss('name'); ?></title>
        <link><?php bloginfo_rss('url'); ?></link>
        <description><?php bloginfo_rss('description'); ?></description>
        <language><?php bloginfo_rss('language'); ?></language>

        <?php while($query->have_posts()) : $query->the_post(); ?>
            <item>
                <link><?php the_permalink_rss(); ?></link>
                <title><?php the_title_rss(); ?></title>
                <pubDate><?php echo mysql2date( 'D, d M Y H:i:s +0000', get_post_time( 'Y-m-d H:i:s', true ), false ); ?></pubDate>
                <dc:creator><![CDATA[<?php coauthors(null, null, null, null, false); ?>]]></dc:creator>
                <guid isPermaLink="false"><?php the_guid(); ?></guid>
                <?php
                    if($categories = wp_get_post_categories(get_the_ID(), ['fields' => 'names'])) {
                        foreach($categories as $category) {
                            printf("<category><![CDATA[%s]]></category>\n", $category);
                        }
                    }

                    if($tags = get_the_tags()) {
                        foreach($tags as $tag) {
                            printf("<tag><![CDATA[%s]]></tag>\n", $tag->name);
                        }
                    }

                    printf("<type>%s</type>\n", get_post_type() ?? 'post');

                    if(has_post_format()) {
                        printf("<format>%s</format>\n", get_post_format());
                    }

                    if(has_post_thumbnail()) {
                        printf("<thumbnail>%s</thumbnail>\n", get_the_post_thumbnail_url(get_the_ID(), 'full'));
                    }

                    if($lead = get_post_meta($post->ID, 'lead-text', true)) {
                        printf("<lead><![CDATA[%s]]></lead>\n", strip_tags($lead));
                    }
                ?>

                <description><![CDATA[<?php the_excerpt_rss(); ?>]]></description>
                <content:encoded><![CDATA[<?php the_content_feed(); ?>]]></content:encoded>
            </item>
        <?php endwhile; ?>
    </channel>
</rss>
