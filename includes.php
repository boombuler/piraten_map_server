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

include "Snoopy.class.php";
include "settings.php";

function get_inner_html( $node ) { 
    $innerHTML= ''; 
    $children = $node->childNodes; 
    foreach ($children as $child) { 
        $innerHTML .= $child->ownerDocument->saveXML( $child ); 
    } 

    return $innerHTML; 
} 

$snoopy = new Snoopy;

if ($use_ssl) {
  $snoopy->curl_path=$curl_path;
  $wikiPath = "https://wiki.piratenpartei.de";
} else {
  $snoopy->curl_path=false;
  $wikiPath = "http://wiki.piratenpartei.de";
}
$apiPath = "$wikiPath/wiki/api.php";

session_start();



$options = array("default"=>"",
	"plakat_ok"=>'Plakat hängt',
	"plakat_a0"=>'A0-Plakat steht',
	"plakat_dieb"=>'Plakat wurde gestohlen',
	"plakat_niceplace"=>'Gute Stelle für ein Plakat',
	"plakat_wrecked"=>'Plakat beschädigt',
	"wand"=>'Plakatwand der Gemeinde',
	"wand_ok"=>'Plakat an der Plakatwand');

$image_upload_typ = 'plakat_ok';


$db = mysql_connect($mysql_server, $mysql_user, $mysql_password);
mysql_selectdb($mysql_database);
mysql_query("SET character_set_connection = utf8");
mysql_query("SET character_set_results = utf8");
mysql_query("SET character_set_client = utf8");
setlocale(LC_ALL, 'de_DE.UTF-8');

if ($_SESSION['siduser'] || $_SESSION['sidip']) {
	// Check if the session is still valid.
	if ($_SESSION['wikisession']) {
		$snoopy->cookies = $_SESSION['wikisession'];

		$request_vars = array('action' => 'query', 'meta' => 'userinfo',  'format' => 'php');
		if(!$snoopy->submit($apiPath, $request_vars))
			die("Snoopy error: {$snoopy->error}");
		$array = unserialize($snoopy->results);

		if ($_SESSION['siduser'] == $array[query][userinfo][name] && $_SESSION['sidip']==$_SERVER["REMOTE_ADDR"])
			$loginok=1;
		else
		{
			$loginok=0;
			unset($_SESSION['siduser']);
			unset($_SESSION['wikisession']);
			unset($_SESSION['sidip']);
		}
	} else {
		if ($_SESSION['sidip']==$_SERVER["REMOTE_ADDR"])
			$loginok=1;
		else
		{
			$loginok=0;
			unset($_SESSION['siduser']);
			unset($_SESSION['wikisession']);
			unset($_SESSION['sidip']);
		}
       }
}

function dieDB() {
    global $debug;
    if ($debug)
      DIE(mysql_error());
    else
      DIE("Error!");
}

function mysql_escape($value) {
	if (get_magic_quotes_gpc())
		$value = stripslashes($value);
	return mysql_real_escape_string($value);
}

function get_float($name) {
  return filter_input(INPUT_GET, $name, FILTER_VALIDATE_FLOAT);
}

function get_int($name) {
  return filter_input(INPUT_GET, $name, FILTER_VALIDATE_INT);
}
function get_typ($typ) {
	global $options;
	$t = $_GET[$typ];
	if (!($t))
		return null;
	foreach($options as $key=>$value) {
		if ($t == $key) {
			return $t;
		}
	}
}

function request_location($lon, $lat) {
        $src = new DOMDocument('1.0', 'utf-8');
        $src->formatOutput = true;
        $src->preserveWhiteSpace = false;
        $src->load("http://nominatim.openstreetmap.org/reverse?format=xml&zoom=18&addressdetails=1&lon=".$lon."&lat=".$lat);
        $city = get_inner_html($src->getElementsByTagName('city')->item(0));
        $street = get_inner_html($src->getElementsByTagName('road')->item(0));
        return array( "city" => mysql_escape($city), "street" =>  mysql_escape($street));
}

