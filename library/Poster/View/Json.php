<?php
foreach($this->getPosters() as $obj) {
    $obj->user    = htmlspecialchars($obj->user);
    $obj->comment = htmlspecialchars($obj->comment);
    $obj->city    = htmlspecialchars($obj->city);
    $obj->street  = htmlspecialchars($obj->street);
    $obj->image   = htmlspecialchars($obj->image);
    $arr[] = $obj;
}

print json_encode($arr);
