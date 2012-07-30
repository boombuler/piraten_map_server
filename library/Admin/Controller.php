<?php
Class Admin_Controller extends Controller
{
    private $categories;

    public function index()
    {
        if (!User::isAdmin()) {
            return $this->displayMessage(_('No Authorization'), false);
        }

        $this->view = 'Admin_View_Desktop';
        $this->categories = System::query("SELECT * FROM  ".System::getConfig('tbl_prefix')."regions")->fetchAll();

        $this->display();
    }

    public function add()
    {
	    if (!User::isAdmin()) {
            return $this->displayMessage(_('No Authorization'), false);
        }
        $zoom = $this->getGetParameter('zoom', FILTER_SANITIZE_NUMBER_INT);
        $lat = $this->getGetParameter('lat', FILTER_SANITIZE_NUMBER_FLOAT);
        $lon = $this->getGetParameter('lon', FILTER_SANITIZE_NUMBER_FLOAT);
        $name = $this->getGetParameter('name');
        if (!$zoom || !$lat || !$lon || !$name) {
            return $this->displayMessage(_('Invalid input'), false);
        }

        if (!System::query("INSERT INTO ".System::getConfig('tbl_prefix')."regions (category, lat, lon, zoom) VALUES(?, ?, ?, ?)", array($name, $lat, $lon, $zoom))) {
            return $this->displayMessage(_('Could not add category'), false);
        }

		return $this->displayMessage(_f('%1$s added', $name), true, array(
                'id' => System::lastInsertId(),
                'name' => $name,
                'lat' => $lat,
                'lon' => $lon,
                'zoom' => $zoom
            ));
    }

    public function drop()
    {
	    if (!User::isAdmin()) {
            return $this->displayMessage(_('No Authorization'), false);
        }
        $id = $this->getGetParameter('id', FILTER_SANITIZE_NUMBER_INT);
        if (!$id) {
			return $this->displayMessage(_('Invalid input'), false);
        }

        if (!System::query("DELETE FROM ".System::getConfig('tbl_prefix')."regions WHERE id = ?", array($id))) {
			return $this->displayMessage(_('Could not delete category'), false);
        }

		return $this->displayMessage(_('Category deleted'), true);
    }

    public function getCategories()
    {	    
        return $this->categories;
    }
}