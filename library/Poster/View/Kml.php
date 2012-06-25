<?php

$dom = new DOMDocument('1.0', 'UTF-8');
$nodeKml = $dom->appendChild($dom->createElementNS('http://www.opengis.net/kml/2.2', 'kml'));

$nodeDoc = $nodeKml->appendChild($dom->createElement('Document'));
$nodeDoc->appendChild($dom->createElement('name', 'PIRATEN'));

$nodeDoc->appendChild($dom->createElement('description'))->appendChild($dom->createCDATASection('Piraten Plakate'));

// Define the styles
$filter  = Data_Poster::getTypes($_GET['filter']);
$styles = array();
$i = 0;
foreach(Data_Poster::getTypes() as $key=>$value) {
	$styleKey = "s$i";
	$i++;
	$styles[$key] = $styleKey;
	if (!($filter) || ($filter == $key)) {
		$nStyle = $nodeDoc->appendChild($dom->createElement('Style'));
		$nStyle->setAttribute('id', $styleKey);
		$nIconS = $nStyle->appendChild($dom->createElement('IconStyle'));
		$nHotSpot = $nIconS->appendChild($dom->createElement('hotSpot'));
		$nHotSpot->setAttribute('x', '0.5');
		$nHotSpot->setAttribute('y', '0.5');
		$nHotSpot->setAttribute('xunits', 'fraction');
		$nHotSpot->setAttribute('yunits', 'fraction');
		$nIconS->appendChild($dom->createElement('scale', '0.6'));
		$nIconS->appendChild($dom->createElement('Icon'))->appendChild($dom->createElement('href', "./images/markers/$key.png"));
	}
}

foreach($this->getPosters() as $obj) {
    $lon = $obj->lon;
    $arr = preg_split("/\./", $lon);
    $ar2 = str_split($arr[1],6);
    $lon = $arr[0].".".$ar2[0];

    $lat = $obj->lat;
    $arr = preg_split("/\./", $lat);
    $ar2 = str_split($arr[1],6);
    $lat = $arr[0].".".$ar2[0];

    $time= $obj->timestamp;

    $place = $nodeDoc->appendChild($dom->createElement('Placemark'));
    $place->appendChild($dom->createElement('name', $obj->marker_id));
    $place->appendChild($dom->createElement('description'))->appendChild(
        $dom->createCDATASection(json_encode(array(
            'id'=>$obj->marker_id,
            't'=>$obj->type,
            'tb'=>Data_Poster::getTypes($obj->type),
            'i'=>htmlspecialchars((string) $obj->image),
            'c'=>htmlspecialchars((string) $obj->comment),
            'ci'=>htmlspecialchars((string) $obj->city),
            's'=>htmlspecialchars((string) $obj->street),
            'u'=>htmlspecialchars($obj->user),
            'd'=>date('d.m.y H:i', strtotime($time))
        ))));
    if (Data_Poster::getTypes($type))
        $place->appendChild($dom->createElement('styleUrl', '#'.$styles[$type]));
    $place->appendChild($dom->createElement('Point'))->appendChild($dom->createElement('coordinates', "$lon,$lat"));
}
echo $dom->saveXML();