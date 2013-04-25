<?php
declare(encoding = 'utf-8');
/**
 * Accepts gitorious web hook calls and stores them
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

if (!isset($_POST['payload'])) {
    header('HTTP/1.0 400 Bad request');
    echo "Payload missing\n";
    exit(1);
}

$payload = json_decode($_POST['payload']);
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
$stmt = $db->prepare(
    'INSERT INTO commits'
    . '(c_hash, c_author, c_date, c_message, c_url, c_project_name'
    . ', c_repository_name, c_repository_url, c_branch)'
    . ' VALUES'
    . '(:c_hash, :c_author, :c_date, :c_message, :c_url, :c_project_name'
    . ', :c_repository_name, :c_repository_url, :c_branch)'
);

$count = 0;
foreach ($payload->commits as $commit) {
    ++$count;
    
    if (preg_match('/^(\d{4})\-(\d{2})\-(\d{2})T(\d{2}):(\d{2}):(\d{2})\+(\d{2}):(\d{2})$/', $useDate, $matches)) {
        $useDate = $matches[1] . "-" . $matches[2] . "-" . $matches[3] . " " . $matches[4] . ":" . $matches[5] . ":" . $matches[6];
    }

    $ok = $stmt->execute(
        array(
            ':c_hash'    => $commit->id,
            ':c_author'  => $commit->author->name
            . ' <' . $commit->author->email . '>',
            ':c_date'    => $useDate,
            ':c_message' => $commit->message,
            ':c_url'     => $commit->url,
            ':c_project_name'    => $payload->project->name,
            ':c_repository_name' => $payload->repository->name,
            ':c_repository_url'  => $payload->repository->url,
            ':c_branch'  => $payload->ref
        )
    );

    if (!$ok) {
        handleError($stmt);
        continue;
    }

    linkKeywordsAndCommit(
        getKeywordsFromMessage($commit->message),
        $db->lastInsertId(),
        $db
    );
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
?>
