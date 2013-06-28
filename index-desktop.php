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
$canSendMail = $send_mail_adr != '';
$is_mac_os = (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'Macintosh') !== false));

?><!DOCTYPE html
	 PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >
<head>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<title>OpenStreetMap Piraten Karte</title>
	<link rel="stylesheet" href="bootstrap-1.1.0.min.css">
	<style type="text/css">
	<!--
	.photo {
		height: 120px;
	}
	#mask {
		position:absolute;
		z-index:10100;
		background-color:#888;
		display:none;
	}
	#mapkey {
		position:absolute;
		z-index:3000;
		bottom:0px;
		left:0px;
		display:none;
	}
	.localmodaldlg {
		position: relative;
		top: auto;
		left: auto;
		margin: 0 auto;
		display:none;
	}
	-->


	</style>
	<script src="http://code.jquery.com/jquery-1.5.2.min.js"></script>
	<script type="text/javascript" src="./js/OpenLayers.php"></script>

	<script type="text/javascript">
//<![CDATA[
<?php
$lat = get_float('lat');
$lon = get_float('lon');
$zoom = get_int('zoom');

if ($lat)
	echo "var lat = ".json_encode($lat).";";
else if ($_SESSION['deflat'])
	echo "var lat = ".json_encode($_SESSION['deflat']).";";
else
	echo "var lat = 53.37;";
if ($lon)
	echo "var lon = ".json_encode($lon).";";
else if ($_SESSION['deflon'])
	echo "var lon = ".json_encode($_SESSION['deflon']).";";
else
	echo "var lon = 10.39;";
if ($zoom)
	echo "var zoom = ".json_encode($zoom).";";
else if ($_SESSION['defzoom'])
	echo "var zoom = ".json_encode($_SESSION['defzoom']).";";
else
	echo "var zoom = 6;";
