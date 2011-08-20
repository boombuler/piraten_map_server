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
		map_add(preg_replace("/,/",".",get_float('lon')),
			preg_replace("/,/",".",get_float('lat')),
			get_typ('typ'));
	case 'del':
		map_del(get_int('id'));
	case 'change':
		map_change(get_int('id'), get_typ('type'));
	case 'addcomment':
		map_addcomment(get_int('id'), $_GET['comment'], $_GET['image']);
	}
}

$rs = mysql_query("SELECT id,lon,lat,type,user,timestamp,comment,image FROM (SELECT * FROM (SELECT * FROM ".$tbl_prefix."felder ORDER BY timestamp DESC) AS sort_felder GROUP BY id) as clean_felder WHERE del!='1' ORDER BY timestamp ASC") OR DIE("Database ERROR");

while($obj = mysql_fetch_object($rs))
{
$arr[] = $obj;
}
print json_encode($arr);
