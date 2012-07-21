<?php
require_once('library/System.php');

$user = User::current();

if (!$user and !System::getConfig('allow_view_public'))
    exit();
$_GET['format'] = 'kml';
$controller = new Poster_Controller();
$controller();