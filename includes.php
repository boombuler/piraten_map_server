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

require_once("Snoopy.class.php");
require_once("settings.php");
require_once("dbcon.php");

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
        return array( "city" => $city, "street" =>  $street);
}

function map_add($lon, $lat, $typ, $resolveAdr) {
    global $tbl_prefix, $_SESSION;

    $city = "null";
    $street = "null";
    if ($resolveAdr) {
        $location = request_location($lon, $lat);
        $city = "'".$location["city"]."'";
        $street = "'".$location["street"]."'";
    }

    $db = openDB();

    if ($typ != '') {
        $sql = $db->prepare("INSERT INTO ".$tbl_prefix."felder (lon, lat, user, type, city, street) VALUES (:lon, :lat, :user, :type, :city, :street)");
        $sql->bindValue("type", $typ);
    } else {
        $sql = $db->prepare("INSERT INTO ".$tbl_prefix."felder (lon, lat, user, city, street) VALUES (:lon, :lat, :user, :city, :street)");
    }

    $sql->bindValue("lon", $lon);
    $sql->bindValue("lat", $lat);
    $sql->bindValue("user", $_SESSION['siduser']);
    $sql->bindValue("city", $city);
    $sql->bindValue("street", $street);
    $sql->execute();
    $id = $db->lastInsertId();

    $db->prepare("INSERT INTO ".$tbl_prefix."plakat (actual_id, del) VALUES (?, false)")
       ->execute(array($id));

    $pid = $db->lastInsertId();

    $db->prepare("UPDATE ".$tbl_prefix."felder SET plakat_id = ? WHERE id = ?")
       ->execute(array($pid, $id));

    $db->prepare("INSERT INTO ".$tbl_prefix."log (plakat_id, user, subject) VALUES(?, ?, ?)")
       ->execute(array($pid, $_SESSION['siduser'], "add"));

    $db = null;
    return $pid;
}

function map_del($id) {
    global $tbl_prefix, $_SESSION;
    $db = openDB();

    $db->prepare("UPDATE ".$tbl_prefix."plakat SET del = true where id = ?")
       ->execute(array($id));

    $db->prepare("INSERT INTO ".$tbl_prefix."log (plakat_id, user, subject) VALUES (?,?,?)")
       ->execute(array($id, $_SESSION['siduser'], "del"));
    $db = null;
}

function map_change($id, $type, $comment, $city, $street, $imageurl) {
    global $tbl_prefix, $_SESSION, $options;

    $db = openDB();

    $query = "INSERT INTO ".$tbl_prefix."felder (plakat_id, lon, lat, user, type, comment, city, street, image) " .
             "SELECT :pid as plakat_id, lon, lat, :user as user, ";
    $params = array(
        'pid' => $id,
        'user' => $_SESSION['siduser']
    );

    if(isset($options[$type])) {
        $params['type'] = $type;
        $query .= ":type as type, ";
    } else $query .= "type, ";
    if ($comment !== null) {
        $params['comment'] = $comment;
        $query .= ":comment as comment, ";
    } else $query .= "comment, ";
    if($city !== null) {
        $params['city'] = $city;
        $query .= ":city as city, ";
    } else $query .= "city, ";
    if($street !== null) {
        $params['street'] = $street;
        $query .= ":street as street, ";
    } else $query .= "street, ";
    if($imageurl !== null) {
        $params['img'] = $imageurl;
        $query .= ":img as image ";
    } else $query .= "image ";

    $query .= " FROM ".$tbl_prefix."felder WHERE id in (SELECT actual_id from ".$tbl_prefix."plakat where id = :pid)";

    $db->prepare($query)->execute($params);

    $newid = $db->lastInsertId();

    $db->prepare("INSERT INTO ".$tbl_prefix."log (plakat_id, user, subject, what) VALUES (?, ?, ?, ?)")
       ->execute(array($id, $_SESSION['siduser'], 'change', 'Type: '.$type));

    $db->prepare("UPDATE ".$tbl_prefix."plakat SET actual_id = ? where id = ?")
       ->execute(array($newid, $id));

    $db = null;
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

function isAdmin() {
    global $_SESSION;
    if (isset($_SESSION['siduser'])) {
        return $_SESSION['admin'] === true;
    }
    return false;
}

?>
