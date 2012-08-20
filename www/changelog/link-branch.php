<?php
/**
 * Redirects a Fisheye branch link to Gitorious
 *
 * URL style: /changelog/~br=master in project/repository/test
 *
 * Our changesets have branch names like "master in project/repository"
 * because otherwise we do have no information about which repository
 * shall be shown.
 */
require_once __DIR__ . '/../www-header.php';

if (!isset($_GET['br']) || $_GET['br'] == '') {
    header('HTTP/1.0 400 Bad Request');
    echo "Branch identifier is missing\n";
    exit(1);
}

list($branch, $rest) = explode(' in ', $_GET['br']);
list($project, $repo, $testrepo) = explode('/', $rest);

$url = $gitoriousUrl . $project . '/' . $repo . '/commits/' . $branch;
header('Location: ' . $url);
?>
