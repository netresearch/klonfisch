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

$protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
$url = $protocol . '://' . $_SERVER['HTTP_HOST'];
$content = str_replace('http://klonfisch', $url, $content);

header("Content-type: application/javascript");
print $content;

?>
