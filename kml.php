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
$params = array();

if ($filter) {
  $filterstr = " AND type = :type";
  $params['type'] = $filter;
}

$bbox = $_GET['bbox'];
if ($bbox) {
    list($bbe, $bbn, $bbw, $bbs) = split(",", $bbox);
    $params['bbe'] = $bbe;
    $params['bbw'] = $bbw;
    $params['bbs'] = $bbs;
    $params['bbn'] = $bbn;
    $filterstr .= " AND (f.lon >= :bbe) AND (f.lon <= :bbw) AND (f.lat >= :bbn) AND (f.lat <= :bbs)";
}


$query = "SELECT p.id, f.lon, f.lat, f.type, f.user, f.timestamp, f.comment, f.city, f.street, f.image "
      . " FROM ".$tbl_prefix."felder f JOIN ".$tbl_prefix."plakat p on p.actual_id = f.id"
      . " WHERE p.del != true".$filterstr;
$db = openDB();

$sql = $db->prepare($query);
$sql->execute($params);
$result = $sql->fetchAll();
foreach($result as $obj) {
    $id  = $obj->id;

    $lon = $obj->lon;
    $arr = preg_split("/\./", $lon);
    $ar2 = str_split($arr[1],6);
    $lon = $arr[0].".".$ar2[0];

    $lat = $obj->lat;
    $arr = preg_split("/\./", $lat);
    $ar2 = str_split($arr[1],6);
    $lat = $arr[0].".".$ar2[0];

    $type= $obj->type;
    $user= $obj->user;
    $time= $obj->timestamp;
    $comment = $obj->comment;

    if ($comment == null)
        $comment = "";
    $city = $obj->city;
    if ($city == null)
        $city = "";
    $street = $obj->street;
    if ($street == null)
        $street = "";
    $image   = $obj->image;
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
$db = null;
echo $dom->saveXML();
?>
