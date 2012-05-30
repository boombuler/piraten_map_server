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
require_once('library/System.php');

$image_upload_typ = 'plakat_ok';

setlocale(LC_ALL, 'de_DE.UTF-8');

if ($_SESSION['siduser'] || $_SESSION['sidip']) {
	// Check if the session is still valid.
    if (WikiConnection::isSessionValid() && $_SESSION['sidip']==$_SERVER["REMOTE_ADDR"])
        $loginok=1;
    else
    {
        $loginok=0;
        unset($_SESSION['siduser']);
        unset($_SESSION['sidip']);
    }
}

function get_inner_html( $node ) { 
    $innerHTML= '';
    $children = $node->childNodes; 
    foreach ($children as $child) { 
        $innerHTML .= $child->ownerDocument->saveXML( $child ); 
    }
    return $innerHTML; 
} 

function get_float($name) {
  return filter_input(INPUT_GET, $name, FILTER_VALIDATE_FLOAT);
}

function get_int($name) {
  return filter_input(INPUT_GET, $name, FILTER_VALIDATE_INT);
}

function get_typ($typ) {
	$t = $_GET[$typ];
	if (!($t))
		return null;
	foreach(System::getPosterFlags() as $key=>$value) {
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
    global $_SESSION;

    $city = "null";
    $street = "null";
    if ($resolveAdr) {
        $location = request_location($lon, $lat);
        $city = "'".$location["city"]."'";
        $street = "'".$location["street"]."'";
    }

    $tbl_prefix = System::getConfig('tbl_prefix');

    if ($typ != '') {
        $sql = System::prepare("INSERT INTO ".$tbl_prefix."felder (lon, lat, user, type, city, street) VALUES (:lon, :lat, :user, :type, :city, :street)");
        $sql->bindValue("type", $typ);
    } else {
        $sql = System::prepare("INSERT INTO ".$tbl_prefix."felder (lon, lat, user, city, street) VALUES (:lon, :lat, :user, :city, :street)");
    }

    $sql->bindValue("lon", $lon);
    $sql->bindValue("lat", $lat);
    $sql->bindValue("user", $_SESSION['siduser']);
    $sql->bindValue("city", $city);
    $sql->bindValue("street", $street);
    $sql->execute();
    $id = System::lastInsertId();

    System::query("INSERT INTO ".$tbl_prefix."plakat (actual_id, del) VALUES (?, false)", array($id));

    $pid = System::lastInsertId();

    System::query("UPDATE ".$tbl_prefix."felder SET plakat_id = ? WHERE id = ?", array($pid, $id));

    System::query("INSERT INTO ".$tbl_prefix."log (plakat_id, user, subject) VALUES(?, ?, ?)", array($pid, $_SESSION['siduser'], "add"));

    return $pid;
}

function map_del($id) {
    global $tbl_prefix, $_SESSION;
    $tbl_prefix = System::getConfig('tbl_prefix');

    System::query("UPDATE ".$tbl_prefix."plakat SET del = true where id = ?", array($id));

    System::query("INSERT INTO ".$tbl_prefix."log (plakat_id, user, subject) VALUES (?,?,?)", array($id, $_SESSION['siduser'], "del"));
}

function map_change($id, $type, $comment, $city, $street, $imageurl) {
    global $_SESSION;

    $tbl_prefix = System::getConfig('tbl_prefix');

    $query = "INSERT INTO ".$tbl_prefix."felder (plakat_id, lon, lat, user, type, comment, city, street, image) " .
             "SELECT :pid as plakat_id, lon, lat, :user as user, ";
    $params = array(
        'pid' => $id,
        'user' => $_SESSION['siduser']
    );

    if(System::getPosterFlags($type)) {
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

    System::query($query, $params);

    $newid = System::lastInsertId();

    System::query("INSERT INTO ".$tbl_prefix."log (plakat_id, user, subject, what) VALUES (?, ?, ?, ?)", array($id, $_SESSION['siduser'], 'change', 'Type: '.$type));

    System::query("UPDATE ".$tbl_prefix."plakat SET actual_id = ? where id = ?", array($newid, $id));
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
