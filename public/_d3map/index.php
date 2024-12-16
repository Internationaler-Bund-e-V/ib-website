<?php
$cache_buster = (int)filemtime('myapp.js') + (int)filemtime('map.min.css');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="initial-scale=1.0, width=device-width"/>
    <title>Map</title>
    <script src="./libs/maps.libs.js?r=<?= $cache_buster ?>"></script>
    <script src="./libs/select2/select2.min.js?r=<?= $cache_buster ?>"></script>
    <link rel="stylesheet" href="./libs/select2/select2.min.css?r=<?= $cache_buster ?>"/>
    <link rel="stylesheet" href="map.min.css?r=<?= $cache_buster ?>"/>
</head>

<body>

<div id="my_global_container">
    <div id="navigation-holder">
        <form>
            <label for="State">Bundesland
                <select id="State" name="State" class="myselect2" onchange="selectState()"></select>
            </label>
            <label for="Tags" class="label_tags">Programmteil
                <select id="Tags" name="Tags" class="myselect2" onchange="selectTag()"></select>
            </label>
        </form>
    </div>
    <div id="global_map_container">
        <div id="map-holder"></div>
        <div id="list-holder">
            <ul id="location_list_view">
                <li>
                    <a href="openlocation.php" class="bind-ID" target="_blank">
                        <span class="bind-Name"></span>
                    </a>
                </li>
            </ul>
        </div>
        <div id="zoom_nav">
            <span><a id="button_zoom_in" href="#">&#43;</a></span>
            <span><a id="button_zoom_out" href="#">&#8722;</a></span>
        </div>
    </div>
    <div id="global_tooltip">
        <div id="tooltip_content">
            <div class="close_tooltip">
                <a href="#" onclick="hideTooltip()">&#x2715</a>
            </div>
            <h1 name="tooltip_headline">HEADLINE</h1>
            <span name="tooltip_city">CITY</span><br/>
            <span name="tooltip_street">STREET</span><br/>
            <span name="tooltip_state">STATE</span><br/>
            <span name="tooltip_phone">PHONE</span><br/>
            <p name="tooltip_tags">Programmteil</p>
            <p>
            <span name="tooltip_link">
                <a href="openlocation.php" class="bind-ID" target="_blank">
                    Zum Standort
                </a>
            </span>
            </p>
        </div>
    </div>
    <div id="countryName">
        <span>Baden-Würtemberg</span>
    </div>
</div>

<script type="text/javascript" src="myapp.js?r=<?= $cache_buster ?>"></script>
<script type="text/javascript" src="select2.js?r=<?= $cache_buster ?>"></script>

<!--<script type="text/javascript" src="jquery.iBMap.js"></script>
<script>
    $('#my_global_container').ibMap({'foo': 'bar'});
</script>
-->

</body>
</html>