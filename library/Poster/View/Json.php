<?php
$arr = array();
foreach($this->getPosters() as $row) {
    foreach ($row as $field => $value) {
        $row[$field] = htmlspecialchars($value);
    }
    $arr[] = $row;
}

print json_encode($arr);
