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

$links = array(
    '/tom-of-finland/',
    '/gay-church/',
    '/queer-literature/',
    '/queer-kirkorov/',
    '/queer-museum/',
    '/deaf-gay/',
    '/women-sex-art/',
    '/musical-penetration/',
    '/gender-neutral/',
    '/trans-resistance/',
    '/bi-curiosity/',
    '/feminist-body/',
    '/real-sex-movies/',
    '/petushnya/',
    '/russian-queers/',
    '/homophobia-from-europe/',
    '/partners-of-nonbiners/',
    '/non-binary/',
    '/ussr-lesbians/',
    '/tosi-bosi/',
    '/queer-dance/',
    '/fantasy-sex/',
    '/kirey-sitnikova/',
    '/conversion-ban/',
    '/feminist-surrogacy/',
    '/castro-tolstoy/',
    '/coming-out-writers/',
    '/gay-vs-hetero/',
    '/lgbt-in-sci-fi/',
    '/christian-sex/',
    '/ftm/',
    '/queer-classics/',
    '/queer-music/',
    '/homosexuals-of-xix-century/',
    '/soviet-homoeroticism/',
    '/london-guide/',
    '/drag-culture/',
    '/male-sex-work/',
    '/study-your-mojo/',
    '/trans-theater/',
    '/bysantine-sex/',
    '/because-i-want-you/',
    '/homosexual-rituals/',
    '/tiergeschenke/',
    '/ritual-sex-2/',
    '/male-sexuality/',
    '/paraphilia/',
    '/early-soviet-drag/',
    '/greek-gay-language/',
    '/lesbian-writing/',
    '/vakasyudo/',
    '/trans-weimar/',
    '/butch/',
    '/underappreciated-lgbt-movies/',
    '/feminism-on-drugs/',
);

foreach ($links as $link) {
    $post_id = url_to_postid(site_url($link));

    if (empty($post_id)) {
        echo "+ empty post id {$post_id}\n";
    }

    if (update_post_meta($post_id, '_knife-horror-content', 1)) {
        echo site_url($link) . "\n";
    }
}

