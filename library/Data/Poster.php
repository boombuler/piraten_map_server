<?php

class Data_Poster extends Data_Table
{
    /**
     * @todo wenn wir das Tool ausserhalb von Deutschland nutzen wollen, sollten wir das hier mit nem translator fixen.
     * @var array
     */
    private static $types = array('default'          => '',
                                  'plakat_ok'        => 'Plakat hängt',
                                  'plakat_a0'        => 'A0-Plakat steht',
                                  'plakat_dieb'      => 'Plakat wurde gestohlen',
                                  'plakat_niceplace' => 'Gute Stelle für ein Plakat',
                                  'plakat_wrecked'   => 'Plakat beschädigt',
                                  'wand'             => 'Plakatwand der Gemeinde',
                                  'wand_ok'          => 'Plakat an der Plakatwand',
                                  'removed'          => 'Plakat wieder abgenommen');

    protected $poster_id;

    protected $marker_id;

    /**
     * @var Data_Marker
     */
    protected $marker;
    protected $username;

    /**
     * @var Data_User
     */
    protected $user;
    protected $timestamp;
    protected $type;
    protected $comment;
    protected $image;

    public function getId()
    {
        return $this->id;
    }

    private function setId($id)
    {
        if ($this->poster_id) {
            throw new Exception('Changing Id is not possible');
        }

        $this->poster_id = (int)$id;
        $this->logModification('poster_id');
        return $this;
    }

    /**
     * @return the $marker_id
     */
    public function getMarkerId()
    {
        return $this->getMarker()->getId();
    }

    /**
     * @return Data_Marker
     */
    public function getMarker()
    {
        if (!$this->marker) {
            $this->marker = Data_Marker::get($this->marker_id);
        }
        return $this->marker;
    }

    /**
     * @param Data_Marker $marker
     * @return Data_Poster
     */
    public function setMarker(Data_Marker $marker)
    {
        if ($this->getMarker()) {
            throw new Exception('Cannot change position of a Poster');
        }
        $this->marker = $marker;
        $this->marker_id = $this->marker->getId();
        $this->logModification('marker_id');
        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return Data_Poster
     */
    public function setUsername($username)
    {
        $this->username = $username;
        $this->logModification('username');
        return $this;
    }

    /**
     * @return the $timestamp
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param $timestamp the $timestamp to set
     * @return Data_Poster
     */
    private function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
        $this->logModification('timestamp');
        return $this;
    }

    /**
     * @return the $type
     */
    public function getType ()
    {
        return $this->type;
    }

    /**
     * @param $type the $type to set
     * @return Data_Poster
     */
    public function setType($type)
    {
        if (!array_key_exists($type, self::$types)) {
            throw new Exception('The Type "' . $type . '" does not exist.');
        }
        $this->type = $type;
        $this->logModification('type');
        return $this;
    }

    /**
     * @return the $comment
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param $comment the $comment to set
     * @return Data_Poster
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        $this->logModification('comment');
        return $this;
    }

    /**
     * @return the $image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param $image the $image to set
     * @return Data_Poster
     */
    public function setImage($image)
    {
        $this->image = $image;
        $this->logModification('image');
        return $this;
    }

    protected static function getTableName()
    {
        return 'posters';
    }

    protected function getPrimaryKeyValues()
    {
        return array(
            'poster_id' => $this->getId()
        );
    }

    public function validate()
    {
        if (!$this->getMarker()->validate() || !$this->getType()) {
            return false;
        }
        return true;
    }

    public function save()
    {
        $this->getMarker()->save();
        if (!$this->marker_id) {
            $this->marker_id = $this->getMarker()->getId();
            $this->logModification('marker_id');
        }
        $this->setTimestamp(mktime());
        parent::save();
    }

    protected function insert($setvals)
    {
        parent::insert($setvals);
        $this->setId(System::lastInsertId());
    }

	public static function get($id)
    {
        $result = self::find('poster_id', $id);
        return $result->fetchObject(__CLASS__);
    }

    public static function getTypes($type = '')
    {
		if ($type != '' && in_array($type, self::$types)) {
            return self::$types[$type];
        }
        return self::$types;
    }
}