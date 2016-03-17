#!/usr/bin/env php
<?php
require __DIR__ . '/../data/klonfisch.config.php';
$url = $klonfischUrl . 'webhook-call.php';
$data = file_get_contents(__DIR__ . '/../data/examples/gitlab-commit-post.json');

$context = stream_context_create(
    array(
        'http' => array(
            'method' => 'POST',
            'ignore_errors' => true,
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => http_build_query(array('payload' => $data))
        )
    )
);
echo 'Sending request to ' . $url . "\n";
$fp = fopen($url, 'rb', false, $context);
if (!$fp) {
    echo "Error opening URL\n";
    exit(1);
}

$res = stream_get_contents($fp);
$meta = stream_get_meta_data($fp);
if (false === preg_match('#^HTTP./. 2..#', $meta['wrapper_data'][0])) {
    echo "POST to $url failed:\n";
    echo $meta['wrapper_data'][0] . "\n";
    echo $res;
    exit(2);
}
//echo "Answer:\n";
echo $res . "\n";
?>
