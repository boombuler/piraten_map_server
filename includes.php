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
	"plakat_dieb"=>'Plakat wurde gestohlen',
	"plakat_niceplace"=>'Gute Stelle für ein Plakat',
	"wand"=>'Plakatwand der Gemeinde',
	"wand_ok"=>'Plakat an der Plakatwand');


$db = mysql_connect($mysql_server, $mysql_user, $mysql_password);
mysql_selectdb($mysql_database);
mysql_query("SET character_set_connection = utf8");
mysql_query("SET character_set_results = utf8");
mysql_query("SET character_set_client = utf8");
setlocale(LC_ALL, 'de_DE.UTF-8');

if ($_SESSION['siduser'] || $_SESSION['sidip'])
{
       if ($_SESSION['wikisession'])
       {
	$snoopy->cookies = $_SESSION['wikisession'];

	$request_vars = array('action' => 'query', 'meta' => 'userinfo', 
		'format' => 'php');
	if(!$snoopy->submit($apiPath, $request_vars))
		die("Snoopy error: {$snoopy->error}");
	$array = unserialize($snoopy->results);

	if ($_SESSION['siduser'] == $array[query][userinfo][name] && $_SESSION['sidip']==$_SERVER["REMOTE_ADDR"])
	{
		$loginok=1;
	}
	else
	{
		$loginok=0;
		unset($_SESSION['siduser']);
		unset($_SESSION['wikisession']);
//		unset($_SESSION['sidpassword']);
		unset($_SESSION['sidip']);
	}
       }
       else if ($_SESSION['sidpassword'])
       {
	$res = mysql_query("SELECT password FROM ".$tbl_prefix."users WHERE username='".$_SESSION['siduser']."' AND password='".$_SESSION['sidpassword']."'");
	$num = mysql_num_rows($res);
	if ($num==1 && $_SESSION['sidip']==$_SERVER["REMOTE_ADDR"])
	{
		$loginok=1;
	}
	else
	{
		$loginok=0;
		unset($_SESSION['siduser']);
//		unset($_SESSION['wikisession']);
		unset($_SESSION['sidpassword']);
		unset($_SESSION['sidip']);
	}

       }
       else
       {
		$loginok=0;
		unset($_SESSION['siduser']);
		unset($_SESSION['wikisession']);
		unset($_SESSION['sidpassword']);
		unset($_SESSION['sidip']);
       }
 
}

function map_add($lon, $lat, $typ) {
	global $tbl_prefix, $_SESSION;
	
	$lon = mysql_real_escape_string($lon);
	$lat = mysql_real_escape_string($lat);
	$typ = mysql_real_escape_string($typ);
	
	if ($typ != '')
		$res = mysql_query("INSERT INTO ".$tbl_prefix."felder (lon,lat,user,type) VALUES ('".$lon."','".$lat."','".$_SESSION['siduser']."', '".$typ."');") OR DIE("Database ERROR");
	else
		$res = mysql_query("INSERT INTO ".$tbl_prefix."felder (lon,lat,user) VALUES ('".$lon."','".$lat."','".$_SESSION['siduser']."');") OR DIE("Database ERROR");

	return;
}

function map_del($id) {
	global $tbl_prefix, $_SESSION;
	
	$id = mysql_real_escape_string($id);
	
	$res = mysql_query("UPDATE ".$tbl_prefix."felder SET del='1',user='".$_SESSION['siduser']."' WHERE id = '".$id."'") OR DIE("Database ERROR");
	
	return;
}

function map_change($id, $type) {
	global $tbl_prefix, $_SESSION, $options;
	
	$id = mysql_real_escape_string($id);
	$type = mysql_real_escape_string($type);
	
	if (isset($options[$type]))
	{
		$res = mysql_query("UPDATE ".$tbl_prefix."felder SET type='".$type."',user='".$_SESSION['siduser']."' WHERE id = '".$id."'") OR DIE("Database ERROR");
	}
	
	return;
}

function map_addcomment($id, $comment, $image) {
	global $tbl_prefix, $_SESSION;
	
	$id = mysql_real_escape_string($id);
	$comment = mysql_real_escape_string(htmlentities($comment));
	$image = mysql_real_escape_string(htmlentities($image));
	
	$res = mysql_query( "UPDATE ".$tbl_prefix."felder SET comment='".$comment."',user='".$_SESSION['siduser']."', image='".$image."' WHERE id = '".$id."'") OR DIE("Database ERROR");
	
	return;
}

?>
