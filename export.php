<?php
require_once('library/System.php');

if (!User::current()) {
    header("location: index.php");
    die;
}
if (!filter_input(INPUT_GET, 'city', FILTER_SANITIZE_STRING))
    die(_('Invalid City'));
$_GET['format'] = 'csv';
$controller = new Poster_Controller();
$controller->listPostersInCity();