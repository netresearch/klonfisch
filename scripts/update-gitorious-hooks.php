#!/usr/bin/env php
<?php
/**
 * Registers or removes the Klonfisch Gitorious web hook
 * for every single repository in the gitorious database.
 *
 * PHP version 5
 *
 * @author Christian Weiske <christian.weiske@netresearch.de>
 */
require_once __DIR__ . '/../www/www-header.php';

$bCreateNew      = false;
$bRemoveExisting = false;
$logLevel = 1;

array_shift($argv);
$files = array();
foreach ($argv as $arg) {
    if ($arg == '-v' || $arg == '--verbose') {
        ++$logLevel;
    } else if ($arg == '-q' || $arg == '--quiet') {
        $logLevel = 0;
    } else if ($arg == '-c' || $arg == '--create') {
        $bCreateNew = true;
    } else if ($arg == '-r' || $arg == '--remove') {
        $bRemoveExisting = true;
    } else if ($arg == '-h' || $arg == '--help') {
        showHelp();
        exit(4);
    } else if ($arg == '--version') {
        echo "klonfisch update-gitorious-hooks 0.0.1\n";
        exit();
    } else {
        klerr('Unknown argument: ' . $arg);
        exit(3);
    }
}

foreach (array('klonfischUrl', 'gitoriousDbDsn', 'gitoriousDbUser') as $var) {
    if (!isset($$var) || $$var == '') {
        klerr('Error: ' . $var . ' not set');
    }
}

if ($bRemoveExisting) {
    kllog('Will remove existing hooks');
} else {
    kllog('Will NOT remove existing hooks');
}
if ($bCreateNew) {
    kllog('Will create new hooks');
} else {
    kllog ('Will NOT create new hooks');
}


$hookUrl = $klonfischUrl . 'webhook-call.php';

$db = new PDO(
    $gitoriousDbDsn, $gitoriousDbUser, $gitoriousDbPass,
    array(
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        PDO::ATTR_PERSISTENT => false
    )
);

//fetch user id
$stmt = $db->prepare(
    'SELECT id FROM users WHERE login = :login LIMIT 1'
);
$stmt->execute(array(':login' => $gitoriousDbHookUser));
$arUser = $stmt->fetch(PDO::FETCH_ASSOC);
if ($arUser === false) {
    klerr('User not found in gitorious user table');
}
$nUserId = $arUser['id'];
kllog(
    'Gitorious user ID for ' . $gitoriousDbHookUser . ' is #' . $nUserId,
    2
);

//fetch all repositories and their webhook-call hooks
$stmt = $db->prepare(
    'SELECT'
    . '  r.id AS r_id, r.name AS r_name'
    . ', p.slug as p_slug'
    . ', h.id AS h_id, h.data AS h_url'
    . ' FROM repositories r'
    . ' JOIN projects p ON r.project_id = p.id'
    . ' LEFT JOIN services h ON r.id = h.repository_id AND h.service_type = "web_hook" AND h.data LIKE :url'
);
$bOk = $stmt->execute(array(':url' => "%\n:url: " . $hookUrl . "\n%"));
checkDbResult($stmt, $bOk);


//remove or register hooks
$nCount = $nExisting = $nMissing = $nCreated = $nDeleted = 0;
while ($arRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
    ++$nCount;

    kllog(
        $arRow['p_slug'] . '/' . $arRow['r_name']
        . ' has ' . ($arRow['h_url'] === null ? 'no': 'a') . ' hook',
        2
    );
    if ($arRow['h_url'] === null) {
        ++$nMissing;
    } else {
        ++$nExisting;
    }

    if ($arRow['h_id'] !== null && $bRemoveExisting) {
        //existing hook
        kllog(
            'Removing existing hook from '
            . $arRow['p_slug'] . '/' . $arRow['r_name']
            . ': ' . $arRow['h_url']
        );
        $stmtDel = $db->prepare('DELETE FROM services WHERE id = :id');
        $bOk = $stmtDel->execute(array(':id' => $arRow['h_id']));
        checkDbResult($stmtDel, $bOk);
        $arRow['h_id']  = null;
        $arRow['h_url'] = null;
        ++$nDeleted;
    }

    if (!$bCreateNew) {
        continue;
    }
    if ($arRow['h_url'] != $hookUrl) {
        kllog(
            'Creating hook for '
            . $arRow['p_slug'] . '/' . $arRow['r_name']
            . ': ' . $hookUrl
        );
        $stmtIns = $db->prepare(
            'INSERT INTO services'
            . ' SET user_id = :user_id'
            . ', repository_id = :repository_id'
            . ', service_type = "web_hook"'
            . ', data = :url'
            . ', created_at = NOW(), updated_at = NOW()'
        );
        $bOk = $stmtIns->execute(
            array(
                ':user_id' => $nUserId,
                ':repository_id' => $arRow['r_id'],
                ':url' => "---\n"
                . ':url: ' . $hookUrl . "\n"
            )
        );
        checkDbResult($stmtIns, $bOk);
        ++$nCreated;
    }
}

kllog('Checked ' . $nCount . ' repositories');
kllog($nExisting . ' existing hooks', 2);
kllog($nMissing . ' missing hooks', 2);
kllog($nCreated . ' hooks created', 2);
kllog($nDeleted . ' hooks deleted', 2);


function kllog($msg, $level = 1)
{
    global $logLevel;
    if ($level <= $logLevel) {
        echo $msg . "\n";
    }
}

function klerr($msg)
{
    file_put_contents('php://stderr', $msg . "\n");
    exit(2);
}

/**
 * Echos the --help screen.
 *
 * @return void
 */
function showHelp()
{
    echo <<<HLP
Usage: php update-gitorious-hooks.php [options]

Registers or removes the klonfisch hook for all gitorious repositories.

Options:

 -h, --help     Show help
 -v, --verbose  Be verbose (more log messages)
 -q, --quiet    Be quiet (no log messages)
 -c, --create   Create hooks when they are missing
 -r, --remove   Remove hooks when they exist
     --version  Show program version

HLP;
}

?>
