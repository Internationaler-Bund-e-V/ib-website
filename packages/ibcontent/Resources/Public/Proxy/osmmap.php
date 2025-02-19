<?php

declare(strict_types=1);

/*
 *   valid baseurls
 */
$validDomains = array(
    "https://redaktion.internationaler-bund.de/",
    "https://ib:ib@ib-redaktion-staging.rmsdev.de/",
    "https://ib-redaktionstool.ddev.site/",
);

$request = "";
$baseurl = "";
if (isset($_REQUEST['baseurl'])) {
    $baseurl = $_REQUEST['baseurl'];
}
if (isset($_REQUEST['navid'])) {
    $navID = intval($_REQUEST['navid']);

    if (isset($_REQUEST['categories'])) {
        $categoryIDs = $_REQUEST['categories'];
        $request = "interfaces/getLocationsForMapsByNavigation/nav_id:" . $navID . "/categories:" . $categoryIDs;
    } else {
        $request = "interfaces/getLocationsForMapsByNavigation/nav_id:" . $navID;
    }
}
if (isset($_REQUEST['locationid'])) {
    $locationID = $_REQUEST['locationid'];
    $request = "interfaces/requestLocation/id:" . $locationID;
}
if (isset($_REQUEST['jobtags'])) {
    $request = "interfaces/requestJobTags";
}
if (isset($_REQUEST['jobservices'])) {
    $request = "interfaces/requestJobServices";
}
if (isset($_REQUEST['fwd'])) {
    $request = "interfaces/requestJobServices";
}
if (isset($_REQUEST['geocode'])) {
    $searchterm = $_REQUEST['geocode'];
    $request = "interfaces/getGeocodes/searchterm:" . $searchterm;
}

if (isset($_REQUEST['radius'])) {
    $radius = $_REQUEST['radius'];
    $lat = $_REQUEST['lat'];
    $long = $_REQUEST['long'];
    $navID = $_REQUEST['navid'];
    if (isset($_REQUEST['categories'])) {
        $categoryIDs = $_REQUEST['categories'];
        $request = "interfaces/requestLocationsByRadius/radius:" . $radius . "/lat:" . $lat . "/long:" . $long . "/navID:" . $navID . "/categories:" . $categoryIDs;
    } else {
        $request = "interfaces/requestLocationsByRadius/radius:" . $radius . "/lat:" . $lat . "/long:" . $long . "/navID:" . $navID;
    }
}

$baseurl = urldecode((string)$baseurl);

if (in_array($baseurl, $validDomains)) {
    $url = $baseurl . $request;

    //die($url);
    $session = curl_init(str_replace(' ', '%20', $url));

    if ($session === false) {
        die("curl_init failed");
    }

    //$session = curl_init('https://redaktion.internationaler-bund.de/interfaces/getLocationsForMapsByNavigation/nav_id:13');
    // Don't return HTTP headers. Do return the contents of the call
    curl_setopt($session, CURLOPT_HEADER, false);
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);

    if (curl_exec($session) == "err101") {
        die("page not found");
    } else {
        echo str_replace("<!--nocache:001-->", "", (string) curl_exec($session));
    }
    //Check for errors.
    if (curl_errno($session)) {
        throw new Exception(curl_error($session));
    }
}
