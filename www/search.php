<?php
declare(encoding = 'utf-8');
/**
 * Search for commits in the klonfisch database.
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
require __DIR__ . '/www-header.php';

$db = new PDO(
    $dbDsn, $dbUser, $dbPass,
    array(
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        PDO::ATTR_PERSISTENT => false
    )
);

$arResults = null;
if (isset($_REQUEST['q'])) {
    $query = $_REQUEST['q'];
    $stmt = $db->prepare(
        'SELECT * FROM commits'
        . ' JOIN keywords_commits USING (c_id)'
        . ' JOIN keywords USING (k_id)'
        . ' WHERE'
        . ' c_hash LIKE :c_hash'
        . ' OR c_author LIKE :c_author'
        . ' OR c_message LIKE :c_message'
        . ' OR k_keyword = :k_keyword'
        . ' GROUP BY c_id'
        . ' ORDER BY c_date DESC'
        . ' LIMIT 0, 20'//FIXME: dynamic limit
    );
    checkDbResult(
        $stmt,
        $stmt->execute(
            array(
                ':c_hash' => $query . '%',
                ':c_author' => '%' . $query . '%',
                ':c_message' => '%' . $query . '%',
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
require __DIR__ . '/../data/templates/search.php';

?>
