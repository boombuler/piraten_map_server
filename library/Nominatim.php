<?php
class Nominatim
{
    const OSM_URL = 'http://nominatim.openstreetmap.org/';

    public static function requestByCoordinates($lat, $lon)
    {
        $data = json_decode(file_get_contents(self::OSM_URL . "reverse?format=json&zoom=18&addressdetails=1&lon=".$lon."&lat=".$lat), true);
        return $data['address'];
    }

    public static function requestByAddress($city, $street, $housenr)
    {
        $data = json_decode(file_get_contents(self::OSM_URL . "search?format=json&zoom=18&addressdetails=1&q=" . $housenr . '+' . $street . ',' . $city), true);
        return $data[0]['address'];
    }
}