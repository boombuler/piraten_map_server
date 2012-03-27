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
ob_start("ob_gzhandler");
require("includes.php");

if (($loginok==0) and !$allow_view_public)
	exit();

if ($loginok!=0) {
	switch ($_GET['action']) {
		case 'add':
			map_add(preg_replace("/,/",".",get_float('lon')),
				preg_replace("/,/",".",get_float('lat')),
				get_typ('typ'), true);
			return;
		case 'del':
			map_del(get_int('id'));
			return;
		case 'change':
			$id = get_int('id');
			$comment = "".$_GET['comment'];
			$city = "".$_GET['city'];
			$street = "".$_GET['street'];
			$image = "".$_GET['image'];
			map_change($id, get_typ('type'), $comment, $city, $street, $image);
			return;
	}
}

$filter    = get_typ('filter');

$dom = new DOMDocument('1.0', 'UTF-8');
$nodeKml = $dom->appendChild($dom->createElementNS('http://www.opengis.net/kml/2.2', 'kml'));

$nodeDoc = $nodeKml->appendChild($dom->createElement('Document'));
$nodeDoc->appendChild($dom->createElement('name', 'PIRATEN'));

$nodeDoc->appendChild($dom->createElement('description'))->appendChild($dom->createCDATASection('Piraten Plakate'));

// Define the styles
$styles = array();
$i = 0;
foreach($options as $key=>$value) {
	$styleKey = "s$i";
	$i++;
	$styles[$key] = $styleKey;
	if (!($filter) || ($filter == $key)) {
		$nStyle = $nodeDoc->appendChild($dom->createElement('Style'));
		$nStyle->setAttribute('id', $styleKey);
		$nIconS = $nStyle->appendChild($dom->createElement('IconStyle'));
		$nHotSpot = $nIconS->appendChild($dom->createElement('hotSpot'));
		$nHotSpot->setAttribute('x', '0.5');
		$nHotSpot->setAttribute('y', '0.5');
		$nHotSpot->setAttribute('xunits', 'fraction');
		$nHotSpot->setAttribute('yunits', 'fraction');
		$nIconS->appendChild($dom->createElement('scale', '0.6'));
		$nIconS->appendChild($dom->createElement('Icon'))->appendChild($dom->createElement('href', "./images/markers/$key.png"));
	}
}

$filterstr = "";
if ($filter) {
  $filterstr = " AND type = '".mysql_escape($filter)."'";
}
$bbox = mysql_escape($_GET['bbox']);
if ($bbox) {
	list($bbe, $bbn, $bbw, $bbs) = split(",", $bbox);
	$filterstr .= " AND (f.lon >= $bbe) AND (f.lon <= $bbw) AND (f.lat >= $bbn) AND (f.lat <= $bbs)";
}


$query = "SELECT p.id, f.lon, f.lat, f.type, f.user, f.timestamp, f.comment, f.city, f.street, f.image "
      . " FROM ".$tbl_prefix."felder f JOIN ".$tbl_prefix."plakat p on p.actual_id = f.id"
      . " WHERE p.del != true".$filterstr;

$res = mysql_query($query) OR dieDB();
$num = mysql_num_rows($res);

for ($i=0;$i<$num;$i++) {
	$id  = mysql_result($res, $i, "id");
	
	$lon = mysql_result($res, $i, "lon");
	$arr = preg_split("/\./", $lon);
	$ar2 = str_split($arr[1],6);
	$lon = $arr[0].".".$ar2[0];
	
	$lat = mysql_result($res, $i, "lat");
	$arr = preg_split("/\./", $lat);
	$ar2 = str_split($arr[1],6);
	$lat = $arr[0].".".$ar2[0];
	
	$type= mysql_result($res, $i, "type");
	
	$user= mysql_result($res, $i, "user");
	
	$time= mysql_result($res, $i, "timestamp");
	
	$comment = mysql_result($res, $i, "comment");
	
	if ($comment == null)
		$comment = "";
	$city = mysql_result($res, $i, "city");
	if ($city == null)
		$city = "";
	$street = mysql_result($res, $i, "street");
	if ($street == null)
		$street = "";
	$image   = mysql_result($res, $i, "image");
	if ($image == "")
		$image = null;
	
	$place = $nodeDoc->appendChild($dom->createElement('Placemark'));
	$place->appendChild($dom->createElement('name', $id));
	$place->appendChild($dom->createElement('description'))->appendChild(
		$dom->createCDATASection(json_encode(array(
			'id'=>$id,
			't'=>$type, 
			'tb'=>$options[$type],
			'i'=>htmlspecialchars($image),
			'c'=>htmlspecialchars($comment),
			'ci'=>htmlspecialchars($city),
			's'=>htmlspecialchars($street),
			'u'=>htmlspecialchars($user),
			'd'=>date('d.m.y H:i', strtotime($time))
		))));
	if (isset($options[$type]))
		$place->appendChild($dom->createElement('styleUrl', '#'.$styles[$type]));
	$place->appendChild($dom->createElement('Point'))->appendChild($dom->createElement('coordinates', "$lon,$lat"));
}
echo $dom->saveXML();	
?>
