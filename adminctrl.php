<?php
    require_once('includes.php');

    if (!isAdmin()) {
        echo '{"status": "error", "message": "Keine Berechtigung"}';
    } else {
        $action = $_GET['action'];

        if ($action == 'add') {
            $zoom = get_int('zoom');
            $lat = get_float('lat');
            $lon = get_float('lon');
            $name = mysql_escape($_GET['name']);
            if ($zoom && $lat && $lon && $name) {
                $lat = format_float($lat);
                $lon = format_float($lon);
                if (mysql_query("INSERT INTO ".$tbl_prefix."regions (category, lat, lon, zoom) VALUES('$name', '$lat', '$lon', '$zoom')")) {
                    $id = mysql_insert_id();
                    $insertet = '{"id": "'.$id.'", "name": "'.$name.'", "lat":"'.$lat.'","lon":"'.$lon.'", "zoom":"'.$zoom.'"}';
                    
                    echo '{"status": "success", "message": "\''.$name.'\' hinzugefügt.", "data":'.$insertet.'}';
                } else {
                    echo '{"status": "error", "message": "Kategorie konnte nicht hinzugefügt werden."}';
                }
            } else {
                echo '{"status": "error", "message": "Fehlerhafte Eingabe"}';
            }
        } else if ($action == 'drop') {
            $id = get_int('id');
            if ($id) {
                if (mysql_query("DELETE FROM ".$tbl_prefix."regions WHERE id = $id")) {
                    echo '{"status": "success", "message": "Kategorie gelöscht"}';
                } else {
                    echo '{"status": "error", "message": "Kategorie konnte nicht gelöscht werden."}';
                }
            } else {
                echo '{"status": "error", "message": "Fehlerhafte Eingabe"}';
            }
        } else {
            echo '{"status": "error", "message": "Unbekannte Aktion"}';
        }
    }
?>