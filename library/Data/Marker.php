<?php
/**
 * A Marker Dataset, which contains only geographical information about an Item, which might be connected to the marker.
 *
 * @author Thomas Kosel
 */
class Data_Marker extends Data_Table
{
    /**
     * @var int
     */
    protected $marker_id;

    /**
     * @var float
     */
    protected $lat;

    /**
     * @var float
     */
    protected $lon;

    /**
     * @var string
     */
    protected $city;

    /**
     * @var string
     */
    protected $street;

    public function getId()
    {
        return $this->marker_id;
    }

    private function setId($id)
    {
        if ($this->marker_id) {
            throw new Exception('Changing Id is not possible');
        }

        $this->marker_id = (int)$id;
        $this->logModification('marker_id');
        return $this;
    }

    /**
     * @return the $lat
     */
    public function getLat()
    {
        return $this->lat;
    }

	/**
     * @param $lat the $lat to set
     * @return Data_Marker
     */
    public function setLat($lat)
    {
        $this->lat = (float) $lat;
        $this->logModification('lat');
        return $this;
    }

	/**
     * @return the $lon
     */
    public function getLon()
    {
        return $this->lon;
    }

	/**
     * @param $lon the $lon to set
     * @return Data_Marker
     */
    public function setLon($lon)
    {
        $this->lon = (float) $lon;
        $this->logModification('lon');
        return $this;
    }

	/**
     * @return the $city
     */
    public function getCity()
    {
        return $this->city;
    }

	/**
     * @param $city the $city to set
     * @return Data_Marker
     */
    public function setCity($city)
    {
        $this->city = $city;
        $this->logModification('city');
        return $this;
    }

	/**
     * @return the $street
     */
    public function getStreet()
    {
        return $this->street;
    }

	/**
     * @param $street the $street to set
     * @return Data_Marker
     */
    public function setStreet($street)
    {
        $this->street = $street;
        $this->logModification('street');
        return $this;
    }

	protected static function getTableName()
    {
        return 'markers';
    }

    protected function getPrimaryKeyValues()
    {
        return array(
            'marker_id' => $this->getId()
        );
    }

    public function validate()
    {
        if (!$this->getLat() || !$this->getLon()) {
            return false;
        }
        return true;
    }


    protected function insert($setvals)
    {
        parent::insert($setvals);
        $this->setId(System::lastInsertId());
    }

    public static function get($id)
    {
        $result = self::find("marker_id", $id);
        return $result->fetchObject(__CLASS__);
    }
}