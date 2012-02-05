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
				get_typ('typ'));
			return;
		case 'del':
			map_del(get_int('id'));
			return;
		case 'change':
			$id = get_int('id');
			$comment = "".$_GET['comment'];
			$image = "".$_GET['image'];
			map_change($id, get_typ('type'), $comment , $image);
			return;
	}
}

$filter    = get_typ('filter');

echo '<?xml version="1.0" encoding="UTF-8"?>';?>
<kml xmlns="http://www.opengis.net/kml/2.2"><Document><name>PIRATEN</name><description><![CDATA[PIRATEN Wahlkampf Hilfe]]></description><?php
$styles = array();
$i = 0;
foreach($options as $key=>$value)
{
  $styleKey = "s$i";
  $i++;
  $styles[$key] = $styleKey;
  if (!($filter) || ($filter == $key)) {
?><Style id="<?php echo $styleKey?>"><IconStyle><hotSpot x="0.5" y="0.5" xunits="fraction" yunits="fraction" /><scale>0.6</scale><Icon><href>./images/markers/<?php echo $key?>.png</href></Icon></IconStyle></Style><?php
  }
}
?>
<?php

$filterstr = "";
if ($filter) {
  $filterstr = " AND type = '".mysql_escape($filter)."'";
}

$query = "SELECT p.id, f.lon, f.lat, f.type, f.user, f.timestamp, f.comment, f.image "
      . " FROM ".$tbl_prefix."felder f JOIN ".$tbl_prefix."plakat p on p.actual_id = f.id"
      . " WHERE p.del != true".$filterstr;

$res = mysql_query($query) OR dieDB();
$num = mysql_num_rows($res);

for ($i=0;$i<$num;$i++)
{
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
	$image   = mysql_result($res, $i, "image");
	if ($image == "")
		$image = null;
?><Placemark><name><?php echo $id?></name><description><![CDATA[<?php 
echo json_encode(array(
	'id'=>$id,
	't'=>$type, 
	'tb'=>$options[$type],
	'i'=>htmlspecialchars($image),
	'c'=>htmlspecialchars($comment),
	'u'=>htmlspecialchars($user),
	'd'=>date('d.m.y H:i', strtotime($time))
));
?>]]></description><?php
if (isset($options[$type]))
{
	echo "<styleUrl>#".$styles[$type]."</styleUrl>";
}
?><Point><coordinates><?php echo $lon?>,<?php echo $lat?>,0.000000</coordinates></Point></Placemark><?php
}
?></Document></kml>
