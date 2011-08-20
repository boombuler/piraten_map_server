<?php
/*
       Licensed to the Apache Software Foundation (ASF) under one
       or more contributor license agreements.  See the NOTICE file
       distributed with this work for additional information
       regarding copyright ownership.  The ASF licenses this file
       to you under the Apache License, Version 2.0 (the
       "License"); you may not use this file except in compliance
       with the License.  You may obtain a copy of the License at

         http://www.apache.org/licenses/LICENSE-2.0

       Unless required by applicable law or agreed to in writing,
       software distributed under the License is distributed on an
       "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
       KIND, either express or implied.  See the License for the
       specific language governing permissions and limitations
       under the License.
*/
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
         "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>OpenStreetMap Piraten Karte</title>
    <meta charset="UTF-8" />
 	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
 	<meta http-equiv="imagetoolbar" content="no" />
	<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />	
	<meta name="robots" content="NOINDEX,NOFOLLOW" />
	
	<script type="text/javascript" src="js/PanoJS.min.js"></script>
	<script type="text/javascript" src="js/touchMapLite.js"></script>
	<script type="text/javascript" src="js/touchMapLite.tileUrlProvider.OSM.js"></script>
	
	<script type="text/javascript" src="js/touchMapLite.marker.js"></script>
	<script type="text/javascript" src="js/htmlEncode.js"></script>
	<script type="application/x-javascript" src="iui/iui.js"></script> 
	<link rel="stylesheet" type="text/css" href="viewer.css" />
	<link rel="stylesheet" type="text/css" href="iui/iui.css" />
	<script type="text/javascript">
		
		var touchMap = null;
		var typLookup = {<?php
$firstloop = true;
foreach ($options as $key=>$value)
{
	if ($value!="") {
	  if (!$firstloop) 
		echo ",";
	  echo "'".$key."':'".$value."'";
	  $firstloop = false;
	}
}
?>}
		function createMarker(data) {
			var marker = new touchMap.marker({
				title: data.type, 
				lat: data.lat*1.0, 
				lon: data.lon*1.0,
				divx: -8,
				divy: -8,
				markerSrc: 'images/markers/'+data.type+'.png',
				onClick: function(event) {

					document.getElementById('info_typ').innerHTML = typLookup[data.type];
					document.getElementById('info_memo').innerHTML = htmlEncode(data.comment, true, 0);
					document.getElementById('info_image').src = data.image ? data.image : 'images/noimg.png'
<?php if ($loginok != 0) { ?>
					document.getElementById('delMark').onclick = function() {
					    makeAJAXrequest("./json.php?action=del&id="+data.id);
					}
<?php } ?>				window.iui.showPageById('editfrm');
					return false;
				}
			} , findOnMap, false);
		}

		function makeAJAXrequest(url, readyFn) {
			var createXMLHttpRequest = function() {
				try { return new XMLHttpRequest(); } catch(e) {}
				return null;
			}
			if (!readyFn)
				readyFn = gmlreload;
			var xhReq = createXMLHttpRequest();
			xhReq.open("get", url, true);
			xhReq.onreadystatechange = function() {
				if ( xhReq.readyState == 4 && xhReq.status == 200 ) {
					readyFn(xhReq.responseText);
				}
			};
			xhReq.send(null);		
		}

		function setMarker(aType) {
		      navigator.geolocation.getCurrentPosition(function(pos) {
			  makeAJAXrequest("./json.php?action=add&typ="+aType+"&lon="+
                                          pos.coords.longitude+"&lat="+pos.coords.latitude);
		      }, undefined, {enableHighAccuracy: true});
		}

		function gmlreload(result) {
			for(var i = 0; i < touchMap.MARKERS.length; i++) {
			    if (touchMap.MARKERS[i])
				touchMap.MARKERS[i].drop()
			}
			var new_markers = JSON.parse( result );	
			for(var i = 0; i < new_markers.length; i++)
				createMarker(new_markers[i]);
		}

		EventUtils.addEventListener(window, 'load', function(){
			touchMap = new touchMapLite("viewer");
<?php
$lat = get_float('lat');
$lon = get_float('lon');
$zoom = get_int('zoom');
if ($lat)
	echo "touchMap.lat = ".json_encode($lat).";";
else if ($_SESSION['deflat'])
	echo "touchMap.lat = ".json_encode($_SESSION['deflat']).";";
else
	echo "touchMap.lat = 53.37;";
if ($lon)
	echo "touchMap.lon = ".json_encode($lon).";";
else if ($_SESSION['deflon'])
	echo "touchMap.lon = ".json_encode($_SESSION['deflon']).";";
else
	echo "touchMap.lon = 10.39;";
if ($zoom)
	echo "touchMap.zoom = ".json_encode($zoom).";";
else if ($_SESSION['defzoom'])
	echo "touchMap.zoom = ".json_encode($_SESSION['defzoom']).";";
else
	echo "touchMap.zoom = 6;";
?>		
			touchMap.init();
			findOnMap = touchMap;	
			makeAJAXrequest('json.php');			
		}, false);

		EventUtils.addEventListener(window, 'resize', function(){
			touchMap.reinitializeGraphic();
		}, false);

		PanoJS.settingsHandler = function(e) {
			window.iui.showPageById('settings');
			return false;
		};
		PanoJS.optionsHandler = function(e) {
			window.iui.showPageById('settings');
			return false;
		}

		toggleWatchLocation = function(node) {
			if(!touchMap.watchLocationHandler()){
				node.setAttribute("toggled", node.getAttribute("toggled") != "true")
			}
		}

		iui.animOn = true;
	</script>	
