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
?><!DOCTYPE html 
	 PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >
<head>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<?php if ($mobile) { ?>
	<meta name="viewport" content="initial-scale=1.0, user-scalable=no">		
	<?php } ?>
	<title>OpenStreetMap Piraten Karte</title>
 
	<style type="text/css">
	<!--
	.photo {
		width: 259px;
	}
	.phototxt {
		width: 175px;
	}	
	-->
	
	
	</style>

	<script type="text/javascript" src="<?php echo $openlayers_path ?>OpenLayers.js"></script>
	<script type="text/javascript" src="<?php echo $openstreetmap_path ?>OpenStreetMap.js"></script>
	<script type="text/javascript" src="./js/urlencode.js"></script>
 
	<script type="text/javascript">
//<![CDATA[	
<?php
if ($_GET['lat'])
	echo "var lat = ".json_encode($_GET['lat']).";";
else if ($_SESSION['deflat'])
	echo "var lat = ".json_encode($_SESSION['deflat']).";";
else
	echo "var lat = 53.37;";
if ($_GET['lon'])
	echo "var lon = ".json_encode($_GET['lon']).";";
else if ($_SESSION['deflon'])
	echo "var lon = ".json_encode($_SESSION['deflon']).";";
else
	echo "var lon = 10.39;";
if ($_GET['zoom'])
	echo "var zoom = ".json_encode($_GET['zoom']).";";
else if ($_SESSION['defzoom'])
	echo "var zoom = ".json_encode($_SESSION['defzoom']).";";
else
	echo "var zoom = 6;";
?> 
		
		var map;
		var gmlLayers = new Array();
 
		function makeAJAXrequest(url) {
			var createXMLHttpRequest = function() {
				try { return new XMLHttpRequest(); } catch(e) {}
				return null;
			}
			var xhReq = createXMLHttpRequest();
			xhReq.open("get", url, true);
			xhReq.onreadystatechange = function() {
			if (xhReq.readyState != 4)  { return; }
				gmlreload();	
			};
			xhReq.send(null);		
		}
 
		function getGML(filter, display) {
			if (!display)
				display = "Unbearbeitet";

			var filterurl = "./kml.php?filter="+filter;
			var mygml = new OpenLayers.Layer.GML(display, filterurl, {
				format: OpenLayers.Format.KML,
				projection: map.displayProjection,
				formatOptions: {
					extractStyles: true,
					extractAttributes: true
				}
			});
			map.addLayer(mygml);
			return {
				url: filterurl,
				gml: mygml
			}
		}

		//Initialise the 'map' object
		function init() {
		  var options = {
				controls:[
					new OpenLayers.Control.Navigation(),
					new OpenLayers.Control.PanZoomBar(),
					new OpenLayers.Control.Attribution(),
					new OpenLayers.Control.LayerSwitcher(),
					new OpenLayers.Control.Permalink()],
				maxResolution: 156543.0399,
				maxExtent: new OpenLayers.Bounds(-2037508.34,-2037508.34,2037508.34,2037508.34),
				numZoomLevels: 19,
				units: 'm',
				projection: new OpenLayers.Projection("EPSG:900913"),
				displayProjection: new OpenLayers.Projection("EPSG:4326")
			};		
		
			map = new OpenLayers.Map ("map",  options );
			layerMapnik = new OpenLayers.Layer.OSM.Mapnik("Mapnik");
			map.addLayer(layerMapnik);
			layerTilesAtHome = new OpenLayers.Layer.OSM.Osmarender("Osmarender");
			map.addLayer(layerTilesAtHome);
			layerCycleMap = new OpenLayers.Layer.OSM.CycleMap("CycleMap");
			map.addLayer(layerCycleMap);

			var control = new OpenLayers.Control();
			OpenLayers.Util.extend(control, {
				draw: function () {
					this.point = new OpenLayers.Handler.Point( control,
						{"done": this.notice},
						{keyMask: OpenLayers.Handler.MOD_CTRL});
					this.point.activate();
				},
				notice: function (point) {
					lonlat = point.transform(
						map.getProjectionObject(),new OpenLayers.Projection("EPSG:4326"));

					makeAJAXrequest("./kml.php?action=add&lon="+lonlat.x+"&lat="+lonlat.y);
				}
			});
			map.addControl(control);
	
			<?php
			foreach ($options as $key=>$value)
			{
			?>
				gmlLayers.push(getGML('<?php echo $key ?>','<?php echo $value ?>'));
			<?php
			}
			?>

			var gmls = new Array();
			for(var i = 0; i < gmlLayers.length; i++)
				gmls.push(gmlLayers[i].gml);

			selectControl = new OpenLayers.Control.SelectFeature(gmls,
						{onSelect: onFeatureSelect, onUnselect: onFeatureUnselect});
		  
			map.addControl(selectControl);
			selectControl.activate();

			var lonLat = new OpenLayers.LonLat(lon, lat).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject());
 
			var markerSize = new OpenLayers.Size(21,25);
			var markerOffset = new OpenLayers.Pixel(-(markerSize.w/2), -markerSize.h);				
			icon = new OpenLayers.Icon('http://www.openstreetmap.org/openlayers/img/marker.png',markerSize,markerOffset);
			map.setCenter (lonLat, zoom); 			
		}

		function onPopupClose(evt) {
			selectControl.unselect(selectedFeature);
		}
		function onFeatureSelect(feature) {
		selectedFeature = feature;
		
		if (feature.pirattype === 'R')
		{
			popup = new OpenLayers.Popup.FramedCloud("chicken", 
								feature.geometry.getBounds().getCenterLonLat(),
								 new OpenLayers.Size(100,100),
				 "<h2><a href='http://wiki.piratenpartei.de/Benutzer:"+feature.attributes.name+"' target='_blank'>"+feature.attributes.name+"</a></h2>"+feature.attributes.description + "<br><br>", null, true, onPopupClose);			
		}
		else
		{			
			popup = new OpenLayers.Popup.FramedCloud("chicken", 
								 feature.geometry.getBounds().getCenterLonLat(),
								 new OpenLayers.Size(100,100),
				 "<h2>"+feature.attributes.name+"</h2>"+feature.attributes.description + "<br><br>", null, true, onPopupClose);
		}
		feature.popup = popup;
		map.addPopup(popup);
	}
	function onFeatureUnselect(feature) {
		map.removePopup(feature.popup);
		feature.popup.destroy();
		feature.popup = null;
	}

	function delid(id){
		selectControl.unselect(selectedFeature);
		makeAJAXrequest("./kml.php?action=del&id="+id);
	}

	function chanteTyp(id, type){
		selectControl.unselect(selectedFeature);
		makeAJAXrequest("./kml.php?action=change&id="+id+"&type="+type);
	}
	function addcomment(id){
		comment = urlencode(document.getElementById('comment['+id+']').value);
		image   = urlencode(document.getElementById('image['+id+']').value);
		makeAJAXrequest("./kml.php?action=addcomment&id="+id+"&comment="+comment+"&image="+image);
		selectControl.unselect(selectedFeature);
	}
	function gmlreload(){
		for(var i = 0; i < gmlLayers.length; i++) {
			var val = gmlLayers[i];
			val.gml.setUrl(val.url);
		}
	}
