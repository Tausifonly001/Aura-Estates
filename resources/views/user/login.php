<?php
$docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$projectRoot = str_replace('\\', '/', dirname(__DIR__, 3));
$basePath = str_replace($docRoot, '', $projectRoot);
$basePrefix = rtrim($basePath, '/');
header("Location: " . $basePrefix . "/login");
exit;
