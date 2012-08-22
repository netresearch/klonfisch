<?php
require_once __DIR__ . '/../../../www-header.php';
header('Content-Type: application/xml');
?>
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<manifest>
  <id><?php echo $klonfischUuid; ?></id>
  <name>Klonfisch</name>
  <typeId>fecru</typeId>
  <version>2.7.15</version>
  <buildNumber>2007015</buildNumber>
  <applinksVersion>3.7.0</applinksVersion>
  <inboundAuthenticationTypes>com.atlassian.applinks.api.auth.types.BasicAuthenticationProvider</inboundAuthenticationTypes>
<!--
  <inboundAuthenticationTypes>com.atlassian.applinks.api.auth.types.OAuthAuthenticationProvider</inboundAuthenticationTypes>
  <inboundAuthenticationTypes>com.atlassian.applinks.api.auth.types.TrustedAppsAuthenticationProvider</inboundAuthenticationTypes>
-->
  <outboundAuthenticationTypes>com.atlassian.applinks.api.auth.types.BasicAuthenticationProvider</outboundAuthenticationTypes>
<!--
  <outboundAuthenticationTypes>com.atlassian.applinks.api.auth.types.OAuthAuthenticationProvider</outboundAuthenticationTypes>
  <outboundAuthenticationTypes>com.atlassian.applinks.api.auth.types.TrustedAppsAuthenticationProvider</outboundAuthenticationTypes>
-->
  <publicSignup>true</publicSignup>
  <url><?php echo $klonfischUrl; ?></url>
</manifest>
