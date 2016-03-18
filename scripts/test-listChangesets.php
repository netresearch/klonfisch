#!/usr/bin/env php
<?php
//JGA-4 is an issue, "JGA" would be the project only
require __DIR__ . '/../data/klonfisch.config.php';
echo file_get_contents(
    $klonfischUrl
    . 'rest-service-fe/changeset-v1/listChangesets/'
    . '?expand=changesets%5B-21:-1%5D.revisions%5B0:29%5D'
    . '&comment=SMM-2'
    . '&p4JobFixed=SMM-2'
    . '&rep=test'
);
?>