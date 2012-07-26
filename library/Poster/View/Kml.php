<?php

$dom = new DOMDocument('1.0', 'UTF-8');
$nodeKml = $dom->appendChild($dom->createElementNS('http://www.opengis.net/kml/2.2', 'kml'));

$nodeDoc = $nodeKml->appendChild($dom->createElement('Document'));
$nodeDoc->appendChild($dom->createElement('name', 'PIRATEN'));

$nodeDoc->appendChild($dom->createElement('description'))->appendChild($dom->createCDATASection('Piraten Plakate'));

// Define the styles
$filter  = $this->getGetParameter('filter');
$validFilter = Data_Poster::isValidType($filter);
$styles = array();
$i = 0;
foreach(Data_Poster::getTypes() as $key=>$value) {
	$styleKey = "s$i";
	$i++;
	$styles[$key] = $styleKey;
	if (!($validFilter) || ($filter == $key)) {
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

foreach($this->getPosters() as $row) {
    $arr = preg_split("/\./", $row['lon']);
    $ar2 = str_split($arr[1],6);
    $lon = $arr[0].".".$ar2[0];

    $arr = preg_split("/\./", $row['lat']);
    $ar2 = str_split($arr[1],6);
    $lat = $arr[0].".".$ar2[0];

    $place = $nodeDoc->appendChild($dom->createElement('Placemark'));
    $place->appendChild($dom->createElement('name', $row['marker_id']));
    $place->appendChild($dom->createElement('description'))->appendChild(
        $dom->createCDATASection(json_encode(array(
            'id' => $row['marker_id'],
            't'  => $row['type'],
            'tb' => Data_Poster::getTypes($row['type']),
            'i'  => htmlspecialchars((string) $row['image']),
            'c'  => htmlspecialchars((string) $row['comment']),
            'ci' => htmlspecialchars((string) $row['city']),
            's'  => htmlspecialchars((string) $row['street']),
            'u'  => htmlspecialchars($row['username']),
            'd'  => date('d.m.y H:i', strtotime($row['timestamp']))
        ))));
    if (Data_Poster::isValidType($row['type']))
        $place->appendChild($dom->createElement('styleUrl', '#'.$styles[$row['type']]));
    $place->appendChild($dom->createElement('Point'))->appendChild($dom->createElement('coordinates', "$lon,$lat"));
}
echo $dom->saveXML();