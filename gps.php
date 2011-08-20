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

function read_latlon($img)
{
	$arr = exif_read_data($img);
	if (!$arr)
		return -1;

	// Lat
	$lat_g = preg_split("/\//",($arr[GPSLatitude][0]));
	if (!$lat_g || $lat_g[1]==0)
		return -1;
	$lat_g = $lat_g[0]/$lat_g[1];

	$lat_m = preg_split("/\//",($arr[GPSLatitude][1]));
	if (!$lat_m || $lat_m[1]==0)
		return -1;
	$lat_m = $lat_m[0]/$lat_m[1];

	$lat_s = preg_split("/\//",($arr[GPSLatitude][2]));
	if (!$lat_s || $lat_s[1]==0)
		return -1;
	$lat_s = $lat_s[0]/$lat_s[1];

	$lat = $lat_g + ($lat_m/60) + ($lat_s/60/60);

	// Lon
	$lon_g = preg_split("/\//",($arr[GPSLongitude][0]));
	if (!$lon_g || $lon_g[1]==0)
		return -1;
	$lon_g = $lon_g[0]/$lon_g[1];

	$lon_m = preg_split("/\//",($arr[GPSLongitude][1]));
	if (!$lon_m || $lon_m[1]==0)
		return -1;
	$lon_m = $lon_m[0]/$lon_m[1];

	$lon_s = preg_split("/\//",($arr[GPSLongitude][2]));
	if (!$lon_s || $lon_s[1]==0)
		return -1;
	$lon_s = $lon_s[0]/$lon_s[1];

	$lon = $lon_g + ($lon_m/60) + ($lon_s/60/60);

	return array($lat, $lon);
}
?>
