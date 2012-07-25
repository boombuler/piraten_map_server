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

User::setCurrent(CronUser::login());

$query = 'SELECT * '
       . ' FROM ' . System::getConfig('tbl_prefix') . 'marker '
       . ' WHERE f.street is null and f.city is null LIMIT 0, '
       . System::getConfig('max_resolve_count');

$result = System::query($query)->fetchAll(PDO::FETCH_CLASS, 'Data_Marker');

foreach ($result as $obj) {
    $location = Nominatim::requestByCoordinates($obj->getLat(), $obj->getLon());

    $obj->setCity($location["city"]);
    $obj->setStreet($location["road"]);

    $obj->save();
}

User::logout();