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
		$lon = preg_replace("/,/",".",$_GET['lon']);
		$lat = preg_replace("/,/",".",$_GET['lat']);
		$typ = preg_replace("/,/",".",$_GET['typ']);
		if ($typ != '')
		  $res = mysql_query("INSERT INTO ".$tbl_prefix."felder (lon,lat,user,type) VALUES ('".$lon."','".$lat."','".$_SESSION['siduser']."', '".$typ."');") OR DIE("Database ERROR");
		else
		  $res = mysql_query("INSERT INTO ".$tbl_prefix."felder (lon,lat,user) VALUES ('".$lon."','".$lat."','".$_SESSION['siduser']."');") OR DIE("Database ERROR");
	case 'del':
		//$res = mysql_db_query("piraten", "DELETE FROM felder WHERE id = '".$_GET['id']."' LIMIT 1") OR DIE("Database ERROR");
		$res = mysql_query("UPDATE ".$tbl_prefix."felder SET del='1',user='".$_SESSION['siduser']."' WHERE id = '".$_GET['id']."'") OR DIE("Database ERROR");
	case 'change':
		$type = $_GET['type'];
		if (isset($options[$type]))
		{
			$res = mysql_query("UPDATE ".$tbl_prefix."felder SET type='".$type."',user='".$_SESSION['siduser']."' WHERE id = '".$_GET['id']."'") OR DIE("Database ERROR");
		}
	case 'addcomment':
		$comment = $_GET['comment'];
		$comment = htmlentities($comment);
		$image   = $_GET['image'];
		$image   = htmlentities($image);
		//print $comment;	
		$res = mysql_query( "UPDATE ".$tbl_prefix."felder SET comment='".$comment."',user='".$_SESSION['siduser']."', image='".$image."' WHERE id = '".$_GET['id']."'") OR DIE("Database ERROR");
	}
}
$rs = mysql_query("SELECT id,lon,lat,type,user,timestamp,comment,image FROM ".$tbl_prefix."felder WHERE del!='1' ORDER BY timestamp ASC") OR DIE("Database ERROR");
while($obj = mysql_fetch_object($rs))
{
$arr[] = $obj;
}
print json_encode($arr);
