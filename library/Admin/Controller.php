<?php
Class Admin_Controller extends Controller
{
    private $categories;

    public function index()
    {
        if (!User::isAdmin()) {
            return $this->displayMessage('Keine Berechtigung', false);
        }

        $this->view = 'Admin_View_Desktop';
        $this->categories = System::query("SELECT * FROM  ".System::getConfig('tbl_prefix')."regions")->fetchAll();
        $this->display();
    }

    public function add()
    {
        $zoom = $this->getGetParameter('zoom', FILTER_SANITIZE_NUMBER_INT);
        $lat = $this->getGetParameter('lat', FILTER_SANITIZE_NUMBER_FLOAT);
        $lon = $this->getGetParameter('lon', FILTER_SANITIZE_NUMBER_FLOAT);
        $name = $this->getGetParameter('name');
        if (!$zoom || !$lat || !$lon || !$name) {
            return $this->displayMessage('Fehlerhafte Eingabe.', false);
        }

        if (!System::query("INSERT INTO ".System::getConfig('tbl_prefix')."regions (category, lat, lon, zoom) VALUES(?, ?, ?, ?)", array($name, $lat, $lon, $zoom))) {
            return $this->displayMessage('Kategorie konnte nicht hinzugefügt werden.', false);
        }

        return $this->displayMessage("'$name' hinzugefügt.", true, array(
                'id' => System::lastInsertId(),
                'name' => $name,
                'lat' => $lat,
                'lon' => $lon,
                'zoom' => $zoom
            ));
    }

    public function drop()
    {
        $id = $this->getGetParameter('id', FILTER_SANITIZE_NUMBER_INT);
        if (!$id) {
            return $this->displayMessage('Fehlerhafte Eingabe.', false);
        }

        if (!System::query("DELETE FROM ".System::getConfig('tbl_prefix')."regions WHERE id = ?", array($id))) {
            return $this->displayMessage('Kategorie konnte nicht gelöscht werden.', false);
        }

        return $this->displayMessage('Kategorie gelöscht.', true);
    }

    public function getCategories()
    {
        return $this->categories;
    }
}