<?php
require __DIR__ . '/../data/klonfisch.config.php';
$url = $klonfischUrl . 'webhook-call.php';
$data = <<<JSON
{
    "after": "df5744f7bc8663b39717f87742dc94f52ccbf4dd",
    "before": "b4ca2d38e756695133cbd0e03d078804e1dc6610",
    "commits": [
        {
            "author": {
                "email": "jason@nospam.org",
                "name": "jason"
            },
            "committed_at": "2012-01-10T11:02:27-07:00",
            "id": "df5744f7bc8663b39717f87742dc94f52ccbf4dd",
            "message": "TEST-2, TEST-33: added a place to put the docstring for Book",
            "timestamp": "2012-01-10T11:02:27-07:00",
            "url": "http:\/\/gitorious.org\/q\/mainline\/commit\/df5744f7bc8663b39717f87742dc94f52ccbf4dd"
        }
    ],
    "project": {
        "description": "a webapp to organize your ebook collectsion.",
        "name": "q"
    },
    "pushed_at": "2012-01-10T11:09:25-07:00",
    "pushed_by": "jason",
    "ref": "new_look",
    "repository": {
        "clones": 4,
        "description": "",
        "name": "mainline",
        "owner": {
            "name": "jason"
        },
        "url": "http:\/\/gitorious.org\/q\/mainline"
    }
}
JSON;

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
