<?php

$host = $_SERVER['HTTP_HOST'];
$uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');

$protocol = "http";
if((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
    $protocol = "https";
}

// https://www.internationaler-bund.de/standort/209994/?t=search
$redirect_url = $protocol ."://" . $host .  "/standort/" . (int)$_GET['ID'] . "/?t=search";
//echo $redirect_url;
header("Location: " . $redirect_url);
exit;