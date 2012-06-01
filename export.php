<?php 
require_once('library/System.php');
require_once("includes.php");

if (!User::current()) {
    header("location: index.php");
    die;
}
if (!filter_input(INPUT_GET, 'city', FILTER_SANITIZE_STRING))
    die('Bitte gib eine Stadt ein.');

$filename = '/tmp/' . md5(gmmktime()) . '.csv';
$result = System::query('SELECT COUNT(p.id) plakate, street, type, comment '
                      . 'FROM ' . System::getConfig('tbl_prefix') . 'felder f '
                      . 'JOIN ' . System::getConfig('tbl_prefix') . 'plakat p ON '
                      . 'p.actual_id=f.id WHERE p.del !=1 AND f.city LIKE ? '
                      . 'GROUP BY street, type, comment', array(filter_input(INPUT_GET, 'city', FILTER_SANITIZE_STRING)));
$file = fopen($filename, 'w');
if (!$file)
  die('could not open file');

fputcsv($file, array('Anzahl', 'street', 'type', 'comment'), ';');
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
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
?>