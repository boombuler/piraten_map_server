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

if ($loginok==0)
{
	echo "Please Login!";
	exit();
}
$id = preg_replace("/[^a-zA-Z0-9]+/","",$_GET[id]);
if (!$id)
{
	echo "Please provide ID!";
	exit();
}

header("Content-type: image/jpg");
$img = imagecreatefromjpeg ("uploads/plakat_".$id.".jpg");
$img = resizeToWidth("480", $img);
imagejpeg($img);

function resizeToWidth($width, $img) {
	$ratio = $width / imagesx($img);
	$height = imagesy($img) * $ratio;
	return resize($width,$height,$img);
}

function resize($width,$height,$img) {
	$new_image = imagecreatetruecolor($width, $height);
	imagecopyresampled($new_image, $img, 0, 0, 0, 0, $width, $height, imagesx($img), imagesy($img));
	return $new_image;   
}


?>