?>

		var map;
		var gmlLayers = new Array();

		function makeAJAXrequest(url, data) {
			$.ajax({
				url: url,
				data: data,
				success: function(msg){
					gmlreload();
				}
			});
		}

		function closeModal() {
			if (selectedFeature != null) {
				sf = selectedFeature;
				selectedFeature = null;
				closeModalDlg(true);
				selectControl.unselect(sf);
			}
		}

		function closeModalDlg(shouldRemove, oncomplete) {
			$('#mask').fadeTo("fast",0, function() {$(this).css('display', 'none')});
			$('body > .modal').fadeOut(function() {
				$(this).remove();
				if(!shouldRemove)
					$('#dlgBag').append($(this));
				if (oncomplete)
					oncomplete();
			});
		}

		function showModal(content) {
			var maskHeight = $(window).height();
			var maskWidth = $(window).width();

			//Set height and width to mask to fill up the whole screen
			$('#mask').css({'width':maskWidth,'height':maskHeight});

			//transition effect
			$('#mask').fadeTo("fast",0.8);
			//Get the window height and width
			var winH = $(window).height();

			$('body').append(content);
			//Set the popup window to center
			$('body > .modal')
				.css('z-index', '10101')
				.css('top',  maskHeight/2-$('body > .modal').height()/2)
				.fadeIn();
		}

		function showModalId(id) {
			showModal($('#'+id));
		}

		<?php include "popups.php" ?>

		function getGML(filter, display) {
			if (!display)
				display = "Unbearbeitet";

			var filterurl = "./kml.php?filter="+filter;

			var mygml = new OpenLayers.Layer.Vector(display, {
				projection: map.displayProjection,
				strategies: [
					new OpenLayers.Strategy.BBOX()
				],
				protocol: new OpenLayers.Protocol.HTTP({
					url: filterurl,
					format: new OpenLayers.Format.KML({
                        extractStyles: true,
                        extractAttributes: true
                    }),
				})
			});

			map.addLayer(mygml);

			return mygml;
		}

		//Initialise the 'map' object
		function init() {
			OpenLayers.ImgPath = "./theme/default/";
			var options = {
				controls:[
					new OpenLayers.Control.Navigation(),
					new OpenLayers.Control.PanZoomBar(),
					new OpenLayers.Control.Attribution(),
					new OpenLayers.Control.LayerSwitcher({
						roundedCornerColor: 'black'
					}),
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
<?php if ($is_mac_os) {?>
						{keyMask: OpenLayers.Handler.MOD_META});
<?php } else { ?>
						{keyMask: OpenLayers.Handler.MOD_CTRL});
<?php } ?>
					this.point.activate();
				},
				notice: function (point) {
					lonlat = point.transform(
						map.getProjectionObject(),new OpenLayers.Projection("EPSG:4326"));

					makeAJAXrequest("./kml.php", {
						"action": "add",
						"lon": lonlat.x,
						"lat" :lonlat.y
					});
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

			selectControl = new OpenLayers.Control.SelectFeature(gmlLayers,
						{onSelect: onFeatureSelect, onUnselect: onFeatureUnselect});

			map.addControl(selectControl);
			selectControl.activate();

			var lonLat = new OpenLayers.LonLat(lon, lat).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject());
			map.setCenter (lonLat, zoom);
		}

	function onFeatureUnselect(feature) {
		closeModal();
	}

	function onFeatureSelect(feature) {
		selectedFeature = feature;
		showModal(createPopup(feature.attributes.description));
	}

	function delid(id){
		selectControl.unselect(selectedFeature);
		makeAJAXrequest("./kml.php", {"action":"del", "id":id});
	}

	function change(id){
		makeAJAXrequest("./kml.php", {
			"id"      : id,
			"action"  : "change",
			"type"    : document.getElementById('typ['+id+']').value,
			"comment" : document.getElementById('comment['+id+']').value,
			"city"    : document.getElementById('city['+id+']').value,
			"street"  : document.getElementById('street['+id+']').value,
			"image"   : document.getElementById('image['+id+']').value
		});
		selectControl.unselect(selectedFeature);
	}
	function gmlreload(){
		for(var i = 0; i < gmlLayers.length; i++) {
			var val = gmlLayers[i];
			//setting loaded to false unloads the layer//
			val.loaded = false;
			//setting visibility to true forces a reload of the layer//
			val.setVisibility(true);
			//the refresh will force it to get the new KML data//
			val.refresh({ force: true, params: { 'random': Math.random()} });
		}
	}

	function togglemapkey() {
		show = $('#mapkey').css('display') == 'none';
		if (show)
		   $('#mapkey').fadeIn();
		else
		   $('#mapkey').fadeOut(function() { $('#mapkey').css('display', 'none') });
	}

	function closeMsg() {
	   $('#message').fadeOut(function() { $(this).remove() });
	   $('#map').animate({top: '40px'});
	}

	$(document).ready(function(e) {
		init();
		$(window).resize(function() {
			var maskHeight = $(window).height();
			var maskWidth = $(window).width();
			$('#mask').css({'width':maskWidth,'height':maskHeight});
			$('body > .modal').css('top',  maskHeight/2-$('body > .modal').height()/2);
		});<?php if ($_GET['message']) { ?>
		setTimeout("closeMsg()", 2500);
		<?php } ?>

		$("body").bind("click", function(e) {
			$("ul.menu-dropdown").hide();
			$('a.menu').parent("li").removeClass("open").children("ul.menu-dropdown").hide();
		});

		$("a.menu").click(function(e) {
			var $target = $(this);
			var $parent = $target.parent("li");
			var $siblings = $target.siblings("ul.menu-dropdown");
			var $parentSiblings = $parent.siblings("li");
			if ($parent.hasClass("open")) {
			  $parent.removeClass("open");
			  $siblings.hide();
			} else {
			  $parent.addClass("open");
			  $siblings.show();
			}
			$parentSiblings.children("ul.menu-dropdown").hide();
			$parentSiblings.removeClass("open");
			return false;
		});
	});
//]]>
  </script>
</head>

