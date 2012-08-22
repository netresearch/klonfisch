<?php
require_once __DIR__ . '/../../../www-header.php';
header('Content-Type: application/xml');
?>
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<entities>
  <entity iconUrl="<?php echo $klonfischUrl; ?>repository.png" typeId="fecru.repository" name="test" key="test"/>
  <entity iconUrl="<?php echo $klonfischUrl; ?>project.png" typeId="fecru.project" name="Klonfisch Default Project" key="CR"/>
</entities>