function map_add($lon, $lat, $typ) {
	global $tbl_prefix, $_SESSION;
	
	$lon = mysql_escape($lon);
	$lat = mysql_escape($lat);
	$typ = mysql_escape($typ);
	
	$location = request_location($lon, $lat);

	$city = $location["city"];
	$street = $location["street"];
	
	if ($typ != '')
		$res = mysql_query("INSERT INTO ".$tbl_prefix."felder (lon,lat,user,type,city,street) VALUES ('".$lon."','".$lat."','".$_SESSION['siduser']."','".$typ."','".$city."','".$street."');") OR dieDB();
	else
		$res = mysql_query("INSERT INTO ".$tbl_prefix."felder (lon,lat,user,city,street) VALUES ('".$lon."','".$lat."','".$_SESSION['siduser']."','".$city."','".$street."');") OR dieDB();
	
	$id = mysql_insert_id();
	$res = mysql_query("INSERT INTO ".$tbl_prefix."plakat (actual_id, del) VALUES('".$id."',false)") OR dieDB();
	$pid = mysql_insert_id();
	mysql_query("UPDATE ".$tbl_prefix."felder SET plakat_id = $pid WHERE id = $id") or dieDB();
	$res = mysql_query("INSERT INTO ".$tbl_prefix."log (plakat_id, user, subject) VALUES('".$pid."','".$_SESSION['siduser']."','add')") OR dieDB();
	return $pid;
}

function map_del($id) {
	global $tbl_prefix, $_SESSION;
	
	$id = mysql_escape($id);
	
	$res = mysql_query("UPDATE ".$tbl_prefix."plakat SET del = true where id = $id") OR dieDB();
	
	$res = mysql_query("INSERT INTO ".$tbl_prefix."log (plakat_id, user, subject) VALUES('".$id."','".$_SESSION['siduser']."','del')") OR dieDB();
	return;
}

function map_change($id, $type, $comment, $city, $street, $imageurl) {
	global $tbl_prefix, $_SESSION, $options;
	
	$id = mysql_escape($id);
	$query = "INSERT INTO ".$tbl_prefix."felder (plakat_id, lon, lat, user, type, comment, city, street, image) "
               . "SELECT $id as plakat_id, lon, lat, '".$_SESSION['siduser']."' as user, ";
	if(isset($options[$type])) {
		$type = mysql_escape($type);
		$query .= "'$type' as type, ";
	} else $query .= "type, ";
	if ($comment !== null) {
		$comment = mysql_escape($comment);
		$query .= "'$comment' as comment, ";
	} else $query .= "comment, ";
	if($city !== null) {	  
		$city = mysql_escape($city);
		$query .= "'$city' as city, ";
	} else $query .= "city, ";
	if($street !== null) {	  
		$street = mysql_escape($street);
		$query .= "'$street' as street, ";
	} else $query .= "street, ";
	if($imageurl !== null) {	  
		$imageurl = mysql_escape($imageurl);
		$query .= "'$imageurl' as image ";
	} else $query .= "image ";

	$query .= " FROM ".$tbl_prefix."felder WHERE id in (SELECT actual_id from ".$tbl_prefix."plakat where id = $id)";

	$res = mysql_query($query) OR dieDB();
	$newid = mysql_insert_id();

	$res = mysql_query("INSERT INTO ".$tbl_prefix."log (plakat_id, user, subject, what) VALUES('".$id."','".$_SESSION['siduser']."','change', 'Type: ".$type."')") OR dieDB();

	mysql_query("UPDATE ".$tbl_prefix."plakat SET actual_id = $newid where id = $id");
	
	return;
}

function getPWHash($user, $pass) {
    return md5(strtolower($user).":".$pass);
}

function errorMsgHeader($msg) {
	return "Location: ./?error=".urlencode($msg);
}

function infoMsgHeader($msg) {
	return "Location: ./?message=".urlencode($msg);
}

?>
