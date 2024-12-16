<?php

/**
 * we need to modify the json data from redaktion.internationaler-bund.de in order to make it work with d3js
 * mk@rms, 2021-10-12
 *
 * @usage json_proxy.php?nav_id=3
 * @param nav_id The navigation id to fetch data from
 */

$naviagtion_id = 0;
if (isset($_GET['nav_id'])) {
    $naviagtion_id = (int)$_GET['nav_id'];
}
$content = file_get_contents("https://redaktion.internationaler-bund.de/interfaces/getLocationsForMapsByNavigation/nav_id:" . $naviagtion_id);
$result = '{"locations":' . trim($content) . '}';
echo $result;
exit;