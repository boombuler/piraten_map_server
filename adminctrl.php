<?php
    require_once('library/System.php');

    if (!User::isAdmin()) {
        echo json_encode(array(
            'status' => 'error',
            'message' => _('No Authorization')
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
						'message' => _f('%s$1 added', $name),
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
						'message' => _('Could not add category')
                    ));
                }
            } else {
                echo json_encode(array(
                    'status' => 'error',
                    'message' => _('Invalid input')
                ));
            }
        } else if ($action == 'drop') {
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if ($id) {
                if (System::query("DELETE FROM ".System::getConfig('tbl_prefix')."regions WHERE id = ?", array($id))) {
                    echo json_encode(array(
                        'status' => 'success',
                        'message'=> _('Category deleted')
                    ));
                } else {
                    echo json_encode(array(
                        'status' => 'error',
                        'message'=> _('Could not delete category')
                    ));
                }
            } else {
                echo json_encode(array(
                    'status' => 'error',
                    'message'=> _('Invalid input')
                ));
            }
        } else {
            echo json_encode(array(
                'status' => 'error',
				'message' => _('Unknown action')
            ));
        }
    }