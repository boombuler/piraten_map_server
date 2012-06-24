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
    $user = User::current();
    if (!$user)
        return;

    $marker = new Data_Marker();
    $marker->setLat($lat)
           ->setLon($lon);
    if ($resolveAdr) {
        $location = request_location($lon, $lat);
        $marker->setCity($location["city"]);
        $marker->setStreet($location["street"]);
    }

    $poster = new Data_Poster();
    $poster->setMarker($marker);

    if ($typ != '') {
        $poster->setType($typ);
    }
    $poster->setUsername($user->getUsername());

    $poster->save();

    Data_Log::add($poster->getMarker()->getId(), Data_Log::SUBJECT_ADD);

    return $poster->getId();
}

function map_del($id) {
    $user = User::current();
    if (!$user)
        return;

    $poster = Data_Poster::get($id);
    if ($poster) {
        $poster->setType('removed')->save();
        Data_Log::add($poster->getId(), Data_Log::SUBJECT_DEL);
    }
}

function map_change($id, $type, $comment, $imageurl) {
    $user = User::current();
    if (!$user)
        return;

    $poster = Data_Poster::get($id);
    if (!$poster)
        return;

    $newposter = new Data_Poster();
    $newposter->setMarker($poster->getMarker())
              ->setType($poster->getType())
              ->setUsername($user->getUsername());

    if($type) {
        $newposter->setType($type);
    }
    if ($comment) {
        $newposter->setComment($comment);
    }
    if($imageurl !== null) {
        $newposter->setImage($imageurl);
    }

    $newposter->save();

    Data_Log::add($newposter->getId(), Data_Log::SUBJECT_CHANGE, 'Type: '.$type);
}