//]]>
  </script>
</head>
 
<body>
	<div style="float:left; width:50%; height:20px" id="desc">
		<?php if ($loginok==0) { ?>
	Plakate werden erst nachdem Login editierbar.<br />
	Lokaler oder Wiki Login möglich!
	<? } else {	?>
	STRG+Mausklick: neuer Marker
	<?php } ?>
	</div>
	<div style="text-align: right; float:right; width:50%; height:20px; top: 0px" id="login">
	<div style="width:100%; height:50px; top: 0px" id="login">
	<?php
	if ($loginok==0)
	{
	?>
		<form action="<?php echo $url?>login.php" method="post">
		<div>
		User: <input type="text" name="username" />
		Pass: <input type="password" name="password" />
		<input type="submit" value="Login" />
		</div>
		</form>
	<?php
	}
	else
	{
	?>
		<form action="<?php echo $url?>login.php?action=logout" method="post">
		<input type="submit" value="Logout" />
		</form>
	<?php
	}
	?>
	</div>
	<p style="clear: both;" />
	<div style="position:absolute; top:20px; width:200px; left:40%; height:20px" id="message">
	<?php
	if ($_GET['message'])
	{
	?>
		<b style="color: red;"><?php echo $_GET['message']?></b>
	<?php
	}
	?>
	</div>
	<div style="position:absolute; top:50px; bottom:30px; left:0px; right:0px;" id="map" ></div>
	<div style="position:absolute; bottom:0px; left:0px; right0px; height:30px" id="example">
<?php
foreach ($options as $key=>$value)
{
	if ($value!="") {
?>
		<img  style="vertical-align:text-top;" src="./images/markers/<?php echo $key?>.png" width="20" alt="<?php echo $key?>" />=<?php echo $value?>
<?php
	}
} ?>
	<script type="text/javascript">
//<![CDATA[	
		init();
//]]>
	</script>

	</body>
</html>
