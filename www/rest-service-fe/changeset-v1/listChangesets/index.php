<?php
//declare(encoding = 'utf-8');
/**
 * Lists commits for a given keyword.
 * Used by the Jira issue and project "Source" tab.
 *
 * PHP version 5
 *
 * @category Tools
 * @package  Klonfisch
 * @author   Christian Weiske <christian.weiske@netresearch.de>
 * @license  AGPLv3 or later
 * @link     https://gitorious.nr/klonfisch
 */
require_once __DIR__ . '/../../../../data/klonfisch.config.php';

if (!isset($_GET['expand'])) {
    header('HTTP/1.0 400 Bad Request');
    echo "expand GET parameter missing\n";
    exit(1);
}
if (!isset($_GET['comment'])) {
    header('HTTP/1.0 400 Bad Request');
    echo "comment GET parameter missing\n";
    exit(1);
}
if (!isset($_GET['rep'])) {
    header('HTTP/1.0 400 Bad Request');
    echo "rep GET parameter missing\n";
    exit(1);
}

$keyword = $_GET['comment'];
if ($keyword == '') {
    header('HTTP/1.0 400 Bad Request');
    echo "comment is empty\n";
    exit(1);
}

$orderByChangesets = ' ORDER BY c_date DESC';
$limitChangesets   = '';

$bListRevisions = false;
$nRevisionsFrom = 0;
$nRevisionsTo   = 10;

if ($_GET['expand'] != '') {
    $arGetParts = explode('.', $_GET['expand']);
    $pattern = '#^([a-z]+)\[([-0-9]+):([-0-9]+)\]$#';
    foreach ($arGetParts as $getPart) {
        if (!preg_match($pattern, $getPart, $arMatches)) {
            header('HTTP/1.0 400 Bad Request');
            echo "expand parameter is invalid\n";
            exit(1);
        }
        list(, $type, $start, $end) = $arMatches;
        if ($type == 'changesets') {
            if ($start < 0) {
                //changesets[-21:-1] -> show 20 oldest changesets
                $orderByChangesets = ' ORDER BY c_date ASC';
                $num = abs($start) - abs($end) + 1;
                $start = abs($end) - 1;
            } else {
                //changeset[0:20] -> show 20 newest changesets
                $orderByChangesets = ' ORDER BY c_date DESC';
                $num = $end - $start + 1;
            }
            $limitChangesets = ' LIMIT ' . (int)$start . ', ' . (int)$num;
        } else if ($type == 'revisions') {
            $bListRevisions = true;
            $nRevisionsFrom = (int)$start;
            $nRevisionsTo   = (int)$end;
        }
    }
}

$db = new PDO(
    $dbDsn, $dbUser, $dbPass,
    array(
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        PDO::ATTR_PERSISTENT => true
    )
);
$stmt = $db->prepare(
    'SELECT commits.* FROM commits'
    . ' JOIN keywords_commits USING (c_id)'
    . ' JOIN keywords USING (k_id)'
    . ' WHERE k_keyword = :keyword'
    . $orderByChangesets
    . $limitChangesets
);

$ok = $stmt->execute(array(':keyword' => $keyword));
if ($ok === false) {
    header('HTTP/1.0 500 Internal server error');
    echo "SQL error\n";
    echo implode(' / ', $stmt->errorInfo()) . "\n";
    exit(1);
}
header('Content-Type: application/xml');

$xml = new XMLWriter();
$xml->openMemory();
$xml->setIndent(true);
$xml->setIndentString(' ');
$xml->startDocument('1.0', 'UTF-8', 'yes');
$xml->startElement('results');
$xml->writeAttribute('expand', 'changesets');

$xml->startElement('changesets');
while ($arRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $xml->startElement('changeset');

    $xml->writeElement('csid',    $arRow['c_hash']);
    $xml->writeElement('date',    date('c', strtotime($arRow['c_date'])));
    $xml->writeElement('author',  $arRow['c_author']);
    $xml->writeElement('branch',  $arRow['c_branch']);
    $xml->writeElement('comment', $arRow['c_message']);

    $xml->startElement('revisions');
    $xml->writeAttribute('size', 0);
    //FIXME: implement revisions (changes files) - JGA-8
    $xml->endElement();//revisions

    $xml->endElement();//changeset
}
$xml->endElement();//changesets

$xml->endElement();//results
$xml->endDocument();

echo $xml->outputMemory();
?>