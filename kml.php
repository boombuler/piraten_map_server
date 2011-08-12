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

require("includes.php");

if (($loginok==0) and !$allow_view_public)
	exit();

if ($loginok!=0) 
{
	switch ($_GET['action'])
	{
	case 'add':
		$lon = mysql_real_escape_string(preg_replace("/,/",".",$_GET['lon']));
		$lat = mysql_real_escape_string(preg_replace("/,/",".",$_GET['lat']));
		$typ = mysql_real_escape_string(preg_replace("/,/",".",$_GET['typ']));
		if ($typ != '')
		  $res = mysql_query("INSERT INTO ".$tbl_prefix."felder (lon,lat,user,type) VALUES ('".$lon."','".$lat."','".$_SESSION['siduser']."', '".$typ."');") OR DIE("Database ERROR");
		else
		  $res = mysql_query("INSERT INTO ".$tbl_prefix."felder (lon,lat,user) VALUES ('".$lon."','".$lat."','".$_SESSION['siduser']."');") OR DIE("Database ERROR");
		return;
	case 'del':
		$res = mysql_query("UPDATE ".$tbl_prefix."felder SET del='1',user='".$_SESSION['siduser']."' WHERE id = '".mysql_real_escape_string($_GET['id'])."'") OR DIE("Database ERROR");
		return;
	case 'change':
		$type = mysql_real_escape_string($_GET['type']);
		if (isset($options[$type]))
		{
			$res = mysql_query("UPDATE ".$tbl_prefix."felder SET type='".$type."',user='".$_SESSION['siduser']."' WHERE id = '".mysql_real_escape_string($_GET['id'])."'") OR DIE("Database ERROR");
		}
		return;
	case 'addcomment':
		$comment = $_GET['comment'];
		$comment = mysql_real_escape_string(htmlentities($comment));
		$image   = $_GET['image'];
		$image   = mysql_real_escape_string(htmlentities($image));
		//print $comment;	
		$res = mysql_query( "UPDATE ".$tbl_prefix."felder SET comment='".$comment."',user='".$_SESSION['siduser']."', image='".$image."' WHERE id = '".mysql_real_escape_string($_GET['id'])."'") OR DIE("Database ERROR");

		return;
	}
}

$filter    = preg_replace("/,/",".",$_GET['filter']);

echo '<?xml version="1.0" encoding="UTF-8"?>'?>

<kml xmlns="http://www.opengis.net/kml/2.2">
 <Document>
  <name>PIRATEN</name>
  <description><![CDATA[PIRATEN Wahlkampf Hilfe]]></description>
<?php
foreach($options as $key=>$value)
{
  if (!($filter) || ($filter == $key)) {
?>
  <Style id="<?php echo $key?>">
    <IconStyle>
	  <hotSpot x="0.5" y="0.5" xunits="fraction" yunits="fraction" />
	  <scale>0.6</scale>
      <Icon>
      <href>./images/markers/<?php echo $key?>.png</href>
      </Icon>
    </IconStyle>
  </Style>
<?php
  }
}
?>
<?php

$filterstr = "";
if ($filter) {
  $filterstr = " AND type = '".mysql_real_escape_string($filter)."'";
}
$res = mysql_query("SELECT id,lon,lat,type,user,timestamp,comment,image FROM ".$tbl_prefix."felder WHERE del!='1' ".$filterstr." ORDER BY timestamp ASC") OR DIE("Database ERROR");
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
	$image   = mysql_result($res, $i, "image");
?>
  <Placemark>
    <name><?php if ($type) {echo $options[$type]." - ";} echo $id?></name>
<?php //    <description><![CDATA[<div dir="ltr">Größer Pau erreichen.</div>]]></description> ?>
<description><![CDATA[<div dir="ltr"><ul>
<?php
if ($loginok!=0)
{
	foreach ($options as $key=>$value)
	{
		if ($key != 'default') {
	?>
		<li><a href="javascript:chanteTyp(<?php echo $id?>, '<?php echo $key?>')"><?php echo $value?></a></li>
	<?php
		}
	}
?>
<br>
<li><a href="javascript:delid(<?php echo $id?>)">löschen</a></li>
</ul>
<?php
}
 if ($image) { ?>
<a target="_blank" href="<?php echo $image?>"><img class="photo" src="<?php echo $image?>"></a><br>
<?php } ?>
<textarea rows="2" cols="30" name="comment[<?php echo $id?>]" id="comment[<?php echo $id?>]">
<?php echo $comment?>
</textarea>
<?php
  if ($loginok!=0) { ?>
<br />Bild URL:<br /><input type="text" class="phototxt" name="image[<?php echo $id?>]" id="image[<?php echo $id?>]" value="<?php echo $image?>" />
<input type="button" value="Speichern" onclick="javascript:addcomment(<?php echo $id?>)">
<?php } ?>
<br>
Von <?php echo $user?>, zuletzt am: <?php echo $time?></div>]]></description>
<?php
if (isset($options[$type]))
{
	echo "<styleUrl>#".$type."</styleUrl>\r\n";
}
?>
    <Point>
      <coordinates><?php echo $lon?>,<?php echo $lat?>,0.000000</coordinates>
    </Point>
  </Placemark>

<?php
}
?>
 </Document>
</kml>
