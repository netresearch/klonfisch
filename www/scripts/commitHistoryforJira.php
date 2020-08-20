<?php
/**
 * Add the correct klonfisch URL to "commitHistoryforJira.js"
 */
$filename = 'commitHistoryforJira.js';
$content = file_get_contents($filename);
if ($content === false) {
    echo 'File not found!';
    http_response_code(404);
    exit(1);
}

$protocol = 'http';

if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
    $protocol = $_SERVER['HTTPS'];
} else if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
    && !empty($_SERVER['HTTP_X_FORWARDED_PROTO'])
) {
    $protocol = $_SERVER['HTTP_X_FORWARDED_PROTO'];
}

$url = $protocol . '://' . $_SERVER['HTTP_HOST'];
$content = str_replace('http://klonfisch', $url, $content);

header("Content-type: application/javascript");
print $content;

?>
