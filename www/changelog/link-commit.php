<?php
/**
 * Redirects a Fisheye commit link to Gitorious
 */
require_once __DIR__ . '/../www-header.php';
$db = new PDO(
    $dbDsn, $dbUser, $dbPass,
    array(
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        PDO::ATTR_PERSISTENT => true
    )
);

if (!isset($_GET['cs']) || $_GET['cs'] == '') {
    header('HTTP/1.0 400 Bad Request');
    echo "Commit hash parameter \"cs\" is missing\n";
    exit(1);
}

//Commit hash link
// /changelog/test?cs=985f39f6d857ac2e1e4e1cab3d235767
$hash = $_GET['cs'];
if (strlen($hash) !== 40 && strlen($hash) !== 32) {
    header('HTTP/1.0 400 Bad Request');
    echo "Length of commit hash is not 40 nor 32\n";
    exit(1);
}

$stmt = $db->prepare(
    'SELECT c_url FROM commits'
    . ' WHERE c_hash = :hash'
);
checkDbResult($stmt, $stmt->execute(array(':hash' => $hash)));
$arRow = $stmt->fetch(PDO::FETCH_ASSOC);
if ($arRow === false) {
    header('HTTP/1.0 404 Not Found');
    echo "Commit not found\n";
    exit(1);
}
header('Location: ' . $arRow['c_url']);
exit(0);

?>