</head>
<body>
	<div class="toolbar" id="toolbar">
	  <h1 id="pageTitle"></h1>
	  <a id="backButton" class="button" href="#"></a>
	</div>    
	<ul id="home" title="Karte" selected="true">
		<div id="viewer">
			<div class="well"><!-- --></div>
			<div class="surface" id="touchArea"><!-- --></div>
			<div class="marker" id="markers"></div> 
			<p class="controls">
				<span class="zoomIn" title="Zoom In">+</span>
				<span class="zoomOut" title="Zoom Out">-</span>
				<span class="options" title="Show Options">Options</span>
			</p>
		</div>
	</ul>
	<div id="settings" title="Menu" class="panel">
		<h2>Zugang</h2>
		<fieldset>
		  <div class="row">
<?php if ($loginok==0) { ?>
                <a class="dir" href="#loginfrm">Login</a>
<?php } else { ?>
		<a class="dir" href="#home" onclick="document.forms['logout'].submit();">Logout</a>
		</div><div class="row">
		<a class="dir" href="#setmarker">Marker auf aktueller Position</a>
<?php } ?>
		  </div>		
		</fieldset>
		
        <h2>Position</h2>
        <fieldset>
            <div class="row">
                <a class="dir" onclick="touchMap.findLocationHandler();" href="#home">Position suchen</a>
            </div>
            <div class="row">
              <label>Positionsverfolgung</label>
                <div class="toggle" onclick="toggleWatchLocation(this);"><span class="thumb"></span><span class="toggleOn">ON</span><span class="toggleOff">OFF</span></div>
            </div>
	</fieldset>
    </div>
<?php if ($loginok==0) { ?>
	<form action="<?php echo $url?>login.php" method="post" class="dialog" id="loginfrm" name="loginfrm">
		<fieldset>
			<h1>Login</h1>
			<a class="button leftButton" type="cancel">Settings</a>
			<a class="button blueButton" onclick="document.forms['loginfrm'].submit();">Login</a>
			<label>User:</label>
			<input type="text" name="username" />
			<label>Pass:</label>
			<input type="password" name="password" />
		</fieldset>
	</form>
<?php } else { ?>
	<form name="logout" id="logout" action="<?php echo $url?>login.php?action=logout" method="post"></form>
<?php } ?>

  <div id="editfrm" title="Details" class="panel">
	<fieldset>
	    <div class="row"><label id="info_typ" class="plaintxt" >&nbsp;</label></div>
	    <div class="row"><label id="info_memo" class="plaintxt" >&nbsp;</label></div>
	    <div class="row"><img src="images/noimg.png" id="info_image" width="250" class="centerimg" /></div>
	</fieldset>
<?php if ($loginok!=0) { ?>
    <a class="whiteButton" href="#home">Marker editieren</a>
    <a class="whiteButton" href="#home" id="delMark">Marker löschen</a>
<?php } ?>
  </div>

  <div id="setmarker" title="Marker setzten" class="panel">
    <fieldset><?php
foreach ($options as $key=>$value)
{
    if ($value != '') { ?>
      <div class="row"><a class="dir" href="#home" onclick="setMarker('<?php echo $key; ?>');"><?php echo $value; ?></a></div>
    <?php }
}
    ?></fieldset>
  </div>


  <script type="text/javascript" src="js/touchMapLite.event.touch.js"></script>
  <script type="text/javascript" src="js/touchMapLite.event.wheel.js"></script>
  <script type="text/javascript" src="js/touchMapLite.geolocation.js"></script>
</body>
</html>