<?php
//JGA-4 is an issue, "JGA" would be the project only
echo file_get_contents(
    'http://klonfisch.cyberdyne.nr'
    . '/rest-service-fe/changeset-v1/listChangesets/'
    . '?expand=changesets%5B0:20%5D.revisions%5B0:29%5D'
    . '&comment=JGA-4'
    . '&p4JobFixed=JGA-4'
    . '&rep=test'
);
?>