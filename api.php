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
    require_once(dirname(__FILE__). "/includes.php");
    require_once(dirname(__FILE__). '/login.php');
    // first include pb_message
    require_once(dirname(__FILE__). '/protobuf/pb_message.php');
    require_once(dirname(__FILE__). '/protobuf/pb_proto_api.php');

    $request = new Request();
    $data = file_get_contents('php://input');//base64_decode($_POST['request']);
    $request->parseFromString($data);
    $response = new Response();

    if (login($request->Username(), $request->Password())) {
        if ($request->Adds_size() > 0) {
            $addedCount = 0;
            for ($i = 0; $i < $request->Adds_size(); $i++) {
                $add = $request->Add($i);
                $id = map_add($add->Lon(), $add->Lat(), $add->Type(), $request->Adds_size() <= $max_resolve_count);

                $comment = $add->Comment();
                $image = $add->ImageUrl();
                if (!$comment)
                    $comment = null;
                if (!$image)
                    $image = null;
                if ($comment != null || $image != null)
                    map_change($id, null, $comment, null, null, $image);
                    $addedCount += 1;
            }
            $response->set_AddedCount($addedCount);
        }
        if ($request->Changes_size() > 0) {
            $changesCount = 0;
            for($i = 0; $i < $request->Changes_size(); $i++) {
                $change = $request->Change($i);
                $id = $change->Id();
                $type = $change->Type();
                if (!$type)
                    $type = null;
                $comment = $change->Comment();
                if (!$comment)
                    $comment = null;
                $image = $change->ImageUrl();
                if (!$image)
                    $image = null;
                map_change($id, $type, $comment, null, null, $image);
                $changesCount += 1;
            }
            $response->set_ChangedCount($changesCount);
        }
        if ($request->Deletes_size() > 0) {
            $delCount = 0;
            for ($i = 0; $i < $request->Deletes_size(); $i++) {
                $delete = $request->Delete($i);
                $id = $delete->Id();
                map_del($id);
                $delCount += 1;
            }
            $response->set_DeletedCount($delCount);
        }
    }

    $filterstr = "";
    $params = array();
    $filter = $request->ViewRequest();
    if ($filter) {
        if ($filter->Filter_Type()) {
            $filterstr = " AND type = :type";
            $params['type'] = $filter->Filter_Type();
        }
        $vb = $filter->ViewBox();
        if ($vb) {
            $params['east'] = number_format($vb->East(), 6, '.', '');
            $params['west'] = number_format($vb->West(), 6, '.', '');
            $params['north'] = number_format($vb->North(), 6, '.', '');
            $params['south'] = number_format($vb->South(), 6, '.', '');
            $filterstr .= " AND (f.lon <= :east) AND (f.lon >= :west) AND (f.lat <= :north) AND (f.lat >= :south)";
        }
    }

    $query = "SELECT p.id, f.lon, f.lat, f.type, f.user, f.timestamp, f.comment, f.image "
         . " FROM ".$tbl_prefix."felder f JOIN ".$tbl_prefix."plakat p on p.actual_id = f.id"
         . " WHERE p.del != true".$filterstr;

    $db = openDB();
    $stmt = $db->prepare($query);
    $stmt->execute($params);

    foreach($stmt->fetchAll() as  $obj) {
        $plak = $response->add_Plakate();
        $plak->set_Id($obj->id);
        $plak->set_Lon($obj->lon);
        $plak->set_Lat($obj->lat);
        $plak->set_Type($obj->type);
        $plak->set_LastModifiedUser($obj->user);
        $plak->set_LastModifiedTime($obj->timestamp);
        $plak->set_Comment($obj->comment);
        $plak->set_ImageUrl($obj->image);
    }
    $db = null;
    die($response->SerializeToString()); // use die to prevent any other data being send
?>