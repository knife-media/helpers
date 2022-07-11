<?php

if(php_sapi_name() !== 'cli') {
    exit;
}

$json = json_decode(file_get_contents(__DIR__ . '/old.json'), false);
$results = $json->results;

foreach ($results as &$result) {
    if (!preg_match('~<p>https.+?youtube.+?v=([a-z0-9_-]+)\s*</p>~is', $result->text, $match)) {
        continue;
    }

    $iframe = '<iframe width="100%" height="auto" src="https://www.youtube.com/embed/' . $match[1] . '?controls=0" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
    $result->text = str_replace($match[0], $iframe, $result->text);
}

$json->results = $results;
$json = json_encode($json, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);


file_put_contents(__DIR__ . '/new.json', $json);
