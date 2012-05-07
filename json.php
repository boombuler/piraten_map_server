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

if ($loginok!=0) 
{
	switch ($_GET['action'])
	{
	case 'add':
		map_add(preg_replace("/,/",".",get_float('lon')),
			preg_replace("/,/",".",get_float('lat')),
			get_typ('typ'), true);
		break;
	case 'del':
		map_del(get_int('id'));
		break;
	case 'change':
		map_change(get_int('id'), get_typ('type'), null, null);
		break;
	case 'addcomment':
		map_change(get_int('id'), null, $_GET['comment'], $_GET['image']);
	}
}

$query = "SELECT p.id, f.lon, f.lat, f.type, f.user, f.timestamp, f.comment, f.city, f.street, f.image "
      . " FROM ".$tbl_prefix."felder f JOIN ".$tbl_prefix."plakat p on p.actual_id = f.id"
      . " WHERE p.del != true";

$db = openDB();
$sql = $db->prepare($query);
$sql->execute();

foreach($sql->fetchAll() as $obj) {
    $obj->user    = htmlspecialchars($obj->user);
    $obj->comment = htmlspecialchars($obj->comment);
    $obj->city    = htmlspecialchars($obj->city);
    $obj->street  = htmlspecialchars($obj->street);
    $obj->image   = htmlspecialchars($obj->image);
    $arr[] = $obj;
}
$db = null;
print json_encode($arr);
