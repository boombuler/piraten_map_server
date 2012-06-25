<?php
class Poster_Controller extends Controller
{
    private $posters;

    public function index()
    {
        if (!User::current() and !System::getConfig('allow_view_public')) {
            return;
        }

        switch ($this->getGetParameter('format')) {
            case 'kml':
                $this->view = 'Poster_View_Kml';
            case 'csv':
                $this->view = 'Poster_View_Csv';
            case 'json':
            default:
                $this->view = 'Poster_View_Json';
        }
        $filterstr = "";
        $params = array();

        if (Data_Poster::getTypes($this->getGetParameter('filter'))) {
          $filterstr = " AND type = :type";
          $params['type'] = Data_Poster::getTypes($this->getGetParameter('filter'));
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
        $query = "SELECT m.marker_id, m.lon, m.lat, p.type, p.user, p.timestamp, p.comment, m.city, m.street, p.image "
              . " FROM ".$tbl_prefix."markers m JOIN ".$tbl_prefix."posters p on p.marker_id = m.marker_id"
              . " WHERE type != 'removed' ".$filterstr . ' GROUP BY m.marker_id ORDER BY p.timestamp DESC';
        $this->posters = System::query($query, $params)->fetchAll();

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
            $location = request_location($marker->getLat(), $marker->getLon());
            $marker->setCity($location["city"]);
            $marker->setStreet($location["street"]);
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

    public function getPosters()
    {
        return $this->posters;
    }
}