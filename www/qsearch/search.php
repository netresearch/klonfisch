<?php
/**
 * Redirects a Fisheye repository search to a gitorious global search
 * We unfortunately do not have information about the repository
 * and the project.
 */
require_once __DIR__ . '/../www-header.php';

header(
    'Location: ' . $gitoriousUrl . 'search?q=' . urlencode($_GET['q'])
);
?>