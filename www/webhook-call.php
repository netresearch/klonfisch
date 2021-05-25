<?php
/**
 * Accepts gitlab web hook calls and stores them
 * in the database.
 *
 * Test it with scripts/test-webhook-post.php
 *
 * PHP version 5
 *
 * @category Tools
 * @package  Klonfisch
 * @author   Christian Weiske <christian.weiske@netresearch.de>
 * @license  AGPLv3 or later
 * @link     https://gitorious.nr/klonfisch
 */
header('HTTP/1.0 500 Internal Server Error');
require __DIR__ . '/../data/klonfisch.config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.0 400 Bad request');
    echo "POST your data, please\n";
    exit(1);
}
if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
    header('HTTP/1.0 400 Bad request');
    echo "POST content type has to be application/json\n";
    exit(1);
}

$payload = json_decode(file_get_contents('php://input'));
if ($payload === null) {
    header('HTTP/1.0 400 Bad request');
    echo "Payload cannot be decoded\n";
    exit(1);
}
$db = new PDO(
    $dbDsn, $dbUser, $dbPass,
    array(
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        PDO::ATTR_PERSISTENT => false
    )
);

$findCommit = $db->prepare(
    'SELECT c_highest_branch, c_id FROM commits'
    . ' WHERE c_hash = :hash'
    . ' AND c_repository_name = :c_repository_name'
);

$updateCommit = $db->prepare(
    'UPDATE commits SET '
    . 'c_author = :c_author, c_date =:c_date,'
    . 'c_message = :c_message, c_url = :c_url,'
    . 'c_project_name = :c_project_name, c_repository_url = :c_repository_url,'
    . 'c_repository_name = :c_repository_name,'
    . 'c_highest_branch = :c_highest_branch'
    . ' WHERE c_hash = :c_hash'
    . ' AND c_repository_name = :c_repository_name'
);

$insertCommit = $db->prepare(
    'INSERT INTO commits'
    . '(c_hash, c_author, c_date, c_message, c_url, c_project_name'
    . ', c_repository_name, c_repository_url, c_highest_branch)'
    . ' VALUES'
    . '(:c_hash, :c_author, :c_date, :c_message, :c_url, :c_project_name'
    . ', :c_repository_name, :c_repository_url, :c_highest_branch)'
);

$branch = $payload->ref;
if (substr($branch, 0, 11) == 'refs/heads/') {
    $branch = substr($branch, 11);
}
$project = '';
if (substr($payload->repository->url, 0, 4) == 'git@') {
    //"url": "git@gitlab.example.org:test\/jira-test.git"
    list($dummy, $urlPath) = explode(':', $payload->repository->url, 2);
    list($project, $dummy) = explode('/', $urlPath, 2);
}

$count = 0;
$branchPriorities = ['master', 'staging', 'showroom', 'develop'];
foreach ($payload->commits as $commit) {
    ++$count;
    unset($cId);

    $findCommit->execute(
        array(
            ':hash' => $commit->id,
            ':c_repository_name' => $payload->repository->name,
        )
    );
    $row = $findCommit->fetch(PDO::FETCH_ASSOC);
    $new = false;

    if (isset($row['c_highest_branch'])) {

        $hb = $row['c_highest_branch'];

        $updateEntry = true;

        if (in_array($hb, $branchPriorities)) {
            if (!in_array($branch, $branchPriorities)
                || array_search($hb, $branchPriorities) < array_search($branch, $branchPriorities)
            ) {
                $updateEntry = false;
            }
        }

        if ($updateEntry) {
            $cId = $row['c_id'];
            $ok = $updateCommit->execute(
                [
                    ':c_hash' => $commit->id,
                    ':c_highest_branch' => $branch,
                    ':c_author' => $commit->author->name
                    . ' <' . $commit->author->email . '>',
                    ':c_date' => date(
                        'Y-m-d H:i:s', strtotime($commit->timestamp)
                    ),
                    ':c_message' => $commit->message,
                    ':c_url' => fixUrl($commit->url),
                    ':c_project_name' => $project,
                    ':c_repository_name' => $payload->repository->name,
                    ':c_repository_url' => fixUrl(
                        $payload->repository->url
                    ),
                ]
            );

        } else {
            $ok = true;
        }
    } else {
        $new = true;
        $ok = $insertCommit->execute(
            array(
                ':c_hash' => $commit->id,
                ':c_author' => $commit->author->name
                . ' <' . $commit->author->email . '>',
                ':c_date' => date(
                    'Y-m-d H:i:s', strtotime($commit->timestamp)
                ),
                ':c_message' => $commit->message,
                ':c_url' => fixUrl($commit->url),
                ':c_project_name' => $project,
                ':c_repository_name' => $payload->repository->name,
                ':c_repository_url' => fixUrl($payload->repository->url),
                ':c_highest_branch' => $branch,
            )
        );
    }
    if (!$ok) {
        handleError($stmt);
        continue;
    }

    if ($new) {
        linkKeywordsAndCommit(
            getKeywordsFromMessage($commit->message),
            $cId ?? $db->lastInsertId(),
            $db
        );
    }
}

