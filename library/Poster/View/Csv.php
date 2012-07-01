<?php
$filename = '/tmp/' . md5(gmmktime()) . '.csv';
$file = fopen($filename, 'w');
if (!$file)
  die('could not open file');

//header
fputcsv($file, array('Anzahl', 'street', 'type', 'comment'), ';');

//content
foreach($this->getPosters() as $row) {
  fputcsv($file, $row, ';');
}

fclose($file);

header('Content-Description: File Transfer');
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename=plakate.csv');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Content-Length: ' . filesize($filename));
ob_clean();
flush();
readfile($filename);
unlink($filename);