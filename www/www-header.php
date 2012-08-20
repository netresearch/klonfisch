<?php
require_once __DIR__ . '/../data/klonfisch.config.php';

function checkDbResult($stmt, $ok)
{
    if ($ok !== false) {
        return;
    }
    header('HTTP/1.0 500 Internal server error');
    echo "SQL error\n";
    echo implode(' / ', $stmt->errorInfo()) . "\n";
    exit(1);
}

?>