/**
 * Gitorious 3.0 has a bug: urls are broken when you use https.
 *
 * @param string $url URL to fix
 *
 * @return string Fixed url
 */
function fixUrl($url)
{
    //NRTECH-1589: fix broken https URLs with gitorious 3.0
    $url = str_replace('https://gitorious.nr:80/', 'https://gitorious.nr/', $url);
    return $url;
}

/**
 * Extracts keywords (project tags and issue numbers) from a commit message
 *
 * @param string $message Commit message to inspect
 *
 * @return array Array of keywords in the message
 */
function getKeywordsFromMessage($message)
{
    $num = preg_match_all(
        '#(([A-Z]{2,})-[0-9]+)#',
        $message,
        $arMatches
    );
    if ($num === 0) {
        return array();
    }

    return array_unique(array_merge($arMatches[1], $arMatches[2]));
}

/**
 * Adds keywords to the keyword table and links the new commit
 * with the keywords
 *
 * @param array   $arKeywords Array of keywords
 * @param integer $nCommitId  Database ID of the commit
 * @param PDO     $db         Database connection object
 *
 * @return void
 */
function linkKeywordsAndCommit($arKeywords, $nCommitId, $db)
{
    if (count($arKeywords) == 0) {
        return;
    }

    foreach ($arKeywords as $keyword) {
        $nKeywordId = getKeywordId($keyword, $db);
        $numAffected = $db->exec(
            'INSERT INTO keywords_commits'
            . ' SET k_id = ' . (int) $nKeywordId
            . ' , c_id = ' . (int) $nCommitId
        );
        if ($numAffected === false) {
            handleError($db);
        }
    }
}

/**
 * Returns the k_id of a keyword.
 *
 * Inserts a new record in the keyword table if it does not exist.
 *
 * @param string $keyword Keyword to get database ID for
 * @param PDO    $db      Database connection object
 *
 * @return integer|boolean Database ID of keyword, false on error
 */
function getKeywordId($keyword, $db)
{
    $stmt = $db->prepare(
        'SELECT k_id FROM keywords WHERE k_keyword = :keyword'
    );
    $ok = $stmt->execute(array(':keyword' => $keyword));
    if ($ok === false) {
        handleError($stmt);
        return false;
    }
    $arRow = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($arRow !== false) {
        return (int) $arRow['k_id'];
    }

    $stmt = $db->prepare('INSERT INTO keywords SET k_keyword = :keyword');
    $ok = $stmt->execute(array(':keyword' => $keyword));
    if ($ok === false) {
        handleError($stmt);
        return false;
    }

    return (int) $db->lastInsertId();
}

function handleError($pdoOrStmt)
{
    header('HTTP/1.0 500 SQL error');
    trigger_error(
        'SQL Error: ' . implode('; ', $pdoOrStmt->errorInfo()),
        E_USER_WARNING
    );
    exit();
}

header('HTTP/1.0 200 OK');
echo $count . " commits inserted\n";
