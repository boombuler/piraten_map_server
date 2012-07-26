<?php
class Poster_Controller extends Controller
{
    private $posters;
	
	private $format;
	
	public function Poster_Controller($format = null)
	{
		if ($format)
			$this->format = $format;
		else
			$this->format = $this->getGetParameter('format');
	}

    public function index()
    {
        if (!User::current() and !System::getConfig('allow_view_public')) {
            return;
        }

        $filterstr = "";
        $params = array();

		if (Data_Poster::isValidType($this->getGetParameter('filter'))) {
          $filterstr = " AND type = :type";
          $params['type'] = $this->getGetParameter('filter');
        }

        $bbox = $this->getGetParameter('bbox');
        if ($bbox) {
            list($bbe, $bbn, $bbw, $bbs) = split(",", $bbox);
            $params['bbe'] = $bbe;
            $params['bbw'] = $bbw;
            $params['bbs'] = $bbs;
            $params['bbn'] = $bbn;
            $filterstr .= " AND (lon >= :bbe) AND (lon <= :bbw) AND (lat >= :bbn) AND (lat <= :bbs)";
        }

        $tbl_prefix = System::getConfig('tbl_prefix');
        $query = "SELECT m.marker_id, m.lon, m.lat, p.type, p.username, p.timestamp, p.comment, m.city, m.street, p.image "
              . " FROM ".$tbl_prefix."markers m JOIN ".$tbl_prefix."posters p on p.marker_id = m.marker_id"
              . " WHERE type != 'removed' ".$filterstr . ' GROUP BY m.marker_id ORDER BY p.timestamp DESC';
        $this->posters = System::query($query, $params)->fetchAll(PDO::FETCH_ASSOC);
		
        $this->display();
    }

    public function add()
    {
        $user = User::current();
        if (!$user)
            return;

        $marker = new Data_Marker();
        $marker->setLat($this->getGetParameter('lat', FILTER_SANITIZE_NUMBER_FLOAT))
               ->setLon($this->getGetParameter('lon', FILTER_SANITIZE_NUMBER_FLOAT));
        //if ($resolveAdr) {
            $location = Nominatim::requestByCoordinates($marker->getLat(), $marker->getLon());
            $marker->setCity($location["city"]);
            $marker->setStreet($location["road"]);
        //}

        $poster = new Data_Poster();
        $poster->setMarker($marker);
        if ($typ != '') {
            $poster->setType($this->getGetParameter('typ'));
        }
        $poster->setUsername($user->getUsername());

        $poster->save();
        Data_Log::add($poster->getMarker()->getId(), Data_Log::SUBJECT_ADD);

        $this->index();
    }

    public function delete()
    {
        $user = User::current();
        if (!$user)
            return;

        $poster = Data_Poster::get($this->getGetParameter('id'));
        if ($poster) {
            $poster->setType('removed')->save();
            Data_Log::add($poster->getId(), Data_Log::SUBJECT_DEL);
        }
        $this->index();
    }

    public function change()
    {
        $user = User::current();
        if (!$user)
            return;

        $poster = Data_Poster::get($this->getGetParameter('id', FILTER_SANITIZE_NUMBER_INT));
        if (!$poster)
            return;

        $newposter = new Data_Poster();
        $newposter->setMarker($poster->getMarker())
                  ->setType($poster->getType())
                  ->setUsername($user->getUsername());

        if($this->getGetParameter('type')) {
            $newposter->setType($this->getGetParameter('type'));
        }
        if ($this->getGetParameter('comment')) {
            $newposter->setComment($this->getGetParameter('comment'));
        }
        if($this->getGetParameter('imageurl')) {
            $newposter->setImage($this->getGetParameter('imageurl'));
        }

        $newposter->save();

        Data_Log::add($newposter->getId(), Data_Log::SUBJECT_CHANGE, 'Type: '.$type);
        $this->index();
    }

    public function listPostersInCity()
    {
        $this->posters = System::query('SELECT COUNT(p.id) plakate, street, type, comment '
                      . 'FROM ' . System::getConfig('tbl_prefix') . 'felder f '
                      . 'JOIN ' . System::getConfig('tbl_prefix') . 'plakat p ON '
                      . 'p.actual_id=f.id WHERE p.del !=1 AND f.city LIKE ? '
                      . 'GROUP BY street, type, comment', array($this->getGetParameter('city')))->fetchAll(PDO::FETCH_ASSOC);
        $this->display();
    }

    public function getPosters()
    {
        return $this->posters;
    }

    protected function getView()
    {
        switch ($this->format) {
            case 'kml':
                return 'Poster_View_Kml';
            case 'csv':
                return 'Poster_View_Csv';
            case 'json':
            default:
                return 'Poster_View_Json';
        }
    }
}