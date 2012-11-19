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
	<title>Plakate-Karte</title>
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
	<link rel="stylesheet" type="text/css" href="viewer.css" />

	<link rel="stylesheet" href="http://code.jquery.com/mobile/1.0b2/jquery.mobile-1.0b2.min.css" />
	<script src="http://code.jquery.com/jquery-1.6.2.min.js"></script>
	<script src="http://code.jquery.com/mobile/1.0b2/jquery.mobile-1.0b2.min.js"></script>	
	
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
					document.getElementById('info_memo').innerHTML = data.comment;
					document.getElementById('info_image').src = data.image ? data.image : 'images/noimg.png'
<?php if ($loginok != 0) { ?>
					document.getElementById('delMark').onclick = function() {
					    makeAJAXrequest("./json.php?action=del&id="+data.id);
					}
<?php } ?>				$.mobile.changePage($("#editfrm"));
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
			if (new_markers != null) {
				for(var i = 0; i < new_markers.length; i++)
					createMarker(new_markers[i]);
			}
		}

		EventUtils.addEventListener(window, 'load', function(){
			touchMap = new touchMapLite("viewer");
<?php
$lat = get_float('lat');
$lon = get_float('lon');
$zoom = get_int('zoom');
if ($lat)
	echo "touchMap.lat = ".$lat.";";
else if ($_SESSION['deflat'])
	echo "touchMap.lat = ".$_SESSION['deflat'].";";
else
	echo "touchMap.lat = 53.37;";
if ($lon)
	echo "touchMap.lon = ".$lon.";";
else if ($_SESSION['deflon'])
	echo "touchMap.lon = ".$_SESSION['deflon'].";";
else
	echo "touchMap.lon = 10.39;";
if ($zoom)
	echo "touchMap.zoom = ".$zoom.";";
else if ($_SESSION['defzoom'])
	echo "touchMap.zoom = ".$_SESSION['defzoom'].";";
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

		PanoJS.optionsHandler = function(e) {
			$.mobile.changePage($("#settings"));
			return false;
		}

		toggleWatchLocation = function(node) {
			if(!touchMap.watchLocationHandler()){
				var myswitch = $("#slider");
				myswitch[0].selectedIndex = myswitch[0].selectedIndex == 0 ? 1 : 0;
				myswitch.slider("refresh");
			}
		}
	</script>	
</head>
<body>
	
	<div id="home" data-role="page">
		<div data-role="header">
			<h1>Plakate-Karte</h1>
		</div>
<?php if(isset($_GET["error"])) { ?>
		<div id="errors" class="ui-bar ui-bar-e">
			<div style="float:right; text-align:right;">
				<a href="#home" onclick="$('#errors').hide();" data-role="button" data-icon="delete" data-iconpos="notext" data-shadow="false" title="Schließen">&nbsp;</a>
			</div>
			<p style="font-size:85%;"><?php echo $_GET["error"]; ?></p>
		</div>
<?php } ?>
<?php if(isset($_GET["message"])) { ?>
		<div id="messages" class="ui-bar ui-bar-d">
			<div style="float:right; text-align:right;">
				<a href="#home" onclick="$('#messages').hide();" data-role="button" data-icon="delete" data-iconpos="notext" data-shadow="false" title="Schließen">&nbsp;</a>
			</div>
			<p style="font-size:85%;"><?php echo $_GET["message"]; ?></p>
		</div>
<?php } ?>
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
	</div>
	
  <div id="setmarker" data-role="page">
	<div data-role="header">
		<a href="#" data-role="button" data-rel="back" data-icon="back" data-iconpos="notext"></a>
		<h1>Marker setzten</h1>
	</div>
	<ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="b">
	<?php
foreach ($options as $key=>$value)
{
    if ($value != '') { ?>
      <li><a href="#home" onclick="setMarker('<?php echo $key; ?>');"><?php echo $value; ?></a></li>
    <?php }
}
    ?></ul>
  </div>
	
	
	
	<div id="settings" data-role="page">
		<div data-role="header">
			<a href="#" data-role="button" data-rel="back" data-icon="back" data-iconpos="notext"></a>
			<h1>Menu</h1>
		</div>
		<div data-role="content">
		
<?php if ($loginok==0) { ?>
			<form action="<?php echo $url?>login.php" method="post" class="dialog" id="loginfrm" name="loginfrm">
<?php } else { ?>
			<form name="logout" id="logout" action="<?php echo $url?>login.php?action=logout" method="post">
<?php } ?>
				<ul data-role="listview" data-theme="c" data-dividertheme="b">
					<li data-role="list-divider">Zugang</li>
						
<?php if ($loginok==0) { ?>
					<li><a href="#register">Registrieren</a></li>
					<li data-role="fieldcontain">
						<label for="username">Benutzername:</label>
						<input type="text" name="username" id="username" />
					</li>
					<li data-role="fieldcontain">
						<label for="password">Passwort:</label>
						<input type="password" name="password" id="password" />
					</li>
					<li><a href="#home" onclick="document.forms['loginfrm'].submit();">Login</a></li>
<?php } else { ?>
					<li><a href="#home" onclick="document.forms['logout'].submit();">Logout</a></li>
					<li><a href="#setmarker" >Marker auf aktueller Position</a></li>
<?php } ?>
					<li data-role="list-divider">Position</li>

					<li><a onclick="touchMap.findLocationHandler();" href="#home">Position suchen</a></li>
					<li data-role="fieldcontain">
						<label for="slider">Positionsverfolgung:</label>
						<select name="slider" id="slider" data-role="slider" onchange="toggleWatchLocation(this);">
							<option value="off">Aus</option>
							<option value="on">An</option>
						</select> 
					</li>
				</ul>
			</form>
		</div>
    </div>

	<div id="register" data-role="page">
		<div data-role="header">
			<a href="#" data-role="button" data-rel="back" data-icon="back" data-iconpos="notext"></a>
			<h1>Registrieren</h1>
		</div>
		<div data-role="content">
		<form action="<?php echo $url?>register.php" method="post" class="dialog" id="registerfrm" name="registerfrm">
			<input type="hidden" name="action" value="register" />
			<ul data-role="listview" data-theme="c" data-dividertheme="b">
				<li data-role="list-divider">Account registrieren</li>
				<li data-role="fieldcontain">
					<label for="username">Benutzername:</label>
					<input type="text" name="username" id="username" />
				</li>
				<li data-role="fieldcontain">
					<label for="email">E-Mail-Adresse:</label>
					<input type="email" name="email" id="email" />
				</li>
				<li><a href="#home" onclick="document.forms['registerfrm'].submit();">Registrieren</a></li>
			</ul>
		</form>
	</div>

  <div id="editfrm" title="Details" data-role="page">
  	<div data-role="header">
		<a href="#" data-role="button" data-rel="back" data-icon="back" data-iconpos="notext"></a>
		<h1>Details</h1>
	</div>
	<div data-role="content">
		<ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="b">
			<li><label id="info_typ" class="plaintxt" >&nbsp;</label></li>
			<li><label id="info_memo" class="plaintxt" >&nbsp;</label></li>
			<li><img src="images/noimg.png" id="info_image" width="250" /></li>
		</ul>
<?php if ($loginok!=0) { ?>
    <!--a class="whiteButton" href="#home">Marker editieren</a-->
		<a href="#home" data-role="button" id="delMark" data-icon="delete">Marker löschen</a>
<?php } ?>
		
	</div>
  </div>




  <script type="text/javascript" src="js/touchMapLite.event.touch.js"></script>
  <script type="text/javascript" src="js/touchMapLite.event.wheel.js"></script>
  <script type="text/javascript" src="js/touchMapLite.geolocation.js"></script>
</body>
</html>
