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
header('content-type: text/html; charset=utf-8');
require("includes.php");

$mobile = strpos($_SERVER['HTTP_USER_AGENT'],"iPhone") || strpos($_SERVER['HTTP_USER_AGENT'],"Android") || strpos($_SERVER['HTTP_USER_AGENT'],"iPod") || strpos($_SERVER['HTTP_USER_AGENT'],"iPad")
	 || strpos($_SERVER['HTTP_USER_AGENT'],"webOS");

if($_GET["view"] == "mobile")
  $mobile = true;
if($_GET["view"] == "desktop")
  $mobile = false;

if ($mobile)
  require('index-mobile.php');
else
  require('index-desktop.php');
?>