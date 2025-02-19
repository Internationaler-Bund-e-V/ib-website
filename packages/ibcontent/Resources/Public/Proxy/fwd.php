<?php

declare(strict_types=1);

/*
 *   valid baseurls
 */
$validDomains = array(
    "https://redaktion.internationaler-bund.de/",
    "https://redaktionstool.ddev.site/",
    "https://redaktion-relaunch.ddev.site/",
    "https://ib:ib@ib-redaktion-staging.rmsdev.de/",
);

$categories = "";
$federalState = "";
$services = "";

$jobtags = false;
$jobservices = false;

$baseurl = "";
if (isset($_REQUEST['baseurl'])) {
    $baseurl = $_REQUEST['baseurl'];
}
if (isset($_REQUEST['categories'])) {
    $categories = $_REQUEST['categories'];
}
if (isset($_REQUEST['services'])) {
    $services = $_REQUEST['services'];
}
if (isset($_REQUEST['federalState'])) {
    $federalState = $_REQUEST['federalState'];
}
if (isset($_REQUEST['jobtags'])) {
    $jobtags = true;
}
if (isset($_REQUEST['jobservices'])) {
    $jobservices = true;
}

$baseurl = urldecode((string)$baseurl);
$categories = urldecode((string)$categories);
$federalState = urldecode((string)$federalState);
$url = "";

if (in_array($baseurl, $validDomains)) {
    if ($jobservices) {
        $url = $baseurl . "interfaces/requestJobservices";
    }

    if ($jobtags) {
        $url = $baseurl . "interfaces/requestJobtags";
    }
    if (!$jobservices && !$jobtags) {
        $url = $baseurl . "interfaces/requestSearchJobs/federalState:" . $federalState
            . "/categories:" . $categories . "/services:" . $services;
    }

    $session = curl_init(str_replace(' ', '%20', (string)$url));

    if ($session === false) {
        die("curl_init failed");
    }

    // Don't return HTTP headers. Do return the contents of the call
    curl_setopt($session, CURLOPT_HEADER, false);
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);

    if (curl_exec($session) == "err101") {
        die("page not found");
    } else {
        echo str_replace("<!--nocache:001-->", "", (string)curl_exec($session));
    }
}
