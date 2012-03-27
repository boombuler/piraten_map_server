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
if (! session_id() == "")
  session_start();
$_SESSION['siduser'] = "CRONJOB";
$_SESSION['sidip'] = $_SERVER["REMOTE_ADDR"];

require("includes.php");

$query = "SELECT p.id, f.lon, f.lat "
      . " FROM ".$tbl_prefix."felder f JOIN ".$tbl_prefix."plakat p on p.actual_id = f.id"
      . " WHERE p.del != true and f.street is null and f.city is null LIMIT 0, $max_resolve_count";

$rs = mysql_query($query) OR dieDB();
while($obj = mysql_fetch_object($rs))
{
        $location = request_location($obj->lon, $obj->lat);

        $city = $location["city"];
        $street = $location["street"];

        map_change($obj->id, null, null, $city, $street, null);
}

?>