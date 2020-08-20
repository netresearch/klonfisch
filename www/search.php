<?php
/**
 * Search for all commits from an ticket in the klonfisch database.
 *
 */
header('HTTP/1.0 500 Internal Server Error');
require __DIR__ . '/www-header.php';

$db = new PDO(
    $dbDsn, $dbUser, $dbPass,
    array(
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        PDO::ATTR_PERSISTENT => false,
    )
);

$arResults = null;
if (isset($_REQUEST['q'])) {
    $query = trim($_REQUEST['q']);
    $stmt = $db->prepare(
        'SELECT * FROM commits'
        . ' JOIN keywords_commits USING (c_id)'
        . ' JOIN keywords USING (k_id)'
        . ' WHERE k_keyword = :k_keyword'
        . ' GROUP BY c_id'
        . ' ORDER BY c_date DESC'
    );
    checkDbResult(
        $stmt,
        $stmt->execute(
            array(
                ':k_keyword' => $query,
            )
        )
    );

    $arResults = array();
    while ($arRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $arResults[] = $arRow;
    }
}


header('HTTP/1.0 200 OK');
header("Access-Control-Allow-Origin: * ");

require '../data/templates/search.phtml';
