<?php
    require_once('library/System.php');

    if (!User::isAdmin()) {
        echo json_encode(array(
            'status' => 'error',
            'message' => 'Keine Berechtigung'
        ));
    } else {
        $action = $_GET['action'];

        if ($action == 'add') {
            $zoom = filter_input(INPUT_GET, 'zoom', FILTER_VALIDATE_INT);
            $lat = filter_input(INPUT_GET, 'lat', FILTER_VALIDATE_FLOAT);
            $lon = filter_input(INPUT_GET, 'lon', FILTER_VALIDATE_FLOAT);
            $name = $_GET['name'];
            if ($zoom && $lat && $lon && $name) {

                if (System::query("INSERT INTO ".System::getConfig('tbl_prefix')."regions (category, lat, lon, zoom) VALUES(?, ?, ?, ?)", array($name, $lat, $lon, $zoom))) {
                    $id = System::lastInsertId();
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
            } else {
                echo json_encode(array(
                    'status' => 'error',
                    'message' => 'Fehlerhafte Eingabe.'
                ));
            }
        } else if ($action == 'drop') {
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if ($id) {
                if (System::query("DELETE FROM ".System::getConfig('tbl_prefix')."regions WHERE id = ?", array($id))) {
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