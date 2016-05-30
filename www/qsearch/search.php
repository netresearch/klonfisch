<?php
/**
 * Redirects a Fisheye repository search to a gitlab global search
 * We unfortunately do not have information about the repository
 * and the project.
 */
require_once __DIR__ . '/../www-header.php';

header(
    'Location: ' . $gitlabUrl . 'search?search=' . urlencode($_GET['q'])
);
?>