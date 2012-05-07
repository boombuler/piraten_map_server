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
            $name = $_GET['name'];
            if ($zoom && $lat && $lon && $name) {
                $db = openDB();
                if ($db->prepare("INSERT INTO ".$tbl_prefix."regions (category, lat, lon, zoom) VALUES(?, ?, ?, ?)")
                       ->execute(array($name, $lat, $lon, $zoom))) {
                    $id = $db->lastInsertId();
                    $result = array(
                        'status' => 'success',
                        'message' => "'$name' hinzugefügt.",
                        'data' => array(
                            'id' => $id,
                            'name' => $name,
                            'lat' => $lat,
                            'lon' => $lon,
                            'zoom' => $zoom
                        )
                    );
                    echo json_encode($result);
                } else {
                    echo json_encode(array(
                        'status' => 'error',
                        'message' => 'Kategorie konnte nicht hinzugefügt werden.'
                    ));
                }
                $db = null;
            } else {
                echo json_encode(array(
                    'status' => 'error',
                    'message' => 'Fehlerhafte Eingabe.'
                ));
            }
        } else if ($action == 'drop') {
            $id = get_int('id');
            if ($id) {
                $db = openDB();
                if ($db->prepare("DELETE FROM ".$tbl_prefix."regions WHERE id = ?")
                       ->execute(array($id))) {
                    echo json_encode(array(
                        'status' => 'success',
                        'message'=> 'Kategorie gelöscht.'
                    ));
                } else {
                    echo json_encode(array(
                        'status' => 'error',
                        'message'=> 'Kategorie konnte nicht gelöscht werden.'
                    ));
                }
                $db = null;
            } else {
                echo json_encode(array(
                    'status' => 'error',
                    'message'=> 'Fehlerhafte Eingabe.'
                ));
            }
        } else {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Unbekannte Aktion.'
            ));
        }
    }
?>