<body>
	<div id="mask"></div>


	<div class="topbar">
      <div class="fill">
        <div class="container">
          <h3><a href="#">Plakat-Karte</a></h3>
          <ul>
		<?php if ($loginok != 0) { ?>
			<form id="formLogout" action="<?php echo $url?>login.php?action=logout" method="post"></form>

			<?php if ($_SESSION['wikisession']) {?>
			  <li><a href="#" onclick="javascript:document.forms['formLogout'].submit()">Abmelden</a></li>
			<?php } else { ?>
			  <li class="menu">
				<a href="#" class="menu"><?php echo $_SESSION['siduser']?></a>
				<ul class="menu-dropdown">
                        <?php if (isAdmin()) { ?>
                    <li><a href="admin.php" target="_blank">Administration</a></li>
                    <li class="divider" />
                        <?php }
                        if ($canSendMail) { ?>
					<li><a href="#" onclick="javascript:showModalId('chpwform');">Passwort ändern</a></li>
					<li class="divider" />
                        <?php } ?>
					<li><a href="#" onclick="javascript:document.forms['formLogout'].submit()">Abmelden</a></li>
				</ul>
			  </li>
			<?php }
				if ($enable_image_upload) { ?>
			<li><a href="#" onclick="javascript:showModalId('uploadimg');">Bild hochladen</a></li>
		<?php   }
			  } else { ?>
			<li><a href="#" onclick="javascript:showModalId('loginform');">Anmelden</a></li>
                <?php if ($canSendMail) { ?>
                        <li><a href="#" onclick="javascript:showModalId('registerform');">Registrieren</a></li>
		<?php } /* $canSendMail */
                      } /* loginok */ ?>
			<li><a href="#" onclick="javascript:togglemapkey();">Legende / Hilfe</a></li>
          </ul>
        </div>
      </div> <!-- /fill -->
    </div> <!-- /topbar -->
	<div style="display:none;" id="dlgBag">
	<?php if ($loginok == 0) { ?>
		<div class="modal localmodaldlg" id="loginform">
          <div class="modal-header">
            <h3>Anmelden</h3>
			<a href="#" class="close" onclick="javascript:closeModalDlg(false);">&times;</a>
          </div>
          <div class="modal-body">
			<form id="formlogin" action="<?php echo $url?>login.php" method="post">

				<div class="clearfix">
					<label for="username">Benutzer</label>
					<div class="input">
						<input type="text" size="30" class="xlarge" name="username" id="username" />
					</div>
				</div>

				<div class="clearfix">
					<label for="password">Passwort</label>
					<div class="input">
						<input type="password" size="30" class="xlarge" name="password" id="password" />
                                                <?php if ($canSendMail) { ?>
						<span class="help-block">
							<a href="#" onclick="javascript:closeModalDlg(false, function() {showModalId('newpassform');});">Passwort vergessen?</a> (Nur für Nicht-Wiki-Benutzer)
						</span>
                                                <?php } ?>
					</div>
				</div>
			</form>
          </div>
          <div class="modal-footer">
			<a href="#" class="btn primary" onclick="javascript:document.forms['formlogin'].submit();">Anmelden</a>
			<a href="#" class="btn secondary" onclick="javascript:closeModalDlg(false);">Abbrechen</a>
          </div>
        </div>

        <?php if ($canSendMail) { ?>
		<div class="modal localmodaldlg" id="newpassform">
          <div class="modal-header">
			<h3>Neues Passwort</h3>
			<a href="#" class="close" onclick="javascript:closeModalDlg(false);">&times;</a>
          </div>
          <div class="modal-body">
			<form id="formnewpass" action="<?php echo $url?>register.php" method="post">
				<input type="hidden" name="action" value="resetpw" />
				<div class="clearfix">
					<label for="username">Benutzername:</label>
					<div class="input">
						<input type="text" size="30" class="xlarge" name="username" />
					</div>
				</div>

				<div class="clearfix">
					<label for="password">E-Mail-Adresse:</label>
					<div class="input">
						<input type="text" size="30" class="xlarge" name="email"/>
					</div>
				</div>
			</form>
          </div>
          <div class="modal-footer">
			<a href="#" class="btn primary" onclick="javascript:document.forms['formnewpass'].submit();">Passwort anfordern</a>
			<a href="#" class="btn secondary" onclick="javascript:closeModalDlg(false);">Abbrechen</a>
          </div>
        </div>


		<div class="modal localmodaldlg" id="registerform">
			<div class="modal-header">
				<h3>Registrieren</h3>
				<a href="#" class="close" onclick="javascript:closeModalDlg(false);">&times;</a>
			</div>
			<div class="modal-body">
				<form id="formregister" action="<?php echo $url?>register.php" method="post">
					<input type="hidden" name="action" value="register" />
					<div class="clearfix">
						<label for="username">Benutzername:</label>
						<div class="input">
							<input type="text" size="30" class="xlarge" name="username" />
						</div>
					</div>

					<div class="clearfix">
						<label for="email">E-Mail-Adresse:</label>
						<div class="input">
							<input type="text" size="30" class="xlarge" name="email"/>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<a href="#" class="btn primary" onclick="javascript:document.forms['formregister'].submit();">Registrieren</a>
				<a href="#" class="btn secondary" onclick="javascript:closeModalDlg(false);">Abbrechen</a>
			</div>
		</div>



	<?php
            } /* $canSendMail */
            } else {
            	if ($enable_image_upload) { ?>
		<div class="modal localmodaldlg" id="uploadimg">
          <div class="modal-header">
			<h3>Bild hochladen</h3>
			<a href="#" class="close" onclick="javascript:closeModalDlg(false);">&times;</a>
          </div>
          <div class="modal-body">
			<form enctype="multipart/form-data" method="post" id="formimgup" action="image.php">
				<div class="clearfix">
					<label for="image">Bild hochladen</label>
					<div class="input">
						<input type="file" id="image" name="image" class="xlarge">
					</div>
				</div>
				<input type="hidden" name="completed" value="1">
			</form>
          </div>
          <div class="modal-footer">
			<a href="#" class="btn primary" onclick="javascript:document.forms['formimgup'].submit();">Hochladen</a>
			<a href="#" class="btn secondary" onclick="javascript:closeModalDlg(false);">Abbrechen</a>
          </div>
        </div>
		<?php }
		  if ($canSendMail) { ?>
		<div class="modal localmodaldlg" id="chpwform">
          <div class="modal-header">
			<h3>Passwort ändern</h3>
			<a href="#" class="close" onclick="javascript:closeModalDlg(false);">&times;</a>
          </div>
          <div class="modal-body">
			<form id="formchpw" action="<?php echo $url?>register.php" method="post">
				<input type="hidden" name="action" value="changepw" />

				<div class="clearfix">
					<label for="password">Neues Passwort:</label>
					<div class="input">
						<input type="password" size="30" class="xlarge" name="newpass" />
					</div>
				</div>

				<div class="clearfix">
					<label for="password">Neues Passwort wiederholen:</label>
					<div class="input">
						<input type="password" size="30" class="xlarge" name="passconfirm" />
					</div>
				</div>
			</form>
          </div>
          <div class="modal-footer">
			<a href="#" class="btn primary" onclick="javascript:document.forms['formchpw'].submit();">Passwort ändern</a>
			<a href="#" class="btn secondary" onclick="javascript:closeModalDlg(false);">Abbrechen</a>
          </div>
        </div>

	<?php } /* $canSendMail */
        } ?>
	</div>
	<?php
	if ($_GET['message'])
	{
	?>
	  <div class="alert-message info" id="message" style="margin-top:43px">
		<a class="close" href="#" onclick="javascript:closeMsg();">&times;</a>
        <p><?php echo htmlspecialchars($_GET['message']) ?></p>
      </div>
	<?php } else if ($_GET['error']) { ?>
	<div class="alert-message error" id="message" style="margin-top:43px">
		<a class="close" href="#" onclick="javascript:closeMsg();">&times;</a>
		<p><?php echo htmlspecialchars($_GET['error']) ?></p>
	</div>
	<?php
	}

	$mapmarginright = 0;
	$mapmargintop = 40;
	if ($_GET['message'] || $_GET['error'])
		$mapmargintop = 81;
	?>
	<div style="position:absolute; top:<?php echo $mapmargintop?>px; bottom:0px; left:0px; right:<?php echo $mapmarginright?>px;" id="map" ></div>
	<div id="mapkey">

		<div class="modal" style="position: relative; top: auto; left: auto; margin: 0 auto; width: 256px;">
          <div class="modal-header">
            <h3>Legende</h3>
			<a href="#" onclick="javascript:togglemapkey();" class="close">&times;</a>
          </div>
          <div class="modal-body">
			<ul class="unstyled">
			  <?php if ($loginok==0) { ?>
				<li>Plakate werden erst nachdem Login editierbar.</li>
				<li>Lokaler oder Wiki-Login ist möglich!</li>
			  <? } else {	?>

<?php if ($is_mac_os) {?>
						<li>CMD+Mausklick: neuer Marker</li>
<?php } else { ?>
						<li>Strg+Mausklick: neuer Marker</li>
<?php } ?>
			  <?php } ?>
			  <ul>
<?php foreach ($options as $key=>$value)
{
	if ($value!="") {
?>
		<li><img  style="vertical-align:text-top;" src="./images/markers/<?php echo $key?>.png" width="20" alt="<?php echo $key?>" />=<?php echo $value?></li>
<?php
	}
} ?></ul>
			</ul>
          </div>
        </div>
	</body>
</html>
