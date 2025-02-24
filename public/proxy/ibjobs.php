<?php
declare(strict_types=1);

$classLoader = require dirname(__DIR__).'/../vendor/autoload.php';

$baseurl = rtrim($_ENV['REDAKTIONSTOOL_URL'], '/\\');

$locations = "";
$clients = "";
$intern = "";
$categories = "";
$titles = "";
$srclients = "";

if (isset($_REQUEST['clients'])) {
    $clients = $_REQUEST['clients'];
}
if (isset($_REQUEST['sr_clients'])) {
    $srclients = $_REQUEST['sr_clients'];
}
if (isset($_REQUEST['intern'])) {
    $intern = $_REQUEST['intern'];
}
if (isset($_REQUEST['categories'])) {
    $categories = $_REQUEST['categories'];
}
if (isset($_REQUEST['titles'])) {
    $titles = $_REQUEST['titles'];
}
if (isset($_REQUEST['locations'])) {
    $locations = $_REQUEST['locations'];
}

$clients = urldecode((string) $clients);
$srclients = urldecode((string) $srclients);
$intern = urldecode((string) $intern);
$locations = urldecode((string) $locations);
$categories = urldecode((string) $categories);
$titles = urldecode((string) $titles);

$url = $baseurl . '/interfaces/requestIbjobs/clients:' . $clients
. '/sr_clients:' . $srclients
. '/intern:' . $intern
. '/locations:' . base64_encode($locations)
. '/categories:' . base64_encode($categories)
. '/titles:' . base64_encode($titles);

$session = curl_init(str_replace(' ', '%20', $url));

if (!$session) {
    die('curl_init failed');
}

// Don't return HTTP headers. Do return the contents of the call
curl_setopt($session, CURLOPT_HEADER, false);
curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);

if (curl_exec($session) == 'err101') {
    die('page not found');
} else {
    echo str_replace('<!--nocache:001-->', "", (string)curl_exec($session));
}
