<?php
/**
 * Accepts gitorious web hook calls and stores them
 * in the database.
 */
require __DIR__ . '/../data/klonfisch.config.php';

if (!isset($_POST['payload'])) {
    header('400 Bad request');
    echo "Payload missing\n";
    exit(1);
}

$payload = json_decode($_POST['payload']);
if ($payload === null) {
    header('400 Bad request');
    echo "Payload cannot be decoded\n";
    exit(1);
}

$db = new PDO(
    $dbDsn, $dbUser, $dbPass,
    array(
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        PDO::ATTR_PERSISTENT => true
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
    $stmt->execute(
        array(
            ':c_hash'   => $commit->id,
            ':c_author' => $commit->author->name . ' <' . $commit->author->email . '>',
            ':c_date' => $commit->committed_at,
            ':c_message' => $commit->message,
            ':c_url' => $commit->url,
            ':c_project_name' => $payload->project->name,
            ':c_repository_name' => $payload->repository->name,
            ':c_repository_url' => $payload->repository->url,
            ':c_branch' => $payload->ref
        )
    );
}

echo $count . " commits inserted\n";
?>