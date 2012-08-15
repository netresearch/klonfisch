<?php
$url = 'http://klonfisch.cyberdyne.nr:8070/webhook-call.php';
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
            "message": "added a place to put the docstring for Book",
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
            //'ignore_errors' => true,
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => http_build_query(array('payload' => $data))
        )
    )
);
$fp = fopen($url, 'rb', false, $context);
if (!$fp) {
    $res = false;
} else {
    // If you're trying to troubleshoot problems, try uncommenting the
    // next two lines; it will show you the HTTP response headers across
    // all the redirects:
    // $meta = stream_get_meta_data($fp);
    // var_dump($meta['wrapper_data']);
    $res = stream_get_contents($fp);
}

if ($res === false) {
    throw new Exception("POST to $url failed: $php_errormsg");
}

//echo "Answer:\n";
echo $res;
?>
