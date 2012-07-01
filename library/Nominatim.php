<?php
class Nominatim
{
    const OSM_URL = 'http://nominatim.openstreetmap.org/';

    public static function requestByCoordinates($lat, $lon)
    {
        $src = new DOMDocument('1.0', 'utf-8');
        $src->formatOutput = true;
        $src->preserveWhiteSpace = false;
        $src->load(self::OSM_URL . "reverse?format=xml&zoom=18&addressdetails=1&lon=".$lon."&lat=".$lat);
        $city = self::getInnerHtml($src->getElementsByTagName('city')->item(0));
        $street = self::getInnerHtml($src->getElementsByTagName('road')->item(0));
        return array( "city" => $city, "street" =>  $street);
    }

    private static function getInnerHtml($node)
    {
        $innerHTML= '';
        $children = $node->childNodes;
        foreach ($children as $child) {
            $innerHTML .= $child->ownerDocument->saveXML( $child );
        }
        return $innerHTML;
